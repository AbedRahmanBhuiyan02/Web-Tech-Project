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
            $payment = trim((string) ($_POST['payment_method'] ?? ''));
            $methods = ['Credit Card', 'bKash', 'Nagad', 'Bank Transfer', 'Cash on Delivery'];
            if ($address === '') {
                $_SESSION['flash'] = 'Shipping address is required.';
            } elseif (!in_array($payment, $methods, true)) {
                $_SESSION['flash'] = 'Choose a valid payment method.';
            } else {
                try {
                    $orderId = $this->orders->createFromCart($userId, $items, $address, $payment);
                    unset($_SESSION['checkout_address']);
                    $_SESSION['flash'] = 'Order #' . $orderId . ' submitted for admin approval.';
                    $this->redirect('?page=orders');
                } catch (\Throwable $error) {
                    $_SESSION['checkout_address'] = $address;
                    $_SESSION['flash'] = $error->getMessage();
                }
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

    public function orders(): void
    {
        $this->requireRole('customer');
        $this->view('orders', [
            'title' => 'My Orders',
            'activePage' => 'orders',
            'orders' => $this->orders->forUser((int) $_SESSION['user']['id']),
        ]);
    }

    public function invoice(int $orderId): void
    {
        $this->requireRole('customer');
        $order = $this->orders->findForUser($orderId, (int) $_SESSION['user']['id']);
        if (!$order) {
            http_response_code(404);
            echo 'Invoice not found.';
            return;
        }

        $pdf = $this->invoicePdf($order);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice-' . (int) $order['id'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function total(array $items): float
    {
        return array_reduce($items, fn(float $sum, array $item): float => $sum + ((float) $item['price'] * (int) $item['quantity']), 0.0);
    }

    private function invoicePdf(array $order): string
    {
        $content = '';
        $content .= $this->pdfRect(36, 690, 540, 66, '0.91 0.97 0.99 rg', false);
        $content .= $this->pdfText(56, 728, 'MedDirect Online Medicine Shop', 20, 'F2');
        $content .= $this->pdfText(56, 708, 'Official customer invoice', 10);
        $content .= $this->pdfText(438, 728, 'INVOICE', 24, 'F2');
        $content .= $this->pdfText(438, 708, 'Order #' . (int) $order['id'], 11);

        $content .= $this->pdfText(56, 660, 'Bill To', 12, 'F2');
        $content .= $this->pdfText(56, 642, (string) $order['customer_name'], 11);
        $content .= $this->pdfText(56, 626, (string) $order['email'], 10);
        $content .= $this->pdfText(56, 610, (string) $order['phone'], 10);
        $content .= $this->pdfText(56, 594, (string) $order['shipping_address'], 10);

        $content .= $this->pdfText(360, 660, 'Order Details', 12, 'F2');
        $content .= $this->pdfText(360, 642, 'Date: ' . date('M j, Y', strtotime((string) $order['order_date'])), 10);
        $content .= $this->pdfText(360, 626, 'Payment: ' . $order['payment_method'], 10);
        $content .= $this->pdfText(360, 610, 'Status: ' . ucfirst((string) $order['status']), 10);

        $content .= $this->pdfRect(56, 538, 500, 26, '0.06 0.42 0.51 rg', false);
        $content .= $this->pdfText(70, 546, 'Medicine', 10, 'F2', '1 1 1 rg');
        $content .= $this->pdfText(330, 546, 'Qty', 10, 'F2', '1 1 1 rg');
        $content .= $this->pdfText(390, 546, 'Unit Price', 10, 'F2', '1 1 1 rg');
        $content .= $this->pdfText(490, 546, 'Total', 10, 'F2', '1 1 1 rg');

        $y = 512;
        foreach ($order['items'] as $item) {
            $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
            $content .= $this->pdfLine(56, $y - 8, 556, $y - 8, '0.87 0.90 0.95 RG');
            $content .= $this->pdfText(70, $y, $this->fitPdfText((string) $item['name'], 38), 10);
            $content .= $this->pdfText(336, $y, (string) (int) $item['quantity'], 10);
            $content .= $this->pdfText(390, $y, 'Tk ' . number_format((float) $item['unit_price'], 2), 10);
            $content .= $this->pdfText(490, $y, 'Tk ' . number_format($lineTotal, 2), 10, 'F2');
            $y -= 28;
        }

        $content .= $this->pdfRect(358, max(110, $y - 42), 198, 40, '0.96 0.98 0.99 rg', true);
        $content .= $this->pdfText(376, max(134, $y - 18), 'Grand Total', 13, 'F2');
        $content .= $this->pdfText(466, max(134, $y - 18), 'Tk ' . number_format((float) $order['total_amount'], 2), 13, 'F2');
        $content .= $this->pdfText(56, 78, 'Thank you for shopping with MedDirect Online Medicine Shop.', 10);
        $content .= $this->pdfText(56, 62, 'Please keep this invoice for your records.', 9);

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n",
            "6 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream\nendobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    }

    private function pdfText(float $x, float $y, string $text, int $size = 10, string $font = 'F1', string $color = '0.09 0.13 0.20 rg'): string
    {
        return "BT\n" . $color . "\n/" . $font . ' ' . $size . " Tf\n" . $x . ' ' . $y . " Td\n(" . $this->pdfEscape($text) . ") Tj\nET\n";
    }

    private function pdfLine(float $x1, float $y1, float $x2, float $y2, string $color = '0 0 0 RG'): string
    {
        return $color . "\n0.7 w\n" . $x1 . ' ' . $y1 . " m\n" . $x2 . ' ' . $y2 . " l\nS\n";
    }

    private function pdfRect(float $x, float $y, float $width, float $height, string $color, bool $stroke): string
    {
        return $color . "\n" . $x . ' ' . $y . ' ' . $width . ' ' . $height . ' re ' . ($stroke ? "B\n" : "f\n");
    }

    private function pdfEscape(string $text): string
    {
        return strtr($text, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);
    }

    private function fitPdfText(string $text, int $max): string
    {
        return strlen($text) > $max ? substr($text, 0, $max - 3) . '...' : $text;
    }
}
