<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;

class ApiController extends Controller
{
    public function __construct(private Medicine $medicines, private Category $categories, private $cart, private Order $orders)
    {
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
        $this->json(['ok' => $updated]);
    }
}
