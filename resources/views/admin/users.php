<?php
ob_start();
$me = current_user();
$query = static fn(array $extra = []): string =>
    '?' . http_build_query(array_merge(['q' => $q, 'filter' => $filter, 'page' => $page], $extra));
?>
<section class="section-tight">
    <div class="container">
        <div class="dash-header">
            <div>
                <h1>Users</h1>
                <p><?= e(format_number($total)) ?> user<?= $total === 1 ? '' : 's' ?> match this view.</p>
            </div>
        </div>

        <?php require __DIR__ . '/_nav.php'; ?>

        <div class="toolbar">
            <form method="get" action="/admin/users">
                <input type="search" name="q" class="form-input" value="<?= e($q) ?>"
                       placeholder="Search username or email…" aria-label="Search users">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($q !== ''): ?>
                    <a href="/admin/users?filter=<?= e($filter) ?>" class="btn btn-ghost btn-sm">Clear</a>
                <?php endif; ?>
            </form>
            <div class="filter-tabs">
                <?php foreach (['all' => 'All', 'admins' => 'Admins', 'verified' => 'Verified', 'disabled' => 'Disabled'] as $key => $label): ?>
                    <a href="/admin/users?filter=<?= e($key) ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
                       class="<?= $filter === $key ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="icon" aria-hidden="true">🔍</div>
                <h3>No users found</h3>
                <p>Nothing matches that search or filter.</p>
                <a href="/admin/users" class="btn btn-secondary btn-sm">Reset filters</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $isSelf = (int)$u['id'] === (int)$me['id'];
                        $isAdmin = $u['role'] === 'admin';
                        $locked = !empty($u['locked_until']) && strtotime((string)$u['locked_until']) > time();
                        ?>
                        <tr>
                            <td>
                                <div class="table-user">
                                    <span class="table-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string)$u['username'], 0, 1))) ?></span>
                                    <div>
                                        <a href="/<?= e($u['username']) ?>" target="_blank" rel="noopener">@<?= e($u['username']) ?></a>
                                        <?php if ($isSelf): ?><span class="text-dim text-xs"> (you)</span><?php endif; ?>
                                        <?php if (!empty($u['display_name'])): ?>
                                            <div class="text-dim text-xs"><?= e($u['display_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted text-sm"><?= e($u['email']) ?></td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    <?php if ($isAdmin): ?><span class="badge badge-admin">Admin</span><?php endif; ?>
                                    <?php if ($u['is_verified']): ?><span class="badge badge-on">Verified</span><?php endif; ?>
                                    <?php if ($u['is_disabled']): ?><span class="badge badge-danger">Disabled</span><?php endif; ?>
                                    <?php if ($locked): ?><span class="badge badge-danger">Locked</span><?php endif; ?>
                                    <?php if (!$isAdmin && !$u['is_verified'] && !$u['is_disabled'] && !$locked): ?>
                                        <span class="badge badge-off">Active</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-muted"><?= e(format_number((int)($u['profile_views'] ?? 0))) ?></td>
                            <td class="text-dim text-sm"><?= e(time_ago((string)$u['created_at'])) ?></td>
                            <td>
                                <form method="post" action="/admin/users" class="table-actions">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">

                                    <?php if ($u['is_disabled']): ?>
                                        <button name="action" value="enable" class="btn btn-xs btn-secondary">Enable</button>
                                    <?php elseif (!$isAdmin): ?>
                                        <button name="action" value="disable" class="btn btn-xs btn-secondary"
                                                data-confirm="Disable @<?= e($u['username']) ?>? Their profile becomes unreachable.">Disable</button>
                                    <?php endif; ?>

                                    <?php if ($u['is_verified']): ?>
                                        <button name="action" value="unverify" class="btn btn-xs btn-ghost">Unverify</button>
                                    <?php else: ?>
                                        <button name="action" value="verify" class="btn btn-xs btn-ghost">Verify</button>
                                    <?php endif; ?>

                                    <?php if ($locked): ?>
                                        <button name="action" value="unlock" class="btn btn-xs btn-ghost">Unlock</button>
                                    <?php endif; ?>

                                    <?php if ($isAdmin && !$isSelf): ?>
                                        <button name="action" value="demote" class="btn btn-xs btn-ghost"
                                                data-confirm="Remove administrator access from @<?= e($u['username']) ?>?">Demote</button>
                                    <?php elseif (!$isAdmin): ?>
                                        <button name="action" value="promote" class="btn btn-xs btn-ghost"
                                                data-confirm="Give @<?= e($u['username']) ?> full administrator access?">Promote</button>
                                        <button name="action" value="reset_password" class="btn btn-xs btn-ghost"
                                                data-confirm="Reset the password for @<?= e($u['username']) ?>? They will need the temporary password shown afterwards.">Reset PW</button>
                                        <button name="action" value="delete" class="btn btn-xs btn-outline-danger"
                                                data-confirm="Permanently delete @<?= e($u['username']) ?> and all their data?">Delete</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="/admin/users<?= e($query(['page' => $page - 1])) ?>">← Prev</a>
                    <?php endif; ?>
                    <span class="current">Page <?= (int)$page ?> of <?= (int)$totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="/admin/users<?= e($query(['page' => $page + 1])) ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
