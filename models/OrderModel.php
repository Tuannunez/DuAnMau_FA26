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
                    orders.total_amount, orders.payment_method, orders.payment_status, orders.status, orders.created_at,
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

    public function getAdminDashboardRevenue(): array
    {
        $summaryStatement = $this->pdo->query(
            'SELECT
                COALESCE(SUM(CASE
                    WHEN payment_status = \'paid\' AND status <> \'cancelled\' THEN total_amount
                    ELSE 0
                END), 0) AS received_amount,
                COALESCE(SUM(CASE
                    WHEN payment_status = \'unpaid\' AND status <> \'cancelled\' THEN total_amount
                    ELSE 0
                END), 0) AS outstanding_amount,
                COALESCE(SUM(payment_status = \'paid\' AND status <> \'cancelled\'), 0) AS paid_orders
             FROM orders'
        );
        $summary = $summaryStatement->fetch();

        $productRevenueStatement = $this->pdo->query(
            'SELECT COALESCE(SUM(order_items.unit_price * order_items.quantity), 0)
             FROM order_items
             INNER JOIN orders ON orders.id = order_items.order_id
             WHERE orders.payment_status = \'paid\' AND orders.status <> \'cancelled\''
        );

        $topProductsStatement = $this->pdo->query(
            'SELECT order_items.product_name,
                    SUM(order_items.quantity) AS quantity_sold,
                    SUM(order_items.unit_price * order_items.quantity) AS revenue
             FROM order_items
             INNER JOIN orders ON orders.id = order_items.order_id
             WHERE orders.payment_status = \'paid\' AND orders.status <> \'cancelled\'
             GROUP BY order_items.product_id, order_items.product_name
             ORDER BY revenue DESC, quantity_sold DESC
             LIMIT 5'
        );

        $startMonth = new DateTimeImmutable('first day of this month');
        $startMonth = $startMonth->modify('-5 months');
        $endMonth = (new DateTimeImmutable('first day of this month'))->modify('+1 month');
        $monthlyStatement = $this->pdo->prepare(
            'SELECT DATE_FORMAT(created_at, \'%Y-%m\') AS month_key, SUM(total_amount) AS revenue
             FROM orders
             WHERE payment_status = \'paid\'
               AND status <> \'cancelled\'
               AND created_at >= :start_date
               AND created_at < :end_date
             GROUP BY DATE_FORMAT(created_at, \'%Y-%m\')
             ORDER BY month_key'
        );
        $monthlyStatement->execute([
            'start_date' => $startMonth->format('Y-m-d'),
            'end_date' => $endMonth->format('Y-m-d'),
        ]);
        $monthlyRevenueByKey = [];
        foreach ($monthlyStatement->fetchAll() as $month) {
            $monthlyRevenueByKey[$month['month_key']] = (float) $month['revenue'];
        }

        $monthlyRevenue = [];
        for ($offset = 0; $offset < 6; $offset++) {
            $month = $startMonth->modify('+' . $offset . ' months');
            $monthKey = $month->format('Y-m');
            $monthlyRevenue[] = [
                'label' => $month->format('m/Y'),
                'revenue' => $monthlyRevenueByKey[$monthKey] ?? 0.0,
            ];
        }

        return [
            'received_amount' => (float) $summary['received_amount'],
            'outstanding_amount' => (float) $summary['outstanding_amount'],
            'paid_orders' => (int) $summary['paid_orders'],
            'product_revenue' => (float) $productRevenueStatement->fetchColumn(),
            'top_products' => $topProductsStatement->fetchAll(),
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    public function getCustomerOrders(?int $userId, array $guestOrderCodes): array
    {
        [$accessCondition, $parameters] = $this->customerAccessCondition($userId, $guestOrderCodes);
        if ($accessCondition === '') {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT orders.id, orders.order_code, orders.total_amount, orders.payment_method,
                    orders.payment_status, orders.status, orders.created_at,
                    (SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) AS item_count
             FROM orders
             WHERE ' . $accessCondition . '
             ORDER BY orders.created_at DESC, orders.id DESC'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findCustomerOrder(string $orderCode, ?int $userId, array $guestOrderCodes): ?array
    {
        [$accessCondition, $parameters] = $this->customerAccessCondition($userId, $guestOrderCodes);
        if ($accessCondition === '') {
            return null;
        }
        $parameters['order_code'] = $orderCode;

        $statement = $this->pdo->prepare(
            '            SELECT id, user_id, order_code, recipient_name, recipient_email, recipient_phone,
                    shipping_address, subtotal, discount_amount, total_amount, payment_method,
                    payment_status, status, note, created_at, updated_at
             FROM orders
             WHERE order_code = :order_code AND (' . $accessCondition . ')
             LIMIT 1'
        );
        $statement->execute($parameters);
        $order = $statement->fetch();

        return $order ?: null;
    }

    public function deleteCustomerOrder(string $orderCode, ?int $userId, array $guestOrderCodes): bool
    {
        [$accessCondition, $parameters] = $this->customerAccessCondition($userId, $guestOrderCodes);
        if ($accessCondition === '') {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $parameters['order_code'] = $orderCode;
            $statement = $this->pdo->prepare(
                'SELECT id
                 FROM orders
                 WHERE order_code = :order_code
                   AND status IN (\'completed\', \'cancelled\')
                   AND (' . $accessCondition . ')
                 FOR UPDATE'
            );
            $statement->execute($parameters);
            $orderId = $statement->fetchColumn();
            if ($orderId === false) {
                $this->pdo->rollBack();
                return false;
            }

            $itemsStatement = $this->pdo->prepare('DELETE FROM order_items WHERE order_id = :order_id');
            $itemsStatement->execute(['order_id' => $orderId]);
            $orderStatement = $this->pdo->prepare(
                'DELETE FROM orders WHERE id = :id AND status IN (\'completed\', \'cancelled\')'
            );
            $orderStatement->execute(['id' => $orderId]);
            if ($orderStatement->rowCount() !== 1) {
                throw new RuntimeException('Đơn hàng đã thay đổi trạng thái, vui lòng tải lại trang.');
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function createCustomerOrder(
        array $customer,
        array $items,
        ?int $userId,
        string $paymentMethod,
        float $discountAmount = 0,
        string $promotionCode = ''
    ): string
    {
        if ($items === []) {
            throw new InvalidArgumentException('Giỏ hàng đang trống.');
        }
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)) {
            throw new InvalidArgumentException('Phương thức thanh toán không hợp lệ.');
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
                     :shipping_address, :subtotal, :discount_amount, :total_amount, :payment_method,
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
                'discount_amount' => max(0, min($discountAmount, $subtotal)),
                'total_amount' => max(0, round($subtotal - max(0, min($discountAmount, $subtotal)), 2)),
                'payment_method' => $paymentMethod,
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

    private function customerAccessCondition(?int $userId, array $guestOrderCodes): array
    {
        $conditions = [];
        $parameters = [];
        if ($userId !== null && $userId > 0) {
            $conditions[] = 'orders.user_id = :customer_user_id';
            $parameters['customer_user_id'] = $userId;
        } else {
            $guestOrderCodes = array_values(array_unique(array_filter(
                $guestOrderCodes,
                static fn($code): bool => is_string($code) && preg_match('/^DH[0-9]{14}[A-F0-9]{6}$/', $code)
            )));
        }
        if (($userId === null || $userId < 1) && $guestOrderCodes !== []) {
            $placeholders = [];
            foreach ($guestOrderCodes as $index => $code) {
                $placeholder = 'guest_order_' . $index;
                $placeholders[] = ':' . $placeholder;
                $parameters[$placeholder] = $code;
            }
            $conditions[] = 'orders.order_code IN (' . implode(', ', $placeholders) . ')';
        }

        return [$conditions === [] ? '' : '(' . implode(' OR ', $conditions) . ')', $parameters];
    }
}
