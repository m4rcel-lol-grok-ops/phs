<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <div class="dash-header">
            <h1>Reports</h1>
            <a href="/admin" class="btn btn-ghost btn-sm">← Admin</a>
        </div>
        <div class="filter-tabs" style="margin-bottom:1.5rem">
            <a href="?status=pending" class="<?= $status === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=reviewed" class="<?= $status === 'reviewed' ? 'active' : '' ?>">Reviewed</a>
            <a href="?status=dismissed" class="<?= $status === 'dismissed' ? 'active' : '' ?>">Dismissed</a>
            <a href="?status=actioned" class="<?= $status === 'actioned' ? 'active' : '' ?>">Actioned</a>
        </div>
        <?php if (empty($reports)): ?>
            <p class="text-muted">No reports in this category.</p>
        <?php else: ?>
            <?php foreach ($reports as $r): ?>
            <div class="card" style="margin-bottom:1rem">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem">
                    <strong>#<?= (int)$r['id'] ?> — <?= e($r['report_type']) ?> on @<?= e($r['reported_username']) ?></strong>
                    <span class="text-muted" style="font-size:0.85rem"><?= e($r['created_at']) ?></span>
                </div>
                <p style="margin-bottom:0.75rem;color:var(--text-muted)"><?= e($r['reason']) ?></p>
                <p style="font-size:0.85rem;color:var(--text-dim)">Reporter: <?= e($r['reporter_username'] ?? 'Anonymous') ?></p>
                <?php if ($r['status'] === 'pending'): ?>
                <form method="post" action="/admin/reports" style="margin-top:1rem;display:flex;gap:0.5rem;flex-wrap:wrap;align-items:end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                    <div class="form-group" style="margin:0;flex:1;min-width:150px">
                        <input type="text" name="notes" class="form-input" placeholder="Admin notes">
                    </div>
                    <label class="form-check"><input type="checkbox" name="disable_user" value="1"> Disable user</label>
                    <button name="action" value="dismiss" class="btn btn-secondary btn-sm">Dismiss</button>
                    <button name="action" value="action" class="btn btn-primary btn-sm">Action</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
