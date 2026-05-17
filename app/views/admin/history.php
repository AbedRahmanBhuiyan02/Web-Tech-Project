<section class="panel">
    <h1>All Customers' Purchase History</h1>
    <?php if (empty($orders)): ?>
        <p>No accepted purchase history yet.</p>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
        <article class="row">
            <div>
                <h3><?= htmlspecialchars($order['customer_name']) ?> - Order #<?= (int) $order['id'] ?></h3>
                <p><?= htmlspecialchars($order['email']) ?></p>
                <p><?= htmlspecialchars($order['medicines']) ?></p>
            </div>
            <strong>Tk <?= number_format((float) $order['total_amount'], 2) ?></strong>
        </article>
    <?php endforeach; ?>
</section>
