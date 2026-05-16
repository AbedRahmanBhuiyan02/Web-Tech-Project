<?php

namespace App\Models;

use PDO;

class User
{
    public function __construct(private PDO $db)
    {
    }

    public function create(array $data): bool
    {
        $statement = $this->db->prepare('INSERT INTO users (name, email, password_hash, role, address, phone) VALUES (:name, :email, :password_hash, :role, :address, :phone)');
        return $statement->execute($data);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $data['id'] = $id;
        $statement = $this->db->prepare('UPDATE users SET name = :name, email = :email, address = :address, phone = :phone, profile_picture = :profile_picture WHERE id = :id');
        return $statement->execute($data);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $statement = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $statement->execute(['hash' => $hash, 'id' => $id]);
    }

    public function setRememberToken(int $id, ?string $token): bool
    {
        $statement = $this->db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
        return $statement->execute(['token' => $token, 'id' => $id]);
    }

    public function findByRememberToken(string $token): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE remember_token = :token');
        $statement->execute(['token' => $token]);
        return $statement->fetch() ?: null;
    }

    public function customers(): array
    {
        return $this->db->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();
    }

    public function deleteCustomer(int $id): bool
    {
        $statement = $this->db->prepare("DELETE FROM users WHERE id = :id AND role = 'customer'");
        return $statement->execute(['id' => $id]);
    }

    public function customerCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }
}
