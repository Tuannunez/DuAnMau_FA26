<?php

class ProductModel extends BaseModel
{
    public function getAvailableForStore(?int $categoryId = null): array
    {
        $sql =
            'SELECT products.id, products.category_id, products.name, products.slug, products.sku,
                    products.description, products.price, products.stock, products.image_url,
                    categories.name AS category_name,
                    (
                        SELECT product_images.image_url
                        FROM product_images
                        WHERE product_images.product_id = products.id
                        ORDER BY product_images.sort_order, product_images.id
                        LIMIT 1
                    ) AS additional_image
             FROM products
             LEFT JOIN categories ON categories.id = products.category_id
             WHERE products.status = 1';
        $parameters = [];
        if ($categoryId !== null) {
            $sql .= ' AND products.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        $sql .= ' ORDER BY products.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function getStoreCategories(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name
             FROM categories
             WHERE status = 1
             ORDER BY categories.name'
        );

        return $statement->fetchAll();
    }

    public function getStoreCategory(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name FROM categories WHERE id = :id AND status = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category ?: null;
    }

    public function getAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT products.*, categories.name AS category_name
             FROM products
             LEFT JOIN categories ON categories.id = products.category_id
             ORDER BY products.id DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM products WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();

        return $product ?: null;
    }

    public function getCategories(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name FROM categories WHERE status = 1 ORDER BY name'
        );

        return $statement->fetchAll();
    }

    public function categoryExists(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM categories WHERE id = :id AND status = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        return (bool) $statement->fetchColumn();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug = :slug';
        $parameters = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function getAdditionalImage(int $productId): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT image_url FROM product_images
             WHERE product_id = :product_id AND sort_order = 1
             ORDER BY id LIMIT 1'
        );
        $statement->execute(['product_id' => $productId]);
        $image = $statement->fetchColumn();

        return $image === false ? null : $image;
    }

    public function create(array $product, ?string $additionalImage): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO products
                    (category_id, name, slug, sku, description, price, stock, image_url, status)
                 VALUES
                    (:category_id, :name, :slug, :sku, :description, :price, :stock, :image_url, :status)'
            );
            $statement->execute($product);

            if ($additionalImage !== null) {
                $this->insertAdditionalImage((int) $this->pdo->lastInsertId(), $additionalImage);
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function update(int $id, array $product, ?string $additionalImage, bool $replaceAdditionalImage): void
    {
        $this->pdo->beginTransaction();
        try {
            $product['id'] = $id;
            $statement = $this->pdo->prepare(
                'UPDATE products
                 SET category_id = :category_id,
                     name = :name,
                     slug = :slug,
                     sku = :sku,
                     description = :description,
                     price = :price,
                     stock = :stock,
                     image_url = :image_url,
                     status = :status
                 WHERE id = :id'
            );
            $statement->execute($product);

            if ($replaceAdditionalImage) {
                $deleteStatement = $this->pdo->prepare(
                    'DELETE FROM product_images WHERE product_id = :product_id AND sort_order = 1'
                );
                $deleteStatement->execute(['product_id' => $id]);
                if ($additionalImage !== null) {
                    $this->insertAdditionalImage($id, $additionalImage);
                }
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function insertAdditionalImage(int $productId, string $imageUrl): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO product_images (product_id, image_url, sort_order)
             VALUES (:product_id, :image_url, 1)'
        );
        $statement->execute([
            'product_id' => $productId,
            'image_url' => $imageUrl,
        ]);
    }
}
