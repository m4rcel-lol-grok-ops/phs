<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$items = [
    '/dashboard' => ['Overview', '▤'],
    '/dashboard/profile' => ['Profile', '👤'],
    '/dashboard/links' => ['Links', '🔗'],
    '/dashboard/appearance' => ['Appearance', '🎨'],
    '/dashboard/account' => ['Account', '⚙'],
];
?>
<aside class="dash-sidebar">
    <p class="dash-sidebar-label">Your profile</p>
    <?php foreach ($items as $href => [$label, $icon]): ?>
        <a href="<?= e($href) ?>"<?= $path === $href ? ' class="active" aria-current="page"' : '' ?>>
            <span class="ico" aria-hidden="true"><?= $icon ?></span><?= e($label) ?>
        </a>
    <?php endforeach; ?>
    <div class="dash-sidebar-sep" role="presentation"></div>
    <a href="/<?= e($user['username']) ?>" target="_blank" rel="noopener">
        <span class="ico" aria-hidden="true">↗</span>View public page
    </a>
</aside>
