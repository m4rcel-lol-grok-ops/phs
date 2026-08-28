<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <div class="dash-content">
        <div class="dash-header"><h1>Account</h1></div>

        <div class="card" style="margin-bottom:1.5rem">
            <h3 style="margin-bottom:1rem">Change email</h3>
            <form method="post" action="/dashboard/account">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="email">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="<?= e($user['email']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Update Email</button>
            </form>
        </div>

        <div class="card" style="margin-bottom:1.5rem">
            <h3 style="margin-bottom:1rem">Change password</h3>
            <form method="post" action="/dashboard/account">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New password</label>
                    <input type="password" name="new_password" class="form-input" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="new_password_confirm" class="form-input" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Change Password</button>
            </form>
        </div>

        <div class="card" style="border-color:var(--error)">
            <h3 style="margin-bottom:1rem;color:var(--error)">Delete account</h3>
            <p class="text-muted" style="margin-bottom:1rem">This permanently deletes your profile, links, and data. Type your username to confirm.</p>
            <form method="post" action="/dashboard/account" onsubmit="return confirm('Really delete your account forever?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <div class="form-group">
                    <input type="text" name="confirm_delete" class="form-input" placeholder="<?= e($user['username']) ?>" required>
                </div>
                <button type="submit" class="btn btn-danger btn-sm">Delete Account</button>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
