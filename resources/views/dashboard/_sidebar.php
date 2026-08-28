<nav>
    <a href="<?= e(url('/dashboard')) ?>" <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'aria-current="page"' : '' ?>>Overview</a>
    <a href="<?= e(url('/dashboard/profile')) ?>" <?= $_SERVER['REQUEST_URI'] === '/dashboard/profile' ? 'aria-current="page"' : '' ?>>Profile Details</a>
    <a href="<?= e(url('/dashboard/links')) ?>" <?= $_SERVER['REQUEST_URI'] === '/dashboard/links' ? 'aria-current="page"' : '' ?>>Manage Links</a>
    <a href="<?= e(url('/dashboard/appearance')) ?>" <?= $_SERVER['REQUEST_URI'] === '/dashboard/appearance' ? 'aria-current="page"' : '' ?>>Appearance</a>
    <a href="<?= e(url('/dashboard/account')) ?>" <?= $_SERVER['REQUEST_URI'] === '/dashboard/account' ? 'aria-current="page"' : '' ?>>Account</a>
    <a href="<?= e(url('/' . $user['username'])) ?>" target="_blank" rel="noopener noreferrer" style="margin-top: 1rem; border: 1px solid var(--border);">View Public Page ↗</a>
</nav>
