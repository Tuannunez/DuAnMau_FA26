<?php

class ReviewModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS reviews (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NULL,
                product_id INT UNSIGNED NULL,
                order_id INT UNSIGNED NULL,
                rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
                content TEXT NULL,
                status ENUM("pending","approved","hidden") NOT NULL DEFAULT "pending",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_reviews_status (status),
                KEY idx_reviews_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $columns = $this->pdo->query('SHOW COLUMNS FROM reviews')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('order_id', $columns, true)) {
            $this->pdo->exec('ALTER TABLE reviews ADD COLUMN order_id INT UNSIGNED NULL AFTER product_id');
        }
        if (!in_array('status', $columns, true)) {
            $this->pdo->exec('ALTER TABLE reviews ADD COLUMN status ENUM("pending","approved","hidden") NOT NULL DEFAULT "pending"');
        }
    }

    public function getAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT reviews.*, users.full_name AS customer_name, products.name AS product_name
             FROM reviews
             LEFT JOIN users ON users.id = reviews.user_id
             LEFT JOIN products ON products.id = reviews.product_id
             ORDER BY reviews.created_at DESC, reviews.id DESC'
        );
        return $statement->fetchAll();
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['approved', 'hidden'], true)) {
            throw new InvalidArgumentException('Trạng thái đánh giá không hợp lệ.');
        }
        $statement = $this->pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM reviews WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function createVerified(int $orderId, int $userId, int $productId, int $rating, string $content): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Số sao phải từ 1 đến 5.');
        }
        if (mb_strlen($content) > 1000) {
            throw new InvalidArgumentException('Nội dung đánh giá không được vượt quá 1000 ký tự.');
        }
        if ($this->existsForOrderItem($orderId, $userId, $productId)) {
            throw new RuntimeException('Bạn đã đánh giá sản phẩm này trong đơn hàng.');
        }
        $statement = $this->pdo->prepare(
            'INSERT INTO reviews (order_id, user_id, product_id, rating, content, status)
             VALUES (:order_id, :user_id, :product_id, :rating, :content, "pending")'
        );
        $statement->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'content' => trim($content),
        ]);
    }

    public function existsForOrderItem(int $orderId, int $userId, int $productId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM reviews
             WHERE order_id = :order_id AND user_id = :user_id AND product_id = :product_id
             LIMIT 1'
        );
        $statement->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
        return (bool) $statement->fetchColumn();
    }

    public function getApprovedForProduct(int $productId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT reviews.rating, reviews.content, reviews.created_at, users.full_name AS customer_name
             FROM reviews
             LEFT JOIN users ON users.id = reviews.user_id
             WHERE reviews.product_id = :product_id AND reviews.status = "approved"
             ORDER BY reviews.created_at DESC, reviews.id DESC'
        );
        $statement->execute(['product_id' => $productId]);
        return $statement->fetchAll();
    }
}
