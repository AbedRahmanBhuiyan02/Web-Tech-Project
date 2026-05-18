<section class="form-shell">
    <h1>Profile</h1>
    <form method="post" enctype="multipart/form-data" data-validate="profile">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label>Name <input name="name" value="<?= htmlspecialchars($user['name']) ?>" required></label>
        <label>Email <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label>
        <label>Address <input name="address" value="<?= htmlspecialchars($user['address']) ?>" required></label>
        <label>Phone <input name="phone" value="<?= htmlspecialchars($user['phone']) ?>" pattern="01[3-9][0-9]{8}" maxlength="11" required><small><?= htmlspecialchars($errors['phone'] ?? '') ?></small></label>
        <label>Profile picture <input type="file" name="profile_picture" accept="image/png,image/jpeg"></label>
        <label>Current password <input type="password" name="current_password"></label>
        <label>New password <input type="password" name="new_password" minlength="8"></label>
        <button type="submit">Save Profile</button>
    </form>
</section>
