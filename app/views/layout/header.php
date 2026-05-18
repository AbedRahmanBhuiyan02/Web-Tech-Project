<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'MedDirect') ?> - MedDirect Online Medicine Shop</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script defer src="assets/app.js"></script>
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= BASE_URL ?>?page=home">MedDirect Online Medicine Shop</a>
    <nav>
        <a class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=home">Browse</a>
        <?php if (!empty($_SESSION['user'])): ?>
            <a class="<?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=profile">Profile</a>
            <?php if ($_SESSION['user']['role'] === 'customer'): ?>
                <a class="<?= ($activePage ?? '') === 'cart' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=cart">Cart <span class="nav-badge"><?= (int) ($_SESSION['cart_count'] ?? 0) ?></span></a>
                <a class="<?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=orders">My Orders</a>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a class="<?= ($activePage ?? '') === 'admin' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin">Dashboard</a>
                <a class="<?= ($activePage ?? '') === 'admin-medicines' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin-medicines">Medicines</a>
                <a class="<?= ($activePage ?? '') === 'admin-categories' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin-categories">Categories</a>
                <a class="<?= ($activePage ?? '') === 'admin-requests' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin-requests">Requests</a>
                <a class="<?= ($activePage ?? '') === 'admin-history' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin-history">History</a>
                <a class="<?= ($activePage ?? '') === 'admin-customers' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=admin-customers">Customers</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>?page=logout">Logout</a>
        <?php else: ?>
            <a class="<?= ($activePage ?? '') === 'login' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=login">Login</a>
            <a class="<?= ($activePage ?? '') === 'register' ? 'active' : '' ?>" href="<?= BASE_URL ?>?page=register">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page" data-base-url="<?= BASE_URL ?>" data-csrf="<?= htmlspecialchars($csrf ?? ($_SESSION['csrf'] ?? '')) ?>" data-user-role="<?= htmlspecialchars($_SESSION['user']['role'] ?? '') ?>">
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
