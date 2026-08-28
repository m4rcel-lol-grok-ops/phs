<?php ob_start(); ?>
<div class="auth-page">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="auth-sub">Log in to manage your extremely important profile.</p>
        <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
        <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
        <form method="post" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required value="<?= old('email') ?>" autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
        <p class="auth-footer">No account? <a href="/register">Create one</a></p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
