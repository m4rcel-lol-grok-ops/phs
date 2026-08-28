<?php
ob_start();
$profileUrl = url($user['username']);
$peak = max(1, max(array_column($viewSeries, 'total')));
$recentViews = array_sum(array_column($viewSeries, 'total'));
$enabledLinks = array_filter($links, static fn($l) => (int)$l['is_enabled'] === 1);
$topLinks = $links;
usort($topLinks, static fn($a, $b) => (int)$b['click_count'] <=> (int)$a['click_count']);
$topLinks = array_slice($topLinks, 0, 5);
?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <h1>Welcome back, internet celebrity.</h1>
                <p>Here is how your unnecessarily dramatic profile is doing.</p>
            </div>
            <a href="/<?= e($user['username']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                View public page ↗
            </a>
        </div>

        <?php if (!$profile['is_public']): ?>
            <div class="alert alert-info mb-3" role="status">
                Your profile is currently private, so nobody else can see it.
                <a href="/dashboard/profile">Make it public</a>.
            </div>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?= e(format_number((int)$profile['profile_views'])) ?></div>
                <div class="stat-label">Profile views</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= e(format_number((int)$totalClicks)) ?></div>
                <div class="stat-label">Link clicks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count($enabledLinks) ?><?php if (count($links) !== count($enabledLinks)): ?><span class="text-dim" style="font-size:1rem">/<?= count($links) ?></span><?php endif; ?></div>
                <div class="stat-label">Active links</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= e(date('M Y', strtotime((string)$user['created_at']) ?: time())) ?></div>
                <div class="stat-label">Joined</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-head">
                <h3>Views, last 14 days</h3>
                <span class="text-dim text-sm"><?= e(format_number($recentViews)) ?> total</span>
            </div>
            <?php if ($recentViews === 0): ?>
                <p class="text-muted text-sm">No views yet. Share your link somewhere and watch this fill up.</p>
            <?php else: ?>
                <div class="sparkline" role="img"
                     aria-label="Daily profile views for the last 14 days, <?= e(format_number($recentViews)) ?> in total">
                    <?php foreach ($viewSeries as $point): ?>
                        <?php $height = $point['total'] > 0 ? max(6, (int)round($point['total'] / $peak * 100)) : 3; ?>
                        <div class="sparkline-bar<?= $point['total'] === 0 ? ' is-empty' : '' ?>"
                             style="height: <?= $height ?>%"
                             title="<?= e(date('D j M', strtotime($point['day']))) ?>: <?= (int)$point['total'] ?> views"></div>
                    <?php endforeach; ?>
                </div>
                <div class="sparkline-axis">
                    <span><?= e(date('j M', strtotime($viewSeries[0]['day']))) ?></span>
                    <span>Today</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="card mb-3">
            <div class="card-head"><h3>Your public link</h3></div>
            <div class="profile-url-box">
                <code><?= e($profileUrl) ?></code>
                <button type="button" class="btn btn-secondary btn-xs" data-copy="<?= e($profileUrl) ?>">Copy</button>
            </div>
            <div class="flex gap-1 flex-wrap">
                <a href="/dashboard/links" class="btn btn-primary btn-sm">Manage links</a>
                <a href="/dashboard/appearance" class="btn btn-secondary btn-sm">Customize look</a>
                <a href="/dashboard/profile" class="btn btn-ghost btn-sm">Edit profile</a>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>Top links</h3>
                <a href="/dashboard/links" class="btn btn-ghost btn-xs">Manage →</a>
            </div>
            <?php if (empty($topLinks)): ?>
                <p class="text-muted text-sm">
                    You have not added any links yet.
                    <a href="/dashboard/links">Add your first one</a>.
                </p>
            <?php else: ?>
                <div class="link-list">
                    <?php foreach ($topLinks as $link): ?>
                        <div class="link-item<?= $link['is_enabled'] ? '' : ' is-disabled' ?>">
                            <span class="link-emoji-badge" aria-hidden="true"><?= e($link['emoji'] ?: '🔗') ?></span>
                            <div class="link-body">
                                <strong><?= e($link['title']) ?></strong>
                                <span class="link-url"><?= e(link_host($link['url']) ?: $link['url']) ?></span>
                            </div>
                            <span class="text-dim text-sm"><?= e(format_number((int)$link['click_count'])) ?> clicks</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
