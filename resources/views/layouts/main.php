<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'pornhub.singles') ?></title>
    <meta name="description" content="<?= e($description ?? 'A completely unnecessary bio-link website. Independent parody/humor project.') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="canonical" href="<?= e(url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))) ?>">
    <meta property="og:title" content="<?= e($title ?? 'pornhub.singles') ?>">
    <meta property="og:description" content="<?= e($description ?? 'A completely unnecessary bio-link website.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))) ?>">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?= e($og_image) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= e($title ?? 'pornhub.singles') ?>">
    <meta name="twitter:description" content="<?= e($description ?? 'A completely unnecessary bio-link website.') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔥</text></svg>">
</head>
<body>
    <header class="navbar">
        <div class="container navbar-inner">
            <a href="/" class="logo">pornhub<span>.singles</span></a>
            <button class="nav-toggle" aria-label="Menu" aria-expanded="false">☰</button>
            <nav>
                <ul class="nav-links">
                    <li><a href="/" class="<?= ($_SERVER['REQUEST_URI'] ?? '') === '/' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="/discover">Discover</a></li>
                    <li><a href="/features">Features</a></li>
                    <li><a href="/about">About</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="/dashboard">Dashboard</a></li>
                        <?php if (is_admin()): ?><li><a href="/admin">Admin</a></li><?php endif; ?>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register" class="nav-cta">Create Profile</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <?php if ($msg = flash('success')): ?>
        <div class="container" style="padding-top:1rem"><div class="alert alert-success"><?= e($msg) ?></div></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="container" style="padding-top:1rem"><div class="alert alert-error"><?= e($msg) ?></div></div>
    <?php endif; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <a href="/" class="logo">pornhub<span>.singles</span></a>
                <p>A completely unnecessary bio-link website.</p>
            </div>
            <ul class="footer-links">
                <li><a href="/">Home</a></li>
                <li><a href="/discover">Discover</a></li>
                <li><a href="/features">Features</a></li>
                <li><a href="/content-policy">Content Policy</a></li>
                <li><a href="/privacy">Privacy</a></li>
                <li><a href="/terms">Terms</a></li>
                <li><a href="/contact">Contact</a></li>
                <li><a href="/about">About</a></li>
            </ul>
            <div class="footer-disclaimer">
                pornhub.singles is an independent parody/humor project.<br>
                Not affiliated with or endorsed by Pornhub or Aylo.
            </div>
        </div>
    </footer>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
