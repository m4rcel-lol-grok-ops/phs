<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/dashboard/_sidebar.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Account Settings</h1>
        
        <div class="card mb-4">
            <h2>Profile Visibility</h2>
            <form action="<?= e(url('/dashboard/account')) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="visibility">
                
                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="is_public" name="is_public" value="1" <?= $profile['is_public'] ? 'checked' : '' ?>>
                    <label for="is_public">Profile is public</label>
                </div>
                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="show_in_discover" name="show_in_discover" value="1" <?= $profile['show_in_discover'] ? 'checked' : '' ?>>
                    <label for="show_in_discover">Show in Discovery directory</label>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Visibility</button>
                </div>
            </form>
        </div>

        <div class="card mb-4">
            <h2>Update Email</h2>
            <form action="<?= e(url('/dashboard/account')) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="email">
                
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="input" value="<?= old('email') ?? e($user['email']) ?>" required>
                </div>
                <div class="input-group">
                    <label for="email_current_password">Confirm with your password</label>
                    <input type="password" id="email_current_password" name="current_password" class="input" required autocomplete="current-password">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn">Update Email</button>
                </div>
            </form>
        </div>

        <div class="card mb-4">
            <h2>Update Password</h2>
            <form action="<?= e(url('/dashboard/account')) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                
                <div class="input-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="input" required>
                </div>
                <div class="input-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="input" required minlength="8">
                </div>
                <div class="input-group">
                    <label for="new_password_confirm">Confirm New Password</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" class="input" required minlength="8">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn">Update Password</button>
                </div>
            </form>
        </div>

        <div class="card" style="border-color: #ef4444;">
            <h2 style="color: #ef4444;">Danger Zone</h2>
            <p class="mb-4">Once you delete your account, there is no going back. Please be certain.</p>
            <form action="<?= e(url('/dashboard/account')) ?>" method="POST" data-confirm="Are you absolutely sure you want to delete your account? This action cannot be undone.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">

                <div class="input-group">
                    <label for="confirm_delete">Type <code><?= e($user['username']) ?></code> to confirm</label>
                    <input type="text" id="confirm_delete" name="confirm_delete" class="input"
                           placeholder="<?= e($user['username']) ?>" required autocomplete="off">
                </div>
                <div class="input-group">
                    <label for="delete_current_password">Your password</label>
                    <input type="password" id="delete_current_password" name="current_password" class="input"
                           required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-danger">Delete Account</button>
            </form>
        </div>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
