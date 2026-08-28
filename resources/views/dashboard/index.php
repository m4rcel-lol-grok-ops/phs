<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/dashboard/_sidebar.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Dashboard</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-label">Profile Views</div>
                <div class="stat-value text-accent"><?= e(format_number($profile['profile_views'])) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Link Clicks</div>
                <div class="stat-value text-accent"><?= e(format_number($totalClicks)) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Links</div>
                <div class="stat-value text-accent"><?= count($links) ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Activity (Last 14 days)</h2>
            <div class="table-wrap mt-4">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th style="text-align: right;">Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($viewSeries)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">No data available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($viewSeries as $day): ?>
                                <tr>
                                    <td><?= e($day['day']) ?></td>
                                    <td style="text-align: right;"><?= e(format_number($day['total'])) ?></td>
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
