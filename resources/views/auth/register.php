<?php ob_start(); ?>
<div class="auth-page">
    <div class="auth-card">
        <h1>Become visible</h1>
        <p class="auth-sub">Create your profile. Takes about 30 seconds.</p>

        <?= flash_alerts() ?>

        <form method="post" action="/register">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input"
                       required pattern="[a-zA-Z0-9_]{3,32}" minlength="3" maxlength="32"
                       autocomplete="username" autocapitalize="none" autocorrect="off"
                       value="<?= old('username') ?>">
                <p class="form-hint">
                    3–32 characters: letters, numbers, underscores. Your page will live at
                    <code><?= e(rtrim((string)env('APP_URL', ''), '/')) ?>/yourname</code>
                </p>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       required autocomplete="email" value="<?= old('email') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input"
                       required minlength="8" autocomplete="new-password">
                <p class="form-hint">At least 8 characters.</p>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input"
                       required minlength="8" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create profile</button>
        </form>

        <p class="auth-footer">
            By signing up you agree to the <a href="/terms">Terms</a>
            and <a href="/content-policy">Content Policy</a>.<br>
            Already have an account? <a href="/login">Log in</a>
        </p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
