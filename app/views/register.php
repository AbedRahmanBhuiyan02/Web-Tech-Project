<section class="form-shell">
    <h1>Create Account</h1>
    <form method="post" data-validate="register">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label>Name <input name="name" required><small><?= htmlspecialchars($errors['name'] ?? '') ?></small></label>
        <label>Email <input type="email" name="email" required><small><?= htmlspecialchars($errors['email'] ?? '') ?></small></label>
        <label>Password <input type="password" name="password" minlength="8" required><small><?= htmlspecialchars($errors['password'] ?? '') ?></small></label>
        <label>Role <select name="role"><option value="customer">Customer</option><option value="admin">Admin</option></select></label>
        <label>Address <input name="address" required><small><?= htmlspecialchars($errors['address'] ?? '') ?></small></label>
        <label>Phone <input name="phone" pattern="01[3-9][0-9]{8}" maxlength="11" required><small><?= htmlspecialchars($errors['phone'] ?? '') ?></small></label>
        <button type="submit">Register</button>
    </form>
</section>
