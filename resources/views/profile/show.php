<?php
/**
 * Public profile page.
 *
 * Every colour/background value comes from Theme::resolve(), which has already
 * run the css_* sanitizers. They are emitted as custom property values only —
 * never as selectors or property names.
 *
 * @var array $profile
 * @var array $links
 * @var array $theme
 * @var bool  $isOwner
 */
use App\Core\Theme;

$colors = $theme['colors'];
$buttonFg = Theme::contrast($colors['button']);
$accentSoft = Theme::rgba($colors['accent'], 0.14);
$surface = Theme::isLight($colors['card'])
    ? 'rgba(0,0,0,0.035)'
    : 'rgba(255,255,255,0.04)';

$displayName = $profile['display_name'] ?: $profile['username'];
$avatar = upload_filename($profile['avatar'] ?? null);
$banner = upload_filename($profile['banner'] ?? null);
$website = $profile['website'] ?? null;
$effect = $theme['effect'];
$flashHtml = flash_alerts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <?php if (!$profile['is_public']): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <meta property="og:title" content="<?= e($displayName) ?> (@<?= e($profile['username']) ?>)">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="<?= e(url($profile['username'])) ?>">
    <?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e($og_image) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary">

    <link rel="stylesheet" href="<?= e(asset('css/profile.css')) ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔥</text></svg>">

    <style>
        .profile-body {
            --p-bg: <?= $colors['bg'] ?>;
            --p-card: <?= $colors['card'] ?>;
            --p-accent: <?= $colors['accent'] ?>;
            --p-text: <?= $colors['text'] ?>;
            --p-button: <?= $colors['button'] ?>;
            --p-button-fg: <?= $buttonFg ?>;
            --p-radius: <?= $theme['radius'] ?>;
            --p-border: <?= $theme['border'] ?>;
            --p-surface: <?= $surface ?>;
            --p-accent-soft: <?= $accentSoft ?>;
            background: <?= $theme['background'] ?>;
            font-family: <?= $theme['font'] ?>;
        }
    </style>
</head>
<body class="profile-body theme-<?= e($theme['theme']) ?>">

<?php if ($effect !== null): ?>
    <div class="fx-layer fx-<?= e($effect) ?>" aria-hidden="true"
         <?= in_array($effect, ['particles', 'snow'], true) ? 'data-fx="' . e($effect) . '"' : '' ?>></div>
<?php endif; ?>

<div class="profile-page">

    <?php if ($flashHtml !== ''): ?>
        <div class="profile-flash"><?= $flashHtml ?></div>
    <?php endif; ?>

    <?php if ($isOwner): ?>
        <div class="owner-bar">
            <span>
                <?= $profile['is_public']
                    ? 'This is your public profile.'
                    : 'Your profile is private — only you can see this page.' ?>
            </span>
            <a href="/dashboard/appearance">Edit appearance →</a>
        </div>
    <?php endif; ?>

    <article class="profile-card">
        <div class="profile-banner"<?= $banner
            ? ' style="background-image:url(\'/uploads/banners/' . e($banner) . '\')"'
            : ' style="background:linear-gradient(135deg,' . $colors['card'] . ',' . $accentSoft . ')"' ?>></div>

        <header class="profile-header">
            <?php if ($avatar !== null): ?>
                <img src="/uploads/avatars/<?= e($avatar) ?>" alt="<?= e($displayName) ?>"
                     class="profile-avatar" width="104" height="104" loading="eager" decoding="async">
            <?php else: ?>
                <div class="profile-avatar-ph" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($displayName, 0, 1))) ?></div>
            <?php endif; ?>

            <h1 class="profile-name">
                <?= e($displayName) ?>
                <?php if ($profile['is_verified']): ?>
                    <span class="verified-badge" title="Verified" aria-label="Verified account">✓</span>
                <?php endif; ?>
            </h1>
            <div class="profile-username">@<?= e($profile['username']) ?></div>

            <?php if (!empty($profile['pronouns'])): ?>
                <div class="profile-pronouns"><?= e($profile['pronouns']) ?></div>
            <?php endif; ?>

            <?php if (!empty($profile['bio'])): ?>
                <p class="profile-bio"><?= e($profile['bio']) ?></p>
            <?php endif; ?>

            <div class="profile-meta">
                <?php if (!empty($profile['location'])): ?>
                    <span>📍 <?= e($profile['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($website)): ?>
                    <span>🔗 <a href="<?= e($website) ?>" rel="nofollow noopener noreferrer" target="_blank"><?= e(link_host($website)) ?></a></span>
                <?php endif; ?>
                <span>Joined <?= e(date('M Y', strtotime((string)$profile['user_created_at']) ?: time())) ?></span>
            </div>
        </header>

        <?php if (!empty($profile['music_url'])): ?>
            <div class="music-player">
                <button type="button" id="music-play" aria-label="Play track">▶</button>
                <div class="music-info">
                    <div class="music-title"><?= e($profile['music_title'] ?: 'Untitled track') ?></div>
                    <?php if (!empty($profile['music_artist'])): ?>
                        <div class="music-artist"><?= e($profile['music_artist']) ?></div>
                    <?php endif; ?>
                </div>
                <audio id="profile-audio" src="<?= e($profile['music_url']) ?>" preload="none"></audio>
            </div>
        <?php endif; ?>

        <div class="profile-links">
            <?php if (empty($links)): ?>
                <p class="profile-empty">No links yet. Embarrassing.</p>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <a href="/click/<?= (int)$link['id'] ?>" class="link-card"
                       target="_blank" rel="noopener noreferrer nofollow">
                        <?php if (!empty($link['emoji'])): ?>
                            <span class="link-emoji" aria-hidden="true"><?= e($link['emoji']) ?></span>
                        <?php endif; ?>
                        <span class="link-info">
                            <span class="link-title"><?= e($link['title']) ?></span>
                            <?php if (!empty($link['description'])): ?>
                                <span class="link-desc"><?= e($link['description']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="link-arrow" aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer class="profile-footer-stats">
            <?= e(format_number((int)$profile['profile_views'])) ?> totally real visitors
            · <?= count($links) ?> link<?= count($links) === 1 ? '' : 's' ?>
        </footer>
    </article>

    <div class="profile-actions">
        <?php if (!$isOwner): ?>
            <button type="button" id="report-btn">Report profile</button>
            <span class="sep" aria-hidden="true">·</span>
        <?php endif; ?>
        <a href="/">Made with <?= e(site_name()) ?></a>
    </div>
</div>

<?php if (!$isOwner): ?>
<div class="modal" id="report-modal" hidden role="dialog" aria-modal="true" aria-labelledby="report-title">
    <div class="modal-card">
        <h3 id="report-title">Report profile</h3>
        <p class="modal-sub">Reports go straight to the site moderators.</p>
        <form method="post" action="/report">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int)$profile['user_id'] ?>">

            <label for="report-type">What is the problem?</label>
            <select name="type" id="report-type">
                <option value="profile">The profile in general</option>
                <option value="link">A link it points to</option>
                <option value="avatar">The avatar image</option>
                <option value="banner">The banner image</option>
                <option value="biography">The biography text</option>
                <option value="other">Something else</option>
            </select>

            <label for="report-reason">Tell us more</label>
            <textarea name="reason" id="report-reason" required minlength="10" maxlength="2000"
                      placeholder="Describe the problem in at least 10 characters."></textarea>

            <div class="modal-actions">
                <button type="submit" class="modal-submit">Submit report</button>
                <button type="button" class="modal-cancel" id="report-close">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="<?= e(asset('js/profile.js')) ?>" defer></script>
</body>
</html>
