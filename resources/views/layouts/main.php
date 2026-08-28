<?php
/** @var string $content */
$navPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [
    '/' => 'Home',
    '/discover' => 'Discover',
    '/features' => 'Features',
    '/about' => 'About',
];
$isActive = static function (string $path) use ($navPath): bool {
    return $path === '/' ? $navPath === '/' : str_starts_with($navPath, $path);
};
$canonical = url($navPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title><?= e($title ?? site_name()) ?></title>
    <meta name="description" content="<?= e($description ?? (setting('site_description') ?: 'A completely unnecessary bio-link website.')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <meta property="og:site_name" content="<?= e(site_name()) ?>">
    <meta property="og:title" content="<?= e($title ?? site_name()) ?>">
    <meta property="og:description" content="<?= e($description ?? 'A completely unnecessary bio-link website.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e($og_image) ?>"><?php endif; ?>
    <meta name="twitter:card" content="<?= !empty($og_image) ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($title ?? site_name()) ?>">
    <meta name="twitter:description" content="<?= e($description ?? 'A completely unnecessary bio-link website.') ?>">

    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔥</text></svg>">
</head>
<body>
    <a href="#main" class="skip-link">Skip to content</a>

    <header class="navbar">
        <div class="container navbar-inner">
            <a href="/" class="logo" aria-label="<?= e(site_name()) ?> home">
                <span class="logo-mark" aria-hidden="true">ph</span>
                <span class="logo-text">pornhub<span>.singles</span></span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="primary-nav">
                <span class="nav-toggle-bar" aria-hidden="true"></span>
                <span class="nav-toggle-bar" aria-hidden="true"></span>
                <span class="nav-toggle-bar" aria-hidden="true"></span>
            </button>

            <nav id="primary-nav" class="nav" aria-label="Primary">
                <ul class="nav-links">
                    <?php foreach ($navItems as $path => $label): ?>
                        <li>
                            <a href="<?= e($path) ?>"<?= $isActive($path) ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="nav-actions">
                    <?php if (is_logged_in() && ($navUser = current_user())): ?>
                        <a href="/dashboard" class="nav-link-btn<?= str_starts_with($navPath, '/dashboard') ? ' active' : '' ?>">Dashboard</a>
                        <?php if (is_admin()): ?>
                            <a href="/admin" class="nav-link-btn<?= str_starts_with($navPath, '/admin') ? ' active' : '' ?>">Admin</a>
                        <?php endif; ?>
                        <a href="/<?= e($navUser['username']) ?>" class="nav-user" title="View your public profile">
                            <span class="nav-user-dot" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string)$navUser['username'], 0, 1))) ?></span>
                            <span class="nav-user-name">@<?= e($navUser['username']) ?></span>
                        </a>
                        <form method="post" action="/logout" class="nav-logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-ghost btn-sm">Log out</button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="nav-link-btn">Log in</a>
                        <?php if (setting_bool('registration_enabled', true)): ?>
                            <a href="/register" class="btn btn-primary btn-sm">Create profile</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <?php if ($alerts = flash_alerts()): ?>
        <div class="container flash-stack"><?= $alerts ?></div>
    <?php endif; ?>

    <main id="main">
        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <a href="/" class="logo">
                    <span class="logo-mark" aria-hidden="true">ph</span>
                    <span class="logo-text">pornhub<span>.singles</span></span>
                </a>
                <p><?= e(setting('site_description') ?: 'A completely unnecessary bio-link website.') ?></p>
            </div>

            <nav class="footer-nav" aria-label="Footer">
                <div class="footer-col">
                    <h4>Site</h4>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/discover">Discover</a></li>
                        <li><a href="/features">Features</a></li>
                        <li><a href="/about">About</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="/content-policy">Content Policy</a></li>
                        <li><a href="/privacy">Privacy</a></li>
                        <li><a href="/terms">Terms</a></li>
                        <li><a href="/contact">Contact</a></li>
                    </ul>
                </div>
            </nav>

            <p class="footer-disclaimer">
                <strong><?= e(site_name()) ?></strong> is an independent parody and humor project.
                Not affiliated with, sponsored by, or endorsed by Pornhub, Aylo, or any related entity.
                <span>&copy; <?= date('Y') ?></span>
            </p>
        </div>
    </footer>

    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
