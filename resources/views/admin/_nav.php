<?php $adminPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/admin'; ?>
<div class="toolbar">
    <div class="filter-tabs">
        <a href="/admin" class="<?= $adminPath === '/admin' ? 'active' : '' ?>">Overview</a>
        <a href="/admin/users" class="<?= $adminPath === '/admin/users' ? 'active' : '' ?>">Users</a>
        <a href="/admin/reports" class="<?= $adminPath === '/admin/reports' ? 'active' : '' ?>">Reports</a>
        <a href="/admin/settings" class="<?= $adminPath === '/admin/settings' ? 'active' : '' ?>">Settings</a>
    </div>
</div>
