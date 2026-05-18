<section class="panel">
    <h1>My Orders</h1>
    <?php if (!$orders): ?>
        <p>No orders yet.</p>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
        <article class="row">
            <div>
                <h3>Order #<?= (int) $order['id'] ?> - <?= htmlspecialchars(ucfirst((string) $order['status'])) ?></h3>
                <p><?= htmlspecialchars($order['medicines']) ?></p>
                <p><?= htmlspecialchars($order['payment_method']) ?> - <?= htmlspecialchars(date('M j, Y', strtotime((string) $order['order_date']))) ?></p>
            </div>
            <div class="actions">
                <strong>Tk <?= number_format((float) $order['total_amount'], 2) ?></strong>
                <a class="button muted" href="<?= BASE_URL ?>?page=invoice&id=<?= (int) $order['id'] ?>">Invoice PDF</a>
            </div>
        </article>
    <?php endforeach; ?>
</section>
