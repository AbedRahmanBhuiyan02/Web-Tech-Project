<?php

namespace App\Models;

use PDO;

class Cart
{
    public function __construct(private PDO $db)
    {
    }

    public function items(int $userId): array
    {
        $statement = $this->db->prepare('SELECT c.*, m.name, m.vendor_name, m.price, m.availability, m.image_path FROM cart c JOIN medicines m ON m.id = c.medicine_id WHERE c.user_id = :user_id ORDER BY c.added_at DESC');
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function add(int $userId, int $medicineId, int $quantity): bool
    {
        $statement = $this->db->prepare('INSERT INTO cart (user_id, medicine_id, quantity) VALUES (:user_id, :medicine_id, :quantity) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
        return $statement->execute(['user_id' => $userId, 'medicine_id' => $medicineId, 'quantity' => $quantity]);
    }

    public function quantityFor(int $userId, int $medicineId): int
    {
        $statement = $this->db->prepare('SELECT COALESCE(quantity, 0) FROM cart WHERE user_id = :user_id AND medicine_id = :medicine_id');
        $statement->execute(['user_id' => $userId, 'medicine_id' => $medicineId]);
        return (int) $statement->fetchColumn();
    }

    public function update(int $userId, int $medicineId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->remove($userId, $medicineId);
        }

        $statement = $this->db->prepare('UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND medicine_id = :medicine_id');
        return $statement->execute(['quantity' => $quantity, 'user_id' => $userId, 'medicine_id' => $medicineId]);
    }

    public function remove(int $userId, int $medicineId): bool
    {
        $statement = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id AND medicine_id = :medicine_id');
        return $statement->execute(['user_id' => $userId, 'medicine_id' => $medicineId]);
    }

    public function clear(int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id');
        return $statement->execute(['user_id' => $userId]);
    }

    public function count(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
        return (int) $statement->fetchColumn();
    }
}
