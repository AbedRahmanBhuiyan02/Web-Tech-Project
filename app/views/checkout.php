<section class="panel">
    <h1>Checkout</h1>
    <div class="invoice">
        <?php foreach ($items as $item): ?>
            <div class="split"><span><?= htmlspecialchars($item['name']) ?> x<?= (int) $item['quantity'] ?></span><strong>Tk <?= number_format((float) $item['price'] * (int) $item['quantity'], 2) ?></strong></div>
        <?php endforeach; ?>
        <div class="total">Invoice Total: Tk <?= number_format($total, 2) ?></div>
    </div>
    <form method="post" data-validate="checkout">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label>Shipping address <textarea name="shipping_address" required><?= htmlspecialchars($_SESSION['checkout_address'] ?? $user['address'] ?? '') ?></textarea></label>
        <label>Payment method
            <select name="payment_method" required>
                <option value="">Select payment</option>
                <option>Credit Card</option>
                <option>bKash</option>
                <option>Nagad</option>
                <option>Bank Transfer</option>
                <option>Cash on Delivery</option>
            </select>
        </label>
        <div class="actions"><a class="button muted" href="<?= BASE_URL ?>?page=cart">Cancel</a><button>Confirm Order</button></div>
    </form>
</section>
