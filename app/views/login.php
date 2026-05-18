<section class="form-shell">
    <h1>Login</h1>
    <form method="post" data-validate="login">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <label>Email <input type="email" name="email" required></label>
        <label>Password <input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button type="submit">Login</button>
    </form>
</section>
