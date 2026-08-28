<?php
ob_start();
$avatar = upload_filename($profile['avatar'] ?? null);
?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <h1>Edit profile</h1>
                <p>Who you are, and who gets to see it.</p>
            </div>
            <a href="/<?= e($user['username']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Preview ↗</a>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Avatar</h3></div>
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <?php if ($avatar !== null): ?>
                    <img src="/uploads/avatars/<?= e($avatar) ?>" alt="Your current avatar"
                         width="72" height="72" style="border-radius:50%;object-fit:cover">
                <?php else: ?>
                    <div class="table-avatar" style="width:72px;height:72px;font-size:1.6rem" aria-hidden="true">
                        <?= e(mb_strtoupper(mb_substr((string)($profile['display_name'] ?: $user['username']), 0, 1))) ?>
                    </div>
                <?php endif; ?>
                <p class="text-muted text-sm">JPG, PNG, or WebP. Square images look best.</p>
            </div>
            <form method="post" action="/dashboard/avatar" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="sr-only" for="avatar">Avatar image file</label>
                    <input type="file" id="avatar" name="avatar" class="form-file"
                           accept="image/jpeg,image/png,image/webp" required>
                </div>
                <div class="field-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Upload avatar</button>
                </div>
            </form>
            <?php if ($avatar !== null): ?>
                <form method="post" action="/dashboard/avatar/delete" class="mt-2"
                      data-confirm="Remove your avatar?">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-ghost btn-xs">Remove avatar</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Details</h3></div>
            <form method="post" action="/dashboard/profile">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="display_name">Display name</label>
                    <input type="text" id="display_name" name="display_name" class="form-input"
                           maxlength="64" value="<?= e($profile['display_name'] ?? '') ?>"
                           placeholder="<?= e($user['username']) ?>">
                    <p class="form-hint">Leave blank to use your username.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bio">Biography</label>
                    <textarea id="bio" name="bio" class="form-textarea" maxlength="500"
                              placeholder="Tell people what they are looking at."><?= e($profile['bio'] ?? '') ?></textarea>
                    <p class="form-hint">Plain text, up to 500 characters. Line breaks are kept.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="pronouns">Pronouns</label>
                        <input type="text" id="pronouns" name="pronouns" class="form-input"
                               maxlength="32" value="<?= e($profile['pronouns'] ?? '') ?>" placeholder="they/them">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input"
                               maxlength="100" value="<?= e($profile['location'] ?? '') ?>" placeholder="The internet">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-input"
                           maxlength="255" value="<?= e($profile['website'] ?? '') ?>" placeholder="https://example.com">
                    <p class="form-hint">Shown next to your location on your public page.</p>
                </div>

                <hr>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_public" value="1" <?= ($profile['is_public'] ?? 1) ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            Public profile
                            <small>When off, only you can open your page. Nobody else can view it.</small>
                        </span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="show_in_discover" value="1" <?= ($profile['show_in_discover'] ?? 1) ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            List me on Discover
                            <small>Your page stays reachable by direct link either way.</small>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Save profile</button>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
