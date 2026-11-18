<?php

class CategoryModel extends BaseModel
{
    public function getAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT categories.id, categories.parent_id, categories.name, categories.slug,
                    categories.description, categories.status, categories.created_at,
                    parent.name AS parent_name,
                    (SELECT COUNT(*) FROM products WHERE products.category_id = categories.id) AS product_count
             FROM categories
             LEFT JOIN categories AS parent ON parent.id = categories.parent_id
             ORDER BY categories.id DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, parent_id, name, slug, description, status
             FROM categories WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category ?: null;
    }

    public function getParentOptions(?int $exceptId = null): array
    {
        $sql = 'SELECT id, name FROM categories WHERE parent_id IS NULL';
        $parameters = [];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->pdo->prepare($sql . ' ORDER BY name');
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE name = :name';
        $parameters = ['name' => $name];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE slug = :slug';
        $parameters = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return (bool) $statement->fetchColumn();
    }

    public function parentExists(int $parentId, ?int $exceptId = null): bool
    {
        if ($exceptId !== null && $parentId === $exceptId) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'SELECT 1 FROM categories WHERE id = :id AND parent_id IS NULL LIMIT 1'
        );
        $statement->execute(['id' => $parentId]);

        return (bool) $statement->fetchColumn();
    }

    public function getReferenceCounts(int $id): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM products WHERE category_id = :product_category_id) AS products,
                (SELECT COUNT(*) FROM categories WHERE parent_id = :parent_category_id) AS children'
        );
        $statement->execute([
            'product_category_id' => $id,
            'parent_category_id' => $id,
        ]);

        return $statement->fetch();
    }

    public function create(array $category): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO categories (parent_id, name, slug, description, status)
             VALUES (:parent_id, :name, :slug, :description, :status)'
        );
        $statement->execute($category);
    }

    public function update(int $id, array $category): void
    {
        $category['id'] = $id;
        $statement = $this->pdo->prepare(
            'UPDATE categories
             SET parent_id = :parent_id,
                 name = :name,
                 slug = :slug,
                 description = :description,
                 status = :status
             WHERE id = :id'
        );
        $statement->execute($category);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
