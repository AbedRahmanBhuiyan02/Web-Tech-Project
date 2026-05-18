<section class="panel">
    <h1>Your Cart</h1>
    <?php if (!$items): ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
    <?php foreach ($items as $item): ?>
        <article class="row">
            <div>
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><?= htmlspecialchars($item['vendor_name']) ?> - Tk <?= number_format((float) $item['price'], 2) ?></p>
            </div>
            <form class="ajax-cart-update">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="medicine_id" value="<?= (int) $item['medicine_id'] ?>">
                <input type="number" name="quantity" min="0" max="<?= (int) $item['availability'] ?>" value="<?= (int) $item['quantity'] ?>">
                <button>Update</button>
            </form>
            <form class="ajax-cart-remove">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="medicine_id" value="<?= (int) $item['medicine_id'] ?>">
                <button>Remove</button>
            </form>
        </article>
    <?php endforeach; ?>
    <div class="total">Total: Tk <?= number_format($total, 2) ?></div>
    <?php if ($items): ?><a class="button" href="<?= BASE_URL ?>?page=checkout">Proceed to Checkout</a><?php endif; ?>
</section>
