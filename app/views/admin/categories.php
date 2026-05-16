<section class="form-shell">
    <h1>Category Management</h1>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <label>Name <input name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></label>
        <label>Type <select name="category_type" required>
            <option value="">Choose type</option>
            <option value="liquid" <?= ($edit['category_type'] ?? '') === 'liquid' ? 'selected' : '' ?>>Liquid</option>
            <option value="solid" <?= ($edit['category_type'] ?? '') === 'solid' ? 'selected' : '' ?>>Solid</option>
        </select></label>
        <button>Save Category</button>
    </form>
</section>
<section class="panel">
    <?php foreach ($categories as $category): ?>
        <div class="row">
            <span><?= htmlspecialchars($category['name']) ?> - <?= htmlspecialchars($category['category_type']) ?></span>
            <a class="button muted" href="<?= BASE_URL ?>?page=admin-categories&edit=<?= (int) $category['id'] ?>">Edit</a>
        </div>
    <?php endforeach; ?>
</section>
