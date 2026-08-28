<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Controllers already build a fully-qualified title (e.g. "Discover — pornhub.singles"),
    // so it is used as-is; only a bare fallback appends the site name here.
    ?>
    <title><?= isset($title) ? e($title) : e(site_name()) ?></title>
    <meta name="description" content="<?= isset($description) ? e($description) : 'A self-hostable link-in-bio platform.' ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
    <noscript>
        <style>
            @media (max-width: 820px) { .nav-links { display: flex !important; position: static !important; } .nav-toggle { display: none !important; } }
        </style>
    </noscript>
</head>
<body>
    <a href="#main" class="skip-link">Skip to content</a>
    <div id="sr-announcer" class="sr-only" aria-live="polite"></div>

    <header class="site-header">
        <div class="header-inner">
            <a href="<?= e(url('/')) ?>" class="site-logo">
                pornhub<span class="badge">singles</span>
            </a>
            <button class="nav-toggle" aria-expanded="false" aria-label="Toggle navigation" aria-controls="main-nav">
                ☰
            </button>
            <nav id="main-nav" class="nav-links" aria-expanded="false">
                <a href="<?= e(url('/discover')) ?>">Discover</a>
                <a href="<?= e(url('/features')) ?>">Features</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= e(url('/dashboard')) ?>">Dashboard</a>
                    <?php if (is_admin()): ?>
                        <a href="<?= e(url('/admin')) ?>">Admin</a>
                    <?php endif; ?>
                    <form action="<?= e(url('/logout')) ?>" method="POST" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm">Log out</button>
                    </form>
                <?php else: ?>
                    <a href="<?= e(url('/login')) ?>">Log in</a>
                    <?php if (setting_bool('registration_enabled', true)): ?>
                        <a href="<?= e(url('/register')) ?>" class="btn btn-primary btn-sm">Create profile</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main id="main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <p>
            &copy; <?= date('Y') ?> <?= e(site_name()) ?>. Not affiliated with anyone. 
            <a href="<?= e(url('/about')) ?>">About</a>
            <a href="<?= e(url('/content-policy')) ?>">Content Policy</a>
            <a href="<?= e(url('/privacy')) ?>">Privacy</a>
            <a href="<?= e(url('/terms')) ?>">Terms</a>
            <a href="<?= e(url('/contact')) ?>">Contact</a>
        </p>
    </footer>

    <?= flash_alerts() ?>
</body>
</html>
