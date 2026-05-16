<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Medicine.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/Order.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\HomeController;
use App\Core\Database;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\User;

$config = require __DIR__ . '/../config/config.php';
$pdo = Database::connect($config);

$users = new User($pdo);
$categories = new Category($pdo);
$medicines = new Medicine($pdo);
$cart = new Cart($pdo);
$orders = new Order($pdo);

if (empty($_SESSION['user']) && !empty($_COOKIE['remember_token'])) {
    $remembered = $users->findByRememberToken(hash('sha256', (string) $_COOKIE['remember_token']));
    if ($remembered) {
        $_SESSION['user'] = ['id' => (int) $remembered['id'], 'name' => $remembered['name'], 'role' => $remembered['role']];
    }
}

$page = (string) ($_GET['page'] ?? 'home');

try {
    match ($page) {
        'login' => (new AuthController($users))->login(),
        'register' => (new AuthController($users))->register(),
        'profile' => (new AuthController($users))->profile(),
        'logout' => (new AuthController($users))->logout(),
        'cart' => (new CartController($cart, $orders, $users))->index(),
        'checkout' => (new CartController($cart, $orders, $users))->checkout(),
        'orders' => (new CartController($cart, $orders, $users))->orders(),
        'admin' => (new AdminController($medicines, $categories, $orders, $users))->dashboard(),
        'admin-medicines' => (new AdminController($medicines, $categories, $orders, $users))->medicines(),
        'admin-categories' => (new AdminController($medicines, $categories, $orders, $users))->categories(),
        'admin-customers' => (new AdminController($medicines, $categories, $orders, $users))->customers(),
        'admin-requests' => (new AdminController($medicines, $categories, $orders, $users))->requests(),
        'admin-history' => (new AdminController($medicines, $categories, $orders, $users))->history(),
        'api-medicines-search' => (new ApiController($medicines, $categories, $cart, $orders))->medicines(),
        'api-cart-add' => (new ApiController($medicines, $categories, $cart, $orders))->cartAdd(),
        'api-cart-update' => (new ApiController($medicines, $categories, $cart, $orders))->cartUpdate(),
        'api-cart-remove' => (new ApiController($medicines, $categories, $cart, $orders))->cartRemove(),
        'api-order-status' => (new ApiController($medicines, $categories, $cart, $orders))->orderStatus(),
        default => (new HomeController($medicines, $categories))->index(),
    };
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo '<h1>Database error</h1><p>Import <code>database/meddirect.sql</code> into phpMyAdmin and check <code>config/config.php</code>.</p>';
}
