<?php ob_start(); ?>
<div class="auth-page">
    <div class="auth-card">
        <h1>Become visible</h1>
        <p class="auth-sub">Create your profile. Takes about 30 seconds.</p>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <form method="post" action="/register">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" required pattern="[a-zA-Z0-9_]{3,32}" value="<?= old('username') ?>" autocomplete="username">
                <p class="form-hint">3–32 characters. Letters, numbers, underscores.</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required value="<?= old('email') ?>" autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Profile</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="/login">Log in</a></p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
