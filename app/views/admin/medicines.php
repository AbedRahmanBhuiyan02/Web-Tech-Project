<section class="form-shell wide">
    <h1>Medicine Management</h1>
    <form method="post" enctype="multipart/form-data" data-validate="medicine">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit['image_path'] ?? '') ?>">
        <label>Name <input name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></label>
        <label>Category <select name="category_id" required>
            <option value="">Choose category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) ($edit['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?> (<?= htmlspecialchars($category['category_type']) ?>)</option>
            <?php endforeach; ?>
        </select></label>
        <label>Vendor <input name="vendor_name" value="<?= htmlspecialchars($edit['vendor_name'] ?? '') ?>" required></label>
        <label>Price <input type="number" step="0.01" min="0.01" name="price" value="<?= htmlspecialchars($edit['price'] ?? '') ?>" required></label>
        <label>Stock <input type="number" min="0" name="availability" value="<?= htmlspecialchars($edit['availability'] ?? '') ?>" required></label>
        <label>Description <textarea name="description" required><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></label>
        <label>Image <input type="file" name="image" accept="image/png,image/jpeg"></label>
        <button>Save Medicine</button>
    </form>
</section>
<section class="panel">
    <h2>Medicines</h2>
    <?php foreach ($medicines as $medicine): ?>
        <div class="row">
            <span><?= htmlspecialchars($medicine['name']) ?> - <?= htmlspecialchars($medicine['vendor_name']) ?></span>
            <div class="actions">
                <a class="button muted" href="<?= BASE_URL ?>?page=admin-medicines&edit=<?= (int) $medicine['id'] ?>">Edit</a>
                <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $medicine['id'] ?>"><button class="danger">Delete</button></form>
            </div>
        </div>
    <?php endforeach; ?>
</section>
