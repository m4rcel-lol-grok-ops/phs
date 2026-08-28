<?php ob_start(); ?>
<section class="hero">
    <div class="container">
        <span class="eyebrow">Parody project · Self-hostable</span>
        <h1>Your links deserve <span class="accent">better</span>.</h1>
        <p class="subtitle">
            Build your own ridiculous little corner of the internet — one page, all your links,
            an unnecessarily dramatic amount of orange.
        </p>
        <div class="hero-actions">
            <?php if (is_logged_in()): ?>
                <a href="/dashboard" class="btn btn-primary btn-lg">Go to dashboard</a>
            <?php elseif (setting_bool('registration_enabled', true)): ?>
                <a href="/register" class="btn btn-primary btn-lg">Create your profile</a>
            <?php endif; ?>
            <a href="/discover" class="btn btn-secondary btn-lg">Browse profiles</a>
        </div>
        <p class="hero-note">
            No subscriptions. No mysterious algorithms.<br>
            Just you, your links, and questionable taste.
        </p>

        <?php if ($stats['profiles'] > 0): ?>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-value"><?= e(format_number($stats['profiles'])) ?></div>
                    <div class="hero-stat-label">Profiles</div>
                </div>
                <div>
                    <div class="hero-stat-value"><?= e(format_number($stats['links'])) ?></div>
                    <div class="hero-stat-label">Links</div>
                </div>
                <div>
                    <div class="hero-stat-value"><?= e(format_number($stats['views'])) ?></div>
                    <div class="hero-stat-label">Page views</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($featured)): ?>
<section class="section-tight">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title">People already being unnecessarily visible</h2>
            <p class="section-sub">A few profiles from this instance.</p>
        </div>
        <div class="discover-grid">
            <?php foreach ($featured as $p): ?>
                <?php $avatar = upload_filename($p['avatar'] ?? null); ?>
                <a href="/<?= e($p['username']) ?>" class="discover-card">
                    <?php if ($avatar !== null): ?>
                        <img src="/uploads/avatars/<?= e($avatar) ?>" alt="" class="discover-avatar"
                             width="72" height="72" loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="discover-avatar-ph" aria-hidden="true">
                            <?= e(mb_strtoupper(mb_substr((string)($p['display_name'] ?: $p['username']), 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <div class="discover-name">
                        <?= e($p['display_name'] ?: $p['username']) ?>
                        <?php if ($p['is_verified']): ?><span class="verified-badge" title="Verified">✓</span><?php endif; ?>
                    </div>
                    <div class="discover-user">@<?= e($p['username']) ?></div>
                    <?php if (!empty($p['bio'])): ?>
                        <div class="discover-bio"><?= e($p['bio']) ?></div>
                    <?php endif; ?>
                    <div class="discover-foot">
                        <span><?= e(format_number((int)$p['profile_views'])) ?> views</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="text-center mt-3"><a href="/discover" class="btn btn-secondary btn-sm">See all profiles →</a></p>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title">Why does this exist?</h2>
            <p class="section-sub">Honestly? Because the internet needed one more bio-link site with extra orange.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon" aria-hidden="true">🎨</div>
                <h3>Themes &amp; customization</h3>
                <p>Six themes, or override every colour yourself. Fonts, backgrounds, gradients, and optional effects.</p>
            </div>
            <div class="feature-card">
                <div class="icon" aria-hidden="true">🔗</div>
                <h3>Unlimited links</h3>
                <p>Add as many as you want. Drag to reorder, hide without deleting, track every click.</p>
            </div>
            <div class="feature-card">
                <div class="icon" aria-hidden="true">📊</div>
                <h3>Real statistics</h3>
                <p>Profile views and link clicks, deduplicated so a refresh does not inflate your numbers.</p>
            </div>
            <div class="feature-card">
                <div class="icon" aria-hidden="true">🎵</div>
                <h3>Background music</h3>
                <p>Attach a track. It never autoplays — visitors decide whether they want the vibe.</p>
            </div>
            <div class="feature-card">
                <div class="icon" aria-hidden="true">✨</div>
                <h3>Visual effects</h3>
                <p>Particles, glow, snow, CRT, scanlines. Optional, and switched off for reduced-motion visitors.</p>
            </div>
            <div class="feature-card">
                <div class="icon" aria-hidden="true">🔒</div>
                <h3>Self-hostable</h3>
                <p>Your data, your server, one Docker command. No corporate overlords required.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!is_logged_in() && setting_bool('registration_enabled', true)): ?>
<section class="section" style="padding-top:0">
    <div class="container text-center">
        <h2 class="section-title">Become unnecessarily visible</h2>
        <p class="section-sub">It takes about 30 seconds. Your future self will either thank you or question everything.</p>
        <a href="/register" class="btn btn-primary btn-lg">Create your profile</a>
    </div>
</section>
<?php endif; ?>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
