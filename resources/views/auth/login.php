<?php ob_start(); ?>
<div class="container-sm">
    <div class="card">
        <h1 class="text-center mb-4">Log in</h1>
        <form action="<?= e(url('/login')) ?>" method="POST">
            <?= csrf_field() ?>
            <div class="input-group">
                <label for="identifier">Username or Email</label>
                <input type="text" id="identifier" name="identifier" class="input" value="<?= old('identifier') ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input" required>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Log in</button>
            </div>
        </form>
        <?php if (setting_bool('registration_enabled', true)): ?>
            <p class="text-center mt-4 text-muted">
                Don't have an account? <a href="<?= e(url('/register')) ?>">Create a profile</a>.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
