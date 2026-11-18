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
            'SELECT id, product_id, product_name, sku, quantity, unit_price
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id'
        );
        $statement->execute(['order_id' => $orderId]);

        return $statement->fetchAll();
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
