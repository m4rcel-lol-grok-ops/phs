<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/admin/_nav.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Users</h1>
        
        <div class="card mb-4">
            <form action="<?= e(url('/admin/users')) ?>" method="GET" class="flex gap-2 items-center" style="flex-wrap:wrap;">
                <input type="text" name="q" class="input" placeholder="Search username or email..." value="<?= e($q ?? '') ?>" style="flex: 1; min-width: 200px;">
                <select name="filter" class="select" style="width: auto;">
                    <option value="">All Users</option>
                    <option value="admins" <?= ($filter ?? '') === 'admins' ? 'selected' : '' ?>>Admins Only</option>
                    <option value="disabled" <?= ($filter ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled Only</option>
                    <option value="verified" <?= ($filter ?? '') === 'verified' ? 'selected' : '' ?>>Verified Only</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($user['username']) ?></strong>
                                        <?php if ($user['is_verified']): ?><span title="Verified" style="color:var(--accent);">✓</span><?php endif; ?>
                                        <?php if ($user['role'] === 'admin'): ?><span class="badge" style="background:#3b82f6;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.8rem;margin-left:4px;">Admin</span><?php endif; ?>
                                        <?php if ($user['is_disabled']): ?><span class="badge" style="background:#ef4444;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.8rem;margin-left:4px;">Disabled</span><?php endif; ?>
                                    </td>
                                    <td><?= e($user['email']) ?></td>
                                    <td><?= e(time_ago($user['created_at'])) ?></td>
                                    <td>
                                        <div class="flex gap-2" style="flex-wrap: wrap;">
                                            <a href="<?= e(url('/' . $user['username'])) ?>" class="btn btn-sm" target="_blank">View</a>
                                            <form action="<?= e(url('/admin/users')) ?>" method="POST" style="display:inline;" data-confirm="Toggle disable status for <?= e($user['username']) ?>?">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                                <input type="hidden" name="action" value="<?= $user['is_disabled'] ? 'enable' : 'disable' ?>">
                                                <button type="submit" class="btn btn-sm <?= $user['is_disabled'] ? 'btn-primary' : 'btn-danger' ?>"><?= $user['is_disabled'] ? 'Enable' : 'Disable' ?></button>
                                            </form>
                                            <form action="<?= e(url('/admin/users')) ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                                <input type="hidden" name="action" value="<?= $user['is_verified'] ? 'unverify' : 'verify' ?>">
                                                <button type="submit" class="btn btn-sm"><?= $user['is_verified'] ? 'Unverify' : 'Verify' ?></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="flex justify-between items-center mt-4">
                    <?php if ($page > 1): ?>
                        <a href="<?= e(url('/admin/users?page=' . ($page - 1) . '&q=' . urlencode($q) . '&filter=' . urlencode($filter))) ?>" class="btn btn-sm">Previous</a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    
                    <span class="text-muted">Page <?= e($page) ?> of <?= e($totalPages) ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= e(url('/admin/users?page=' . ($page + 1) . '&q=' . urlencode($q) . '&filter=' . urlencode($filter))) ?>" class="btn btn-sm">Next</a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
