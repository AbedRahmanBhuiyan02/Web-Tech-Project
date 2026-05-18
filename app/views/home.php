<section class="hero">
    <div>
        <p class="eyebrow">Online medicine shop</p>
        <h1>Browse medicines by genre, vendor, and type</h1>
        <p>Find available medicines with vendor, category, stock, and price details.</p>
    </div>
</section>

<section class="panel">
    <form class="filters">
        <select name="genre">
            <option value="">All genres</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['name']) ?>" <?= $filters['genre'] === $category['name'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
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
        <button type="submit">Apply</button>
    </form>
</section>

<section class="grid cards" id="medicine-list">
    <?php foreach ($medicines as $medicine): ?>
        <article class="card medicine-card">
            <div class="image-fallback"><?= strtoupper(substr($medicine['name'], 0, 1)) ?></div>
            <span class="badge"><?= htmlspecialchars($medicine['category_type']) ?></span>
            <h3><?= htmlspecialchars($medicine['name']) ?></h3>
            <p><?= htmlspecialchars($medicine['description']) ?></p>
            <p><strong><?= htmlspecialchars($medicine['vendor_name']) ?></strong> - <?= htmlspecialchars($medicine['category_name']) ?></p>
            <div class="split">
                <strong>Tk <?= number_format((float) $medicine['price'], 2) ?></strong>
                <span>Stock <?= (int) $medicine['availability'] ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</section>
