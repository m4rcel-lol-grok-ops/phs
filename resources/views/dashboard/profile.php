<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <div class="dash-content">
        <div class="dash-header"><h1>Edit Profile</h1></div>
        <p class="text-muted mb-2">Customize your extremely important internet presence.</p>

        <div class="card" style="margin-bottom:1.5rem">
            <h3 style="margin-bottom:1rem">Avatar</h3>
            <?php if ($profile['avatar']): ?>
                <img src="/uploads/avatars/<?= e($profile['avatar']) ?>" alt="" width="80" height="80" style="border-radius:50%;margin-bottom:1rem">
            <?php endif; ?>
            <form method="post" action="/dashboard/avatar" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="form-input" style="margin-bottom:0.75rem">
                <button type="submit" class="btn btn-primary btn-sm">Upload Avatar</button>
            </form>
        </div>

        <div class="card">
            <form method="post" action="/dashboard/profile">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="display_name">Display name</label>
                    <input type="text" id="display_name" name="display_name" class="form-input" value="<?= e($profile['display_name'] ?? '') ?>" maxlength="64">
                </div>
                <div class="form-group">
                    <label class="form-label" for="bio">Biography</label>
                    <textarea id="bio" name="bio" class="form-textarea" maxlength="500"><?= e($profile['bio'] ?? '') ?></textarea>
                    <p class="form-hint">Plain text. Max 500 characters.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pronouns">Pronouns</label>
                    <input type="text" id="pronouns" name="pronouns" class="form-input" value="<?= e($profile['pronouns'] ?? '') ?>" maxlength="32" placeholder="e.g. they/them">
                </div>
                <div class="form-group">
                    <label class="form-label" for="location">Location</label>
                    <input type="text" id="location" name="location" class="form-input" value="<?= e($profile['location'] ?? '') ?>" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-input" value="<?= e($profile['website'] ?? '') ?>" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_public" value="1" <?= ($profile['is_public'] ?? 1) ? 'checked' : '' ?>>
                        Public profile
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="show_in_discover" value="1" <?= ($profile['show_in_discover'] ?? 1) ? 'checked' : '' ?>>
                        Show in Discover
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
