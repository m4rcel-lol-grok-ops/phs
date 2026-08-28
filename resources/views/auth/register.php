<?php ob_start(); ?>
<div class="container-sm">
    <div class="card">
        <h1 class="text-center mb-4">Create your profile</h1>
        <?php if (!setting_bool('registration_enabled', true)): ?>
            <div class="empty-state">
                <h3>Registration closed</h3>
                <p>We are not accepting new signups right now.</p>
            </div>
        <?php else: ?>
            <form action="<?= e(url('/register')) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="input" value="<?= old('username') ?>" required pattern="[a-zA-Z0-9_-]+" title="Alphanumeric, dashes, underscores">
                    <div class="form-help">This will be your link: <?= e(link_host(url('/'))) ?>/<strong>username</strong></div>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="input" value="<?= old('email') ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="input" required minlength="8">
                </div>
                <div class="input-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="input" required minlength="8">
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create profile</button>
                </div>
            </form>
            <p class="text-center mt-4 text-muted">
                By registering, you agree to the <a href="<?= e(url('/content-policy')) ?>">Content Policy</a>. No explicit content allowed.
            </p>
            <p class="text-center mt-4 text-muted">
                Already have an account? <a href="<?= e(url('/login')) ?>">Log in</a>.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
