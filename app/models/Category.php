<?php

namespace App\Models;

use PDO;

class Category
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $type): bool
    {
        $statement = $this->db->prepare('INSERT INTO categories (name, category_type) VALUES (:name, :type)');
        return $statement->execute(['name' => $name, 'type' => $type]);
    }

    public function update(int $id, string $name, string $type): bool
    {
        $statement = $this->db->prepare('UPDATE categories SET name = :name, category_type = :type WHERE id = :id');
        return $statement->execute(['id' => $id, 'name' => $name, 'type' => $type]);
    }

    public function delete(int $id): bool
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM medicines WHERE category_id = :id');
        $count->execute(['id' => $id]);
        if ((int) $count->fetchColumn() > 0) {
            return false;
        }

        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }
}
