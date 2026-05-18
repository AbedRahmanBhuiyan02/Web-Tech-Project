<section class="hero">
    <div>
        <p class="eyebrow">Online medicine shop</p>
        <h1>Browse medicines by genre, vendor, and liquid or solid category.</h1>
        <p>Customers can search inventory, add medicines to cart, checkout, choose payment, and wait for admin approval.</p>
    </div>
</section>

<section class="panel">
    <form class="filters" id="medicine-search">
        <input name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Search medicine name">
        <select name="category_id">
            <option value="">All genres</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" data-type="<?= htmlspecialchars($category['category_type']) ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?> (<?= htmlspecialchars($category['category_type']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <select name="vendor">
            <option value="">All vendors</option>
            <?php foreach ($vendors as $vendor): ?>
                <option value="<?= htmlspecialchars($vendor) ?>" <?= $filters['vendor'] === $vendor ? 'selected' : '' ?>><?= htmlspecialchars($vendor) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type">
            <option value="">Liquid and solid</option>
            <option value="liquid" <?= $filters['type'] === 'liquid' ? 'selected' : '' ?>>Liquid</option>
            <option value="solid" <?= $filters['type'] === 'solid' ? 'selected' : '' ?>>Solid</option>
        </select>
    </form>
</section>

<section class="grid cards" id="medicine-list">
    <?php foreach ($medicines as $medicine): ?>
        <article class="card medicine-card">
            <?php if (!empty($medicine['image_path']) && is_file(__DIR__ . '/../../public/' . $medicine['image_path'])): ?>
                <img class="medicine-image" src="<?= htmlspecialchars($medicine['image_path']) ?>" alt="<?= htmlspecialchars($medicine['name']) ?>">
            <?php else: ?>
                <div class="image-fallback"><?= strtoupper(substr($medicine['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <span class="badge"><?= htmlspecialchars($medicine['category_type']) ?></span>
            <h3><?= htmlspecialchars($medicine['name']) ?></h3>
            <p><?= htmlspecialchars($medicine['description']) ?></p>
            <p><strong><?= htmlspecialchars($medicine['vendor_name']) ?></strong> - <?= htmlspecialchars($medicine['category_name']) ?></p>
            <div class="split">
                <strong>Tk <?= number_format((float) $medicine['price'], 2) ?></strong>
                <span>Stock <?= (int) $medicine['availability'] ?></span>
            </div>
            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'customer'): ?>
                <form class="ajax-add-cart">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="medicine_id" value="<?= (int) $medicine['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int) $medicine['availability'] ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
