<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/dashboard/_sidebar.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Profile Details</h1>
        
        <div class="card mb-4">
            <h2>Images</h2>
            <div style="display:flex; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom: 0.5rem;">Avatar</label>
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-elevated); margin-bottom: 1rem; overflow: hidden; display: flex; align-items:center; justify-content:center;">
                        <?php $avatarFile = upload_filename($profile['avatar'] ?? null); ?>
                        <?php if ($avatarFile !== null): ?>
                            <img src="/uploads/avatars/<?= e($avatarFile) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <span style="font-size:2rem;color:var(--text-muted);">?</span>
                        <?php endif; ?>
                    </div>
                    <form action="<?= e(url('/dashboard/avatar')) ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" name="avatar" accept="image/png, image/jpeg, image/webp" required class="input" style="padding: 0.25rem;">
                        <button type="submit" class="btn btn-primary btn-sm mt-4">Upload</button>
                    </form>
                    <?php if ($profile['avatar']): ?>
                    <form action="<?= e(url('/dashboard/avatar/delete')) ?>" method="POST" style="margin-top: 0.5rem;" data-confirm="Delete avatar?">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <?php endif; ?>
                </div>

                <div style="flex: 1; min-width: 250px;">
                    <label style="display:block; font-weight:600; margin-bottom: 0.5rem;">Banner</label>
                    <div style="width: 100%; height: 100px; border-radius: var(--radius-sm); background: var(--bg-elevated); margin-bottom: 1rem; overflow: hidden; display: flex; align-items:center; justify-content:center;">
                        <?php $bannerFile = upload_filename($profile['banner'] ?? null); ?>
                        <?php if ($bannerFile !== null): ?>
                            <img src="/uploads/banners/<?= e($bannerFile) ?>" alt="Banner" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <span style="color:var(--text-muted);">No banner</span>
                        <?php endif; ?>
                    </div>
                    <form action="<?= e(url('/dashboard/banner')) ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" name="banner" accept="image/png, image/jpeg, image/webp" required class="input" style="padding: 0.25rem;">
                        <button type="submit" class="btn btn-primary btn-sm mt-4">Upload</button>
                    </form>
                    <?php if ($profile['banner']): ?>
                    <form action="<?= e(url('/dashboard/banner/delete')) ?>" method="POST" style="margin-top: 0.5rem;" data-confirm="Delete banner?">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Information</h2>
            <form action="<?= e(url('/dashboard/profile')) ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="input-group">
                    <label for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" class="input" value="<?= old('display_name') ?? e($profile['display_name']) ?>">
                </div>

                <div class="input-group">
                    <label for="pronouns">Pronouns</label>
                    <input type="text" id="pronouns" name="pronouns" class="input" value="<?= old('pronouns') ?? e($profile['pronouns']) ?>" placeholder="e.g. they/them">
                </div>

                <div class="input-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="textarea"><?= old('bio') ?? e($profile['bio']) ?></textarea>
                </div>

                <div class="input-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" class="input" value="<?= old('location') ?? e($profile['location']) ?>">
                </div>

                <div class="input-group">
                    <label for="website">Primary Website</label>
                    <input type="url" id="website" name="website" class="input" value="<?= old('website') ?? e($profile['website']) ?>">
                </div>

                <hr style="border-color: var(--border); margin: 2rem 0;">
                <h2>Music Player (Optional)</h2>

                <div class="input-group">
                    <label for="music_url">Audio URL (mp3/wav)</label>
                    <input type="url" id="music_url" name="music_url" class="input" value="<?= old('music_url') ?? e($profile['music_url']) ?>">
                </div>
                <div class="input-group">
                    <label for="music_title">Track Title</label>
                    <input type="text" id="music_title" name="music_title" class="input" value="<?= old('music_title') ?? e($profile['music_title']) ?>">
                </div>
                <div class="input-group">
                    <label for="music_artist">Artist</label>
                    <input type="text" id="music_artist" name="music_artist" class="input" value="<?= old('music_artist') ?? e($profile['music_artist']) ?>">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
