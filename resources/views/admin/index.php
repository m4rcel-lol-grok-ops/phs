<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Admin</h1>
        <div class="stat-grid" style="margin:2rem 0">
            <div class="stat-card"><div class="stat-value"><?= format_number($stats['users']) ?></div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-value"><?= format_number($stats['profiles']) ?></div><div class="stat-label">Profiles</div></div>
            <div class="stat-card"><div class="stat-value"><?= format_number($stats['links']) ?></div><div class="stat-label">Links</div></div>
            <div class="stat-card"><div class="stat-value"><?= format_number($stats['reports']) ?></div><div class="stat-label">Pending Reports</div></div>
            <div class="stat-card"><div class="stat-value"><?= format_number($stats['views']) ?></div><div class="stat-label">Total Views</div></div>
        </div>
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
            <a href="/admin/users" class="btn btn-primary">Users</a>
            <a href="/admin/reports" class="btn btn-secondary">Reports</a>
            <a href="/admin/settings" class="btn btn-secondary">Settings</a>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
