<section class="panel">
    <h1>All Purchase Requests</h1>
    <?php if (empty($orders)): ?>
        <p>No purchase requests yet.</p>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
        <article class="row">
            <div>
                <h3>#<?= (int) $order['id'] ?> <?= htmlspecialchars($order['customer_name']) ?></h3>
                <p><?= htmlspecialchars($order['shipping_address']) ?> - <?= htmlspecialchars($order['order_date']) ?></p>
            </div>
            <div class="actions">
                <strong>Tk <?= number_format((float) $order['total_amount'], 2) ?></strong>
                <span><?= htmlspecialchars(ucfirst((string) $order['status'])) ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</section>
