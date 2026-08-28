<?php ob_start(); ?>
<section class="section-tight">
    <div class="container">
        <div class="dash-header">
            <div>
                <h1>Admin</h1>
                <p>Everything happening on this instance.</p>
            </div>
        </div>

        <?php require __DIR__ . '/_nav.php'; ?>

        <?php if ($stats['reports'] > 0): ?>
            <div class="alert alert-info mb-3" role="status">
                <?= (int)$stats['reports'] ?> report<?= $stats['reports'] === 1 ? '' : 's' ?> waiting for review.
                <a href="/admin/reports">Open the queue</a>.
            </div>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['users'])) ?></div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['new_week'])) ?></div><div class="stat-label">New this week</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['links'])) ?></div><div class="stat-label">Links</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['views'])) ?></div><div class="stat-label">Profile views</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['clicks'])) ?></div><div class="stat-label">Link clicks</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['reports'])) ?></div><div class="stat-label">Pending reports</div></div>
            <div class="stat-card"><div class="stat-value"><?= e(format_number($stats['disabled'])) ?></div><div class="stat-label">Disabled users</div></div>
        </div>

        <div class="form-row" style="gap:1.25rem">
            <div class="card">
                <div class="card-head">
                    <h3>Newest users</h3>
                    <a href="/admin/users" class="btn btn-ghost btn-xs">All users →</a>
                </div>
                <?php if (empty($recentUsers)): ?>
                    <p class="text-muted text-sm">No users yet.</p>
                <?php else: ?>
                    <div class="link-list">
                        <?php foreach ($recentUsers as $u): ?>
                            <div class="link-item">
                                <span class="table-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string)$u['username'], 0, 1))) ?></span>
                                <div class="link-body">
                                    <strong><a href="/<?= e($u['username']) ?>" target="_blank" rel="noopener">@<?= e($u['username']) ?></a></strong>
                                    <span class="link-url"><?= e(time_ago((string)$u['created_at'])) ?></span>
                                </div>
                                <?php if ($u['is_disabled']): ?>
                                    <span class="badge badge-danger">Disabled</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-head"><h3>Recent admin actions</h3></div>
                <?php if (empty($recentActions)): ?>
                    <p class="text-muted text-sm">Nothing logged yet.</p>
                <?php else: ?>
                    <div class="link-list">
                        <?php foreach ($recentActions as $log): ?>
                            <div class="link-item">
                                <div class="link-body">
                                    <strong><?= e(str_replace('_', ' ', (string)$log['action'])) ?></strong>
                                    <span class="link-url">
                                        by @<?= e($log['admin_username'] ?? 'deleted user') ?>
                                        <?= $log['details'] ? '· ' . e((string)$log['details']) : '' ?>
                                    </span>
                                </div>
                                <span class="text-dim text-xs"><?= e(time_ago((string)$log['created_at'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
