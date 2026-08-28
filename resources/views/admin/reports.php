<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/admin/_nav.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Reports</h1>
        
        <div class="flex gap-4 mb-4" style="flex-wrap: wrap;">
            <a href="<?= e(url('/admin/reports?status=pending')) ?>" class="btn <?= ($status ?? 'pending') === 'pending' ? 'btn-primary' : '' ?>">Pending (<?= e($counts['pending'] ?? 0) ?>)</a>
            <a href="<?= e(url('/admin/reports?status=reviewed')) ?>" class="btn <?= ($status ?? '') === 'reviewed' ? 'btn-primary' : '' ?>">Reviewed (<?= e($counts['reviewed'] ?? 0) ?>)</a>
            <a href="<?= e(url('/admin/reports?status=dismissed')) ?>" class="btn <?= ($status ?? '') === 'dismissed' ? 'btn-primary' : '' ?>">Dismissed (<?= e($counts['dismissed'] ?? 0) ?>)</a>
        </div>

        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <h3>No reports</h3>
                <p>There are no reports in this category.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <?php foreach ($reports as $report): ?>
                    <div class="card" style="border-left: 4px solid <?= $report['status'] === 'pending' ? '#ef4444' : 'var(--border)' ?>;">
                        <div class="flex justify-between items-center mb-2">
                            <strong>Reported User: <a href="<?= e(url('/' . $report['reported_username'])) ?>" target="_blank">@<?= e($report['reported_username']) ?></a></strong>
                            <span class="text-muted"><?= e(time_ago($report['created_at'])) ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="badge" style="background:var(--bg-elevated);padding:2px 6px;border-radius:4px;font-size:0.8rem;"><?= e(strtoupper($report['report_type'])) ?></span>
                        </div>
                        <div class="mb-4" style="background: var(--bg); padding: 1rem; border-radius: var(--radius-sm);">
                            <?= e($report['reason']) ?>
                        </div>
                        
                        <?php if ($report['status'] === 'pending'): ?>
                            <form action="<?= e(url('/admin/reports')) ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="report_id" value="<?= e($report['id']) ?>">
                                <input type="hidden" name="disable_user" value="1">
                                <div class="input-group">
                                    <label>Action to take</label>
                                    <select name="action" class="select">
                                        <option value="review">Mark as Reviewed (No action)</option>
                                        <option value="dismiss">Dismiss (Invalid report)</option>
                                        <option value="action">Take Action (Disable user)</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Internal Notes (Optional)</label>
                                    <input type="text" name="notes" class="input">
                                </div>
                                <button type="submit" class="btn btn-primary">Process Report</button>
                            </form>
                        <?php else: ?>
                            <div class="text-muted">
                                Status: <strong><?= e(ucfirst($report['status'])) ?></strong><br>
                                Processed by: <?= e($report['reviewer_username'] ?? 'Unknown') ?>
                                <?php if ($report['admin_notes']): ?>
                                    <br>Notes: <?= e($report['admin_notes']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
