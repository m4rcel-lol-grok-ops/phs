<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <h1>Account</h1>
                <p>Sign-in details and the big red button.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Account details</h3></div>
            <table>
                <tbody>
                    <tr><td class="text-muted">Username</td><td><strong>@<?= e($user['username']) ?></strong></td></tr>
                    <tr><td class="text-muted">Role</td><td>
                        <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-off' ?>"><?= e(ucfirst($user['role'])) ?></span>
                    </td></tr>
                    <tr><td class="text-muted">Verified</td><td>
                        <span class="badge <?= $user['is_verified'] ? 'badge-on' : 'badge-off' ?>">
                            <?= $user['is_verified'] ? 'Verified' : 'Not verified' ?>
                        </span>
                    </td></tr>
                    <tr><td class="text-muted">Member since</td><td><?= e(date('j F Y', strtotime((string)$user['created_at']) ?: time())) ?></td></tr>
                </tbody>
            </table>
            <p class="form-hint mt-2">Usernames cannot be changed — your public link depends on it.</p>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Change email</h3></div>
            <form method="post" action="/dashboard/account">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="email">
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" id="email" name="email" class="form-input"
                           required value="<?= e($user['email']) ?>" autocomplete="email">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email_password">Confirm with your password</label>
                    <input type="password" id="email_password" name="current_password" class="form-input"
                           required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Update email</button>
            </form>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Change password</h3></div>
            <form method="post" action="/dashboard/account">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" class="form-input"
                           required autocomplete="current-password">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input"
                               required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_password_confirm">Confirm new password</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-input"
                               required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Change password</button>
            </form>
        </div>

        <div class="card card-danger">
            <div class="card-head"><h3>Delete account</h3></div>
            <div class="notice notice-danger mb-2">
                <strong>This cannot be undone.</strong> Your profile, links, uploads, and all
                statistics are permanently removed, and your username becomes available again.
            </div>
            <form method="post" action="/dashboard/account"
                  data-confirm="Permanently delete your account and everything on it?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="confirm_delete">Type <code><?= e($user['username']) ?></code> to confirm</label>
                        <input type="text" id="confirm_delete" name="confirm_delete" class="form-input"
                               required placeholder="<?= e($user['username']) ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="delete_password">Your password</label>
                        <input type="password" id="delete_password" name="current_password" class="form-input"
                               required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn btn-danger btn-sm">Delete my account forever</button>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
