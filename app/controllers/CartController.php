<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;

class CartController extends Controller
{
    public function __construct(private Cart $cart, private Order $orders, private User $users)
    {
    }

    public function index(): void
    {
        $this->requireRole('customer');
        $items = $this->cart->items((int) $_SESSION['user']['id']);
        $this->view('cart', [
            'title' => 'Cart',
            'activePage' => 'cart',
            'csrf' => $this->csrf(),
            'items' => $items,
            'total' => $this->total($items),
        ]);
    }

    public function checkout(): void
    {
        $this->requireRole('customer');
        $userId = (int) $_SESSION['user']['id'];
        $items = $this->cart->items($userId);
        if (!$items) {
            $_SESSION['flash'] = 'Your cart is empty.';
            $this->redirect('?page=cart');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $address = trim((string) ($_POST['shipping_address'] ?? ''));
            if ($address === '') {
                $_SESSION['flash'] = 'Shipping address is required.';
            } else {
                $_SESSION['checkout_address'] = $address;
                $_SESSION['flash'] = 'Invoice details are ready for confirmation.';
            }
        }

        $this->view('checkout', [
            'title' => 'Checkout',
            'activePage' => 'cart',
            'csrf' => $this->csrf(),
            'items' => $items,
            'total' => $this->total($items),
            'user' => $this->users->find($userId),
        ]);
    }

    private function total(array $items): float
    {
        return array_reduce($items, fn(float $sum, array $item): float => $sum + ((float) $item['price'] * (int) $item['quantity']), 0.0);
    }
}
