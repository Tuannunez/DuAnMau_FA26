<?php

class OrderModel extends BaseModel
{
    private const ORDER_STATUSES = [
        'pending',
        'confirmed',
        'shipping',
        'completed',
        'cancelled',
    ];

    private const PAYMENT_STATUSES = [
        'unpaid',
        'paid',
        'refunded',
    ];

    public function getAll(?string $status = null): array
    {
        $sql =
            'SELECT orders.id, orders.order_code, orders.recipient_name, orders.recipient_phone,
                    orders.total_amount, orders.payment_status, orders.status, orders.created_at,
                    (SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) AS item_count
             FROM orders';
        $parameters = [];
        if ($status !== null) {
            $sql .= ' WHERE orders.status = :status';
            $parameters['status'] = $status;
        }
        $sql .= ' ORDER BY orders.created_at DESC, orders.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT orders.*, users.full_name AS account_name
             FROM orders
             LEFT JOIN users ON users.id = orders.user_id
             WHERE orders.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();

        return $order ?: null;
    }

    public function getItems(int $orderId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, product_id, product_name, sku, size, quantity, unit_price
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id'
        );
        $statement->execute(['order_id' => $orderId]);

        return $statement->fetchAll();
    }

    public function createCustomerOrder(array $customer, array $items, ?int $userId): string
    {
        if ($items === []) {
            throw new InvalidArgumentException('Giỏ hàng đang trống.');
        }

        $this->pdo->beginTransaction();
        try {
            $quantitiesByProduct = [];
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);
                if ($productId < 1 || $quantity < 1) {
                    throw new RuntimeException('Thông tin sản phẩm trong giỏ không hợp lệ.');
                }
                $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $quantity;
            }
            ksort($quantitiesByProduct, SORT_NUMERIC);

            $productsById = [];
            foreach ($quantitiesByProduct as $productId => $totalQuantity) {
                $statement = $this->pdo->prepare(
                    'SELECT id, name, sku, price, stock
                     FROM products
                     WHERE id = :id AND status = 1
                     FOR UPDATE'
                );
                $statement->execute(['id' => $productId]);
                $product = $statement->fetch();
                if (!$product) {
                    throw new RuntimeException('Một sản phẩm trong giỏ đã ngừng bán. Vui lòng kiểm tra lại giỏ hàng.');
                }
                if ($totalQuantity > (int) $product['stock']) {
                    throw new RuntimeException('Số lượng sản phẩm "' . $product['name'] . '" không còn đủ trong kho.');
                }
                $productsById[$productId] = $product;
            }

            $lockedProducts = [];
            $subtotal = 0.0;
            foreach ($items as $item) {
                $product = $productsById[(int) $item['product_id']];
                $product['quantity'] = $item['quantity'];
                $product['size'] = $item['size'];
                $lockedProducts[] = $product;
                $subtotal += (float) $product['price'] * $item['quantity'];
            }

            $orderCode = 'DH' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
            $orderStatement = $this->pdo->prepare(
                'INSERT INTO orders
                    (user_id, order_code, recipient_name, recipient_email, recipient_phone,
                     shipping_address, subtotal, discount_amount, total_amount, payment_method,
                     payment_status, status, note)
                 VALUES
                    (:user_id, :order_code, :recipient_name, :recipient_email, :recipient_phone,
                     :shipping_address, :subtotal, 0, :total_amount, :payment_method,
                     :payment_status, :status, :note)'
            );
            $orderStatement->execute([
                'user_id' => $userId,
                'order_code' => $orderCode,
                'recipient_name' => $customer['name'],
                'recipient_email' => $customer['email'],
                'recipient_phone' => $customer['phone'],
                'shipping_address' => $customer['address'],
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'note' => $customer['note'] === '' ? null : $customer['note'],
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            $itemStatement = $this->pdo->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, product_name, sku, size, quantity, unit_price)
                 VALUES
                    (:order_id, :product_id, :product_name, :sku, :size, :quantity, :unit_price)'
            );
            $stockStatement = $this->pdo->prepare(
                'UPDATE products SET stock = stock - :quantity WHERE id = :id'
            );
            $decrementedProducts = [];
            foreach ($lockedProducts as $product) {
                $itemStatement->execute([
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'sku' => $product['sku'],
                    'size' => $product['size'] === '' ? null : $product['size'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['price'],
                ]);
                $decrementedProducts[$product['id']] = ($decrementedProducts[$product['id']] ?? 0) + $product['quantity'];
            }
            foreach ($decrementedProducts as $productId => $quantity) {
                $stockStatement->execute(['quantity' => $quantity, 'id' => $productId]);
            }

            $this->pdo->commit();

            return $orderCode;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function updateStatus(int $id, string $status, string $paymentStatus): void
    {
        if (!in_array($status, self::ORDER_STATUSES, true)
            || !in_array($paymentStatus, self::PAYMENT_STATUSES, true)) {
            throw new InvalidArgumentException('Trạng thái đơn hàng không hợp lệ.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE orders
             SET status = :status, payment_status = :payment_status
             WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'payment_status' => $paymentStatus,
            'id' => $id,
        ]);
    }

    public static function statuses(): array
    {
        return self::ORDER_STATUSES;
    }

    public static function paymentStatuses(): array
    {
        return self::PAYMENT_STATUSES;
    }
}
