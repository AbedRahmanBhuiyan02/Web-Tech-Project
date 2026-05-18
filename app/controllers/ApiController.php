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
            'genre' => trim((string) ($_GET['genre'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
        ])]);
    }

    public function cartAdd(): void
    {
        $this->requireRole('customer');
        $this->verifyCsrf();
        $medicine = $this->medicines->find((int) ($_POST['medicine_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        if (!$medicine || $quantity > (int) $medicine['availability']) {
            $this->json(['ok' => false, 'error' => 'Invalid medicine or quantity.'], 422);
        }
        $this->cart->add((int) $_SESSION['user']['id'], (int) $medicine['id'], $quantity);
        $this->json(['ok' => true, 'cartCount' => $this->cart->count((int) $_SESSION['user']['id'])]);
    }

    public function cartUpdate(): void
    {
        $this->requireRole('customer');
        $this->verifyCsrf();
        $this->cart->update((int) $_SESSION['user']['id'], (int) ($_POST['medicine_id'] ?? 0), max(0, (int) ($_POST['quantity'] ?? 0)));
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
        $this->orders->updateStatus((int) ($_POST['order_id'] ?? 0), $status);
        $this->json(['ok' => true]);
    }
}
