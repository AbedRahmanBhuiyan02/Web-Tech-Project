<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;

class ApiController extends Controller
{
    public function __construct(private Medicine $medicines, private Category $categories, private Cart $cart, private Order $orders)
    {
    }

    public function medicines(): void
    {
        $this->json(['ok' => true, 'medicines' => $this->medicines->all([
            'q' => trim((string) ($_GET['q'] ?? '')),
            'vendor' => trim((string) ($_GET['vendor'] ?? '')),
            'category_id' => (int) ($_GET['category_id'] ?? 0),
            'genre' => trim((string) ($_GET['genre'] ?? '')),
            'type' => in_array($_GET['type'] ?? '', ['liquid', 'solid'], true) ? $_GET['type'] : '',
        ])]);
    }

    public function cartAdd(): void
    {
        $this->requireRole('customer');
        $this->verifyCsrf();
        $userId = (int) $_SESSION['user']['id'];
        $medicine = $this->medicines->find((int) ($_POST['medicine_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        if (!$medicine || $quantity + $this->cart->quantityFor($userId, (int) $medicine['id']) > (int) $medicine['availability']) {
            $this->json(['ok' => false, 'error' => 'Invalid medicine or quantity.'], 422);
        }
        $this->cart->add($userId, (int) $medicine['id'], $quantity);
        $this->json(['ok' => true, 'cartCount' => $this->cart->count($userId)]);
    }

    public function cartUpdate(): void
    {
        $this->requireRole('customer');
        $this->verifyCsrf();
        $medicine = $this->medicines->find((int) ($_POST['medicine_id'] ?? 0));
        $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
        if (!$medicine || $quantity > (int) $medicine['availability']) {
            $this->json(['ok' => false, 'error' => 'Invalid medicine or quantity.'], 422);
        }
        $this->cart->update((int) $_SESSION['user']['id'], (int) $medicine['id'], $quantity);
        $this->json(['ok' => true]);
    }

    public function cartRemove(): void
    {
        $this->requireRole('customer');
        $this->verifyCsrf();
        $this->cart->remove((int) $_SESSION['user']['id'], (int) ($_POST['medicine_id'] ?? 0));
        $this->json(['ok' => true]);
    }

    public function orderStatus(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['accepted', 'rejected'], true)) {
            $this->json(['ok' => false, 'error' => 'Invalid status.'], 422);
        }
        $updated = $this->orders->updateStatus((int) ($_POST['order_id'] ?? 0), $status);
        $this->json(['ok' => $updated, 'error' => $updated ? null : 'Order could not be updated.'], $updated ? 200 : 422);
    }
}
