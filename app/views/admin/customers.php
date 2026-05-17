<section class="panel">
    <h1>Delete Customers</h1>
    <?php foreach ($customers as $customer): ?>
        <div class="row">
            <span><?= htmlspecialchars($customer['name']) ?> - <?= htmlspecialchars($customer['email']) ?></span>
            <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $customer['id'] ?>"><button class="danger">Delete</button></form>
        </div>
    <?php endforeach; ?>
</section>
