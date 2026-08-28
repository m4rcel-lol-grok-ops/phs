<?php
ob_start();
$tabs = ['pending' => 'Pending', 'reviewed' => 'Reviewed', 'actioned' => 'Actioned', 'dismissed' => 'Dismissed'];
?>
<section class="section-tight">
    <div class="container">
        <div class="dash-header">
            <div>
                <h1>Reports</h1>
                <p>User-submitted reports, newest first.</p>
            </div>
        </div>

        <?php require __DIR__ . '/_nav.php'; ?>

        <div class="toolbar">
            <div class="filter-tabs">
                <?php foreach ($tabs as $key => $label): ?>
                    <a href="/admin/reports?status=<?= e($key) ?>" class="<?= $status === $key ? 'active' : '' ?>">
                        <?= e($label) ?>
                        <?php if (!empty($counts[$key])): ?><span class="count">(<?= (int)$counts[$key] ?>)</span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <div class="icon" aria-hidden="true">🛡️</div>
                <h3>Nothing <?= e($status) ?></h3>
                <p>
                    <?= $status === 'pending'
                        ? 'The moderation queue is empty. Enjoy it while it lasts.'
                        : 'No reports have this status yet.' ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($reports as $r): ?>
                <div class="card mb-2">
                    <div class="card-head">
                        <div>
                            <h3>
                                <span class="badge badge-off">#<?= (int)$r['id'] ?></span>
                                <?= e(ucfirst((string)$r['report_type'])) ?> report on
                                <a href="/<?= e($r['reported_username']) ?>" target="_blank" rel="noopener">@<?= e($r['reported_username']) ?></a>
                            </h3>
                            <p class="text-dim text-xs mt-1">
                                Reported by <?= $r['reporter_username'] ? '@' . e($r['reporter_username']) : 'an anonymous visitor' ?>
                                · <?= e(time_ago((string)$r['created_at'])) ?>
                                <?php if (!empty($r['reviewer_username'])): ?>
                                    · reviewed by @<?= e($r['reviewer_username']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="badge <?= $r['status'] === 'pending' ? 'badge-admin' : 'badge-off' ?>">
                            <?= e(ucfirst((string)$r['status'])) ?>
                        </span>
                    </div>

                    <blockquote class="notice mb-2"><?= nl2br(e((string)$r['reason'])) ?></blockquote>

                    <?php if (!empty($r['admin_notes'])): ?>
                        <p class="text-muted text-sm mb-2"><strong>Notes:</strong> <?= e((string)$r['admin_notes']) ?></p>
                    <?php endif; ?>

                    <?php if ($r['status'] === 'pending'): ?>
                        <form method="post" action="/admin/reports">
                            <?= csrf_field() ?>
                            <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                            <div class="form-group">
                                <label class="form-label" for="notes-<?= (int)$r['id'] ?>">Moderator notes <span class="text-dim">(optional)</span></label>
                                <input type="text" id="notes-<?= (int)$r['id'] ?>" name="notes" class="form-input"
                                       maxlength="2000" placeholder="What did you decide, and why?">
                            </div>
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="disable_user" value="1">
                                    <span class="form-check-text">
                                        Also disable @<?= e($r['reported_username']) ?>
                                        <small>Only applies when you choose “Take action”. Administrators are never disabled this way.</small>
                                    </span>
                                </label>
                            </div>
                            <div class="flex gap-1 flex-wrap">
                                <button name="action" value="action" class="btn btn-primary btn-sm">Take action</button>
                                <button name="action" value="review" class="btn btn-secondary btn-sm">Mark reviewed</button>
                                <button name="action" value="dismiss" class="btn btn-ghost btn-sm">Dismiss</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
