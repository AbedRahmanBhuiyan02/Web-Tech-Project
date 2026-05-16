<section class="stats">
    <article><strong><?= $medicinesCount ?></strong><span>Medicines</span></article>
    <article><strong><?= $categoriesCount ?></strong><span>Categories</span></article>
    <article><strong><?= $customersCount ?></strong><span>Customers</span></article>
    <article><strong><?= $pendingCount ?></strong><span>Pending Orders</span></article>
</section>
<section class="panel">
    <h1>Recent Purchase Requests</h1>
    <?php foreach ($orders as $order): ?>
        <div class="row"><span>#<?= (int) $order['id'] ?> <?= htmlspecialchars($order['customer_name']) ?></span><strong><?= htmlspecialchars($order['status']) ?></strong></div>
    <?php endforeach; ?>
</section>
