<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <div class="dash-header">
            <h1>Users</h1>
            <a href="/admin" class="btn btn-ghost btn-sm">← Admin</a>
        </div>
        <form method="get" class="filters" style="margin-bottom:1.5rem">
            <input type="search" name="q" class="form-input" value="<?= e($q) ?>" placeholder="Search username or email...">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
        </form>
        <div class="table-wrap card" style="padding:0">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td>
                            <a href="/<?= e($u['username']) ?>" target="_blank">@<?= e($u['username']) ?></a>
                            <?php if ($u['is_verified']): ?> ✓<?php endif; ?>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['role']) ?></td>
                        <td><?= format_number((int)($u['profile_views'] ?? 0)) ?></td>
                        <td><?= $u['is_disabled'] ? '<span style="color:var(--error)">Disabled</span>' : 'Active' ?></td>
                        <td>
                            <form method="post" action="/admin/users" style="display:inline-flex;gap:0.25rem;flex-wrap:wrap">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <?php if ($u['is_disabled']): ?>
                                    <button name="action" value="enable" class="btn btn-sm btn-secondary">Enable</button>
                                <?php else: ?>
                                    <button name="action" value="disable" class="btn btn-sm btn-secondary">Disable</button>
                                <?php endif; ?>
                                <?php if ($u['is_verified']): ?>
                                    <button name="action" value="unverify" class="btn btn-sm btn-ghost">Unverify</button>
                                <?php else: ?>
                                    <button name="action" value="verify" class="btn btn-sm btn-ghost">Verify</button>
                                <?php endif; ?>
                                <button name="action" value="reset_password" class="btn btn-sm btn-ghost">Reset PW</button>
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <button name="action" value="delete" class="btn btn-sm btn-danger" data-confirm="Delete this user permanently?">Delete</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $totalPages = max(1, (int)ceil($total / $perPage)); if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>">← Prev</a><?php endif; ?>
            <span class="active"><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>">Next →</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
