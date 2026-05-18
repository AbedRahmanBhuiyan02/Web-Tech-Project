<?php

namespace App\Models;

use PDO;

class Medicine
{
    public function __construct(private PDO $db)
    {
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT m.*, c.name AS category_name, c.category_type
                FROM medicines m
                JOIN categories c ON c.id = m.category_id
                WHERE 1=1';
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $sql .= ' AND (m.name LIKE :q_name OR m.description LIKE :q_description)';
            $params['q_name'] = '%' . $filters['q'] . '%';
            $params['q_description'] = '%' . $filters['q'] . '%';
        }
        if (($filters['vendor'] ?? '') !== '') {
            $sql .= ' AND m.vendor_name = :vendor';
            $params['vendor'] = $filters['vendor'];
        }
        if ((int) ($filters['category_id'] ?? 0) > 0) {
            $sql .= ' AND c.id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        if (($filters['genre'] ?? '') !== '') {
            $sql .= ' AND c.name = :genre';
            $params['genre'] = $filters['genre'];
        }
        if (($filters['type'] ?? '') !== '') {
            $sql .= ' AND c.category_type = :type';
            $params['type'] = $filters['type'];
        }

        $sql .= ' ORDER BY m.created_at DESC, m.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT m.*, c.name AS category_name, c.category_type FROM medicines m JOIN categories c ON c.id = m.category_id WHERE m.id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function vendors(): array
    {
        return array_column($this->db->query('SELECT DISTINCT vendor_name FROM medicines ORDER BY vendor_name')->fetchAll(), 'vendor_name');
    }

    public function create(array $data): bool
    {
        $statement = $this->db->prepare('INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path) VALUES (:name, :category_id, :vendor_name, :price, :availability, :description, :image_path)');
        return $statement->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE medicines SET name = :name, category_id = :category_id, vendor_name = :vendor_name, price = :price, availability = :availability, description = :description, image_path = :image_path WHERE id = :id');
        return $statement->execute($data);
    }

    public function delete(int $id): bool
    {
        $pending = $this->db->prepare('SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.medicine_id = :id AND o.status = "pending"');
        $pending->execute(['id' => $id]);
        if ((int) $pending->fetchColumn() > 0) {
            return false;
        }

        $statement = $this->db->prepare('DELETE FROM medicines WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
    }
}
