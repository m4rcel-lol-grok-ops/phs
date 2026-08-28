<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <div class="dash-content">
        <div class="dash-header">
            <h1>Welcome back, internet celebrity.</h1>
        </div>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?= format_number((int)$profile['profile_views']) ?></div>
                <div class="stat-label">Profile Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= format_number((int)$totalClicks) ?></div>
                <div class="stat-label">Link Clicks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($links) ?></div>
                <div class="stat-label">Links</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                <div class="stat-label">Joined</div>
            </div>
        </div>
        <div class="card">
            <h3 style="margin-bottom:0.75rem">Your profile</h3>
            <p class="text-muted" style="margin-bottom:1rem">
                <a href="/<?= e($user['username']) ?>" target="_blank"><?= e(url($user['username'])) ?></a>
            </p>
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
                <a href="/dashboard/links" class="btn btn-primary btn-sm">Manage Links</a>
                <a href="/dashboard/appearance" class="btn btn-secondary btn-sm">Customize</a>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
