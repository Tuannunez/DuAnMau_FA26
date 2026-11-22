<?php

class PromotionModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS promotions (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                code VARCHAR(30) NOT NULL,
                name VARCHAR(180) NOT NULL,
                description TEXT NULL,
                discount_type ENUM("percentage","fixed") NOT NULL DEFAULT "percentage",
                discount_value DECIMAL(12,2) NOT NULL,
                starts_at DATETIME NULL,
                ends_at DATETIME NULL,
                status TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_promotions_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $columnRows = $this->pdo->query('SHOW COLUMNS FROM promotions')->fetchAll();
        $columns = array_column($columnRows, 'Field');
        $missing = [
            'description' => 'TEXT NULL',
            'discount_type' => 'ENUM("percentage","fixed") NOT NULL DEFAULT "percentage"',
            'discount_value' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
            'starts_at' => 'DATETIME NULL',
            'ends_at' => 'DATETIME NULL',
            'status' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'created_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];
        foreach ($missing as $column => $definition) {
            if (!in_array($column, $columns, true)) {
                $this->pdo->exec('ALTER TABLE promotions ADD COLUMN ' . $column . ' ' . $definition);
            }
        }

        $discountTypeColumn = null;
        foreach ($columnRows as $column) {
            if (($column['Field'] ?? null) === 'discount_type') {
                $discountTypeColumn = $column;
                break;
            }
        }
        $discountTypeLength = null;
        if ($discountTypeColumn !== null
            && preg_match('/^varchar\((\d+)\)/i', (string) $discountTypeColumn['Type'], $matches)) {
            $discountTypeLength = (int) $matches[1];
        }
        if ($discountTypeColumn !== null
            && ($discountTypeLength === null || $discountTypeLength < 10)) {
            $this->pdo->exec(
                "ALTER TABLE promotions
                 MODIFY COLUMN discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage'"
            );
        }
        $this->pdo->exec(
            "UPDATE promotions
             SET discount_type = CASE LOWER(discount_type)
                 WHEN 'percent' THEN 'percentage'
                 WHEN 'fixed_amount' THEN 'fixed'
                 WHEN 'amount' THEN 'fixed'
                 ELSE LOWER(discount_type)
             END
             WHERE LOWER(discount_type) IN ('percent', 'fixed_amount', 'amount')"
        );
    }

    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT * FROM promotions ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM promotions WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function create(array $promotion): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO promotions
                (code, name, description, discount_type, discount_value, starts_at, ends_at, status)
             VALUES
                (:code, :name, :description, :discount_type, :discount_value, :starts_at, :ends_at, :status)'
        );
        $statement->execute($promotion);
    }

    public function update(int $id, array $promotion): void
    {
        $promotion['id'] = $id;
        $statement = $this->pdo->prepare(
            'UPDATE promotions SET
                code = :code,
                name = :name,
                description = :description,
                discount_type = :discount_type,
                discount_value = :discount_value,
                starts_at = :starts_at,
                ends_at = :ends_at,
                status = :status
             WHERE id = :id'
        );
        $statement->execute($promotion);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM promotions WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function toggleStatus(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE promotions SET status = IF(status = 1, 0, 1) WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public function resolveCode(string $code, float $subtotal): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        $statement = $this->pdo->prepare(
            'SELECT * FROM promotions
             WHERE code = :code
               AND status = 1
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())
             LIMIT 1'
        );
        $statement->execute(['code' => $code]);
        $promotion = $statement->fetch();
        if (!$promotion) {
            return null;
        }

        $value = (float) $promotion['discount_value'];
        $discount = $promotion['discount_type'] === 'percentage'
            ? $subtotal * $value / 100
            : $value;
        $discount = min($discount, $subtotal);
        $discount = round($discount, 2);

        return [
            'code' => $promotion['code'],
            'discount_amount' => $discount,
            'total_amount' => round($subtotal - $discount, 2),
        ];
    }
}
