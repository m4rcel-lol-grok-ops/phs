<?php ob_start(); ?>
<div class="auth-page">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="auth-sub">Log in to manage your extremely important profile.</p>

        <?= flash_alerts() ?>

        <form method="post" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="identifier">Username or email</label>
                <input type="text" id="identifier" name="identifier" class="form-input"
                       required autocomplete="username" autocapitalize="none" autocorrect="off"
                       value="<?= old('identifier') ?>" placeholder="admin">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <?php if (setting_bool('registration_enabled', true)): ?>
            <p class="auth-footer">No account? <a href="/register">Create one</a></p>
        <?php else: ?>
            <p class="auth-footer">Registration is currently closed.</p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
