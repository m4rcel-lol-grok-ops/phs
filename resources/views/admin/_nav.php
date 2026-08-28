<nav>
    <a href="<?= e(url('/admin')) ?>" <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'aria-current="page"' : '' ?>>Overview</a>
    <a href="<?= e(url('/admin/users')) ?>" <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'aria-current="page"' : '' ?>>Users</a>
    <a href="<?= e(url('/admin/reports')) ?>" <?= strpos($_SERVER['REQUEST_URI'], '/admin/reports') === 0 ? 'aria-current="page"' : '' ?>>Reports</a>
    <a href="<?= e(url('/admin/settings')) ?>" <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0 ? 'aria-current="page"' : '' ?>>Settings</a>
    <a href="<?= e(url('/dashboard')) ?>" style="margin-top: 1rem; border: 1px solid var(--border);">Back to Dashboard</a>
</nav>
