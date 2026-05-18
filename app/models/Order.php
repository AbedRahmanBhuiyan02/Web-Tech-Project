<?php

namespace App\Models;

use PDO;

class Order
{
    public function __construct(private PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT o.*, u.name AS customer_name, u.email, u.phone FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.order_date DESC')->fetchAll();
    }

    public function acceptedHistory(): array
    {
        $statement = $this->db->query("SELECT o.*, u.name AS customer_name, u.email, GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') AS medicines
            FROM orders o
            JOIN users u ON u.id = o.user_id
            JOIN order_items oi ON oi.order_id = o.id
            JOIN medicines m ON m.id = oi.medicine_id
            WHERE o.status = 'accepted'
            GROUP BY o.id
            ORDER BY o.order_date DESC");
        return $statement->fetchAll();
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare("SELECT o.*, GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') AS medicines
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN medicines m ON m.id = oi.medicine_id
            WHERE o.user_id = :user_id
            GROUP BY o.id
            ORDER BY o.order_date DESC");
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function findForUser(int $orderId, int $userId): ?array
    {
        $statement = $this->db->prepare('SELECT o.*, u.name AS customer_name, u.email, u.phone FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = :id AND o.user_id = :user_id');
        $statement->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $statement->fetch();
        if (!$order) {
            return null;
        }

        $items = $this->db->prepare('SELECT oi.*, m.name, m.vendor_name FROM order_items oi JOIN medicines m ON m.id = oi.medicine_id WHERE oi.order_id = :order_id ORDER BY oi.id');
        $items->execute(['order_id' => $orderId]);
        $order['items'] = $items->fetchAll();
        return $order;
    }

    public function createFromCart(int $userId, array $items, string $address, string $paymentMethod): int
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) $item['price'] * (int) $item['quantity'];
        }

        $this->db->beginTransaction();
        try {
            $order = $this->db->prepare('INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (:user_id, :total_amount, :shipping_address, :payment_method)');
            $order->execute([
                'user_id' => $userId,
                'total_amount' => $total,
                'shipping_address' => $address,
                'payment_method' => $paymentMethod,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare('INSERT INTO order_items (order_id, medicine_id, quantity, unit_price) VALUES (:order_id, :medicine_id, :quantity, :unit_price)');
            $stockStatement = $this->db->prepare('UPDATE medicines SET availability = availability - :quantity_decrement WHERE id = :medicine_id AND availability >= :quantity_required');
            foreach ($items as $item) {
                $stockStatement->execute([
                    'quantity_decrement' => $item['quantity'],
                    'quantity_required' => $item['quantity'],
                    'medicine_id' => $item['medicine_id'],
                ]);
                if ($stockStatement->rowCount() === 0) {
                    throw new \RuntimeException('Stock unavailable for ' . $item['name']);
                }
                $itemStatement->execute([
                    'order_id' => $orderId,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                ]);
            }

            $payment = $this->db->prepare('INSERT INTO payments (order_id, amount, payment_method, transaction_id) VALUES (:order_id, :amount, :payment_method, :transaction_id)');
            $payment->execute([
                'order_id' => $orderId,
                'amount' => $total,
                'payment_method' => $paymentMethod,
                'transaction_id' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
            ]);

            $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id')->execute(['user_id' => $userId]);
            $this->db->commit();
            return $orderId;
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['accepted', 'rejected'], true)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $current = $this->db->prepare('SELECT status FROM orders WHERE id = :id FOR UPDATE');
            $current->execute(['id' => $id]);
            $currentStatus = $current->fetchColumn();
            if (!$currentStatus) {
                $this->db->rollBack();
                return false;
            }

            if ($currentStatus !== 'pending') {
                $this->db->rollBack();
                return false;
            }

            $statement = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id AND status = 'pending'");
            $statement->execute(['status' => $status, 'id' => $id]);
            if ($statement->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }

            if ($status === 'rejected') {
                $restore = $this->db->prepare('UPDATE medicines m JOIN order_items oi ON oi.medicine_id = m.id SET m.availability = m.availability + oi.quantity WHERE oi.order_id = :order_id');
                $restore->execute(['order_id' => $id]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function pendingCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    }
}
