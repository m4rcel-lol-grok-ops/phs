<?php ob_start(); ?>
<div style="text-align: center; padding: 4rem 1rem; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80vw; height: 80vw; max-width: 800px; max-height: 800px; background: radial-gradient(circle, var(--accent) 0%, transparent 60%); opacity: 0.1; z-index: -1;"></div>
    <h1 style="font-size: clamp(3rem, 8vw, 5rem); margin-bottom: 1rem; line-height: 1;">One link.<br>Zero bullshit.</h1>
    <p style="font-size: 1.2rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 2rem auto;">
        The black and orange link-in-bio platform for totally real creators. Share your links, customize your aesthetic, and embrace the parody.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php if (setting_bool('registration_enabled', true)): ?>
            <a href="<?= e(url('/register')) ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Claim your profile</a>
        <?php endif; ?>
        <a href="<?= e(url('/discover')) ?>" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">Explore</a>
    </div>
</div>

<div class="container mt-8 mb-8">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
        <div class="card text-center">
            <h3 style="font-size: 2.5rem; color: var(--accent);"><?= e(format_number($stats['profiles'] ?? 0)) ?></h3>
            <div class="text-muted">Active Profiles</div>
        </div>
        <div class="card text-center">
            <h3 style="font-size: 2.5rem; color: var(--accent);"><?= e(format_number($stats['links'] ?? 0)) ?></h3>
            <div class="text-muted">Links Shared</div>
        </div>
        <div class="card text-center">
            <h3 style="font-size: 2.5rem; color: var(--accent);"><?= e(format_number($stats['views'] ?? 0)) ?></h3>
            <div class="text-muted">Total Views</div>
        </div>
    </div>
</div>

<?php if (!empty($featured)): ?>
<div class="container mb-8">
    <h2 class="text-center mb-4">Featured Profiles</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach ($featured as $profile): ?>
            <a href="<?= e(url('/' . $profile['username'])) ?>" class="card" style="display: flex; align-items: center; gap: 1rem; text-decoration: none; transition: transform 0.2s;">
                <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background: var(--bg-elevated); flex-shrink: 0;">
                    <?php $avatarFile = upload_filename($profile['avatar'] ?? null); ?>
                    <?php if ($avatarFile !== null): ?>
                        <img src="<?= e('/uploads/avatars/' . $avatarFile) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--accent);color:#000;font-weight:bold;font-size:1.5rem;">
                            <?= e(strtoupper(substr($profile['display_name'] ?: $profile['username'], 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= e($profile['display_name'] ?: $profile['username']) ?>
                        <?php if ($profile['is_verified']): ?><span style="color:var(--accent);">✓</span><?php endif; ?>
                    </div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">@<?= e($profile['username']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div style="background: var(--bg-card); padding: 4rem 1rem; border-top: 1px solid var(--border); text-align: center;">
    <h2 style="margin-bottom: 1rem;">No explicit content.</h2>
    <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">
        We are a parody site. We host links, not videos. Any sexually explicit or NSFW material will result in an immediate ban. 
    </p>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
