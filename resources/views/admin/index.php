<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/admin/_nav.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Admin Overview</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="card text-center">
                <h3 style="font-size: 2rem; color: var(--accent);"><?= e(format_number($stats['users'] ?? 0)) ?></h3>
                <div class="text-muted">Total Users</div>
            </div>
            <div class="card text-center">
                <h3 style="font-size: 2rem; color: var(--accent);"><?= e(format_number($stats['links'] ?? 0)) ?></h3>
                <div class="text-muted">Total Links</div>
            </div>
            <div class="card text-center">
                <h3 style="font-size: 2rem; color: #ef4444;"><?= e(format_number($stats['reports'] ?? 0)) ?></h3>
                <div class="text-muted">Pending Reports</div>
            </div>
        </div>

        <div class="card mb-4">
            <h2>Recent Users</h2>
            <div class="table-wrap mt-4">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentUsers)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($user['username']) ?></strong>
                                        <?php if ($user['role'] === 'admin'): ?><span class="badge" style="background:#3b82f6;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.8rem;margin-left:4px;">Admin</span><?php endif; ?>
                                    </td>
                                    <td><?= e($user['email']) ?></td>
                                    <td><?= e(time_ago($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['is_disabled']): ?>
                                            <span style="color:#ef4444;">Disabled</span>
                                        <?php else: ?>
                                            <span style="color:#10b981;">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><a href="<?= e(url('/admin/users')) ?>" class="btn">View all users</a></div>
        </div>

        <div class="card">
            <h2>Recent Admin Actions</h2>
            <div class="table-wrap mt-4">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>User</th>
                            <th>Target</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentActions)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No actions logged.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentActions as $action): ?>
                                <tr>
                                    <td><?= e($action['action']) ?></td>
                                    <td><?= e($action['admin_username']) ?></td>
                                    <td><?= e($action['target_username'] ?? 'N/A') ?></td>
                                    <td><?= e(time_ago($action['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
