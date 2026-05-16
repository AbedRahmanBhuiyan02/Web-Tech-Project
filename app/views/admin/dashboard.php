<section class="stats" aria-label="Dashboard summary">
    <article>
        <span>Total Medicines</span>
        <strong><?= (int) $medicinesCount ?></strong>
    </article>
    <article>
        <span>Total Categories</span>
        <strong><?= (int) $categoriesCount ?></strong>
    </article>
    <article>
        <span>Total Customers</span>
        <strong><?= (int) $customersCount ?></strong>
    </article>
    <article>
        <span>Pending Orders</span>
        <strong><?= (int) $pendingCount ?></strong>
    </article>
</section>

<section class="panel">
    <h1>Recent Purchase Requests</h1>
    <?php if (empty($orders)): ?>
        <p>No purchase requests yet.</p>
    <?php endif; ?>
    <?php foreach ($orders as $order): ?>
        <article class="row">
            <span>
                #<?= (int) $order['id'] ?>
                <?= htmlspecialchars($order['customer_name']) ?>
                <small><?= htmlspecialchars(date('M j, Y', strtotime((string) $order['order_date']))) ?></small>
            </span>
            <span>
                Tk <?= number_format((float) $order['total_amount'], 2) ?>
                <strong><?= htmlspecialchars(ucfirst((string) $order['status'])) ?></strong>
            </span>
        </article>
    <?php endforeach; ?>
</section>
