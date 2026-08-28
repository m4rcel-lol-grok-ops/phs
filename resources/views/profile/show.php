<?php
$bgStyle = '';
$cardBg = $profile['card_color'] ?? '#1a1a1a';
$textColor = $profile['text_color'] ?? '#ffffff';
$accent = $profile['accent_color'] ?? '#ff9900';
$btnColor = $profile['button_color'] ?? '#ff9900';

switch ($profile['bg_type'] ?? 'solid') {
    case 'gradient':
        $bgStyle = 'background: ' . ($profile['bg_gradient'] ?: 'linear-gradient(135deg, #0a0a0a, #1a0a00)') . ';';
        break;
    case 'image':
        if ($profile['bg_image']) {
            $bgStyle = 'background: url(/uploads/banners/' . e($profile['bg_image']) . ') center/cover no-repeat fixed;';
        }
        break;
    case 'url':
        if ($profile['bg_url']) {
            $bgStyle = 'background: url(' . e($profile['bg_url']) . ') center/cover no-repeat fixed;';
        }
        break;
    default:
        $bgStyle = 'background: ' . e($profile['bg_color'] ?? '#0a0a0a') . ';';
}

$fontMap = [
    'mono' => 'var(--font-mono)',
    'serif' => 'Georgia, "Times New Roman", serif',
    'rounded' => '"Segoe UI Rounded", "SF Pro Rounded", system-ui, sans-serif',
    'system' => 'var(--font)',
];
$font = $fontMap[$profile['font_family'] ?? 'system'] ?? $fontMap['system'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e($og_image) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body { <?= $bgStyle ?> color: <?= e($textColor) ?>; font-family: <?= $font ?>; }
        .profile-card { --card-bg: <?= e($cardBg) ?>; background: <?= e($cardBg) ?>; }
        .link-card:hover { border-color: <?= e($accent) ?> !important; background: color-mix(in srgb, <?= e($accent) ?> 12%, transparent) !important; }
        .verified-badge { background: <?= e($accent) ?>; }
        .music-player button { background: <?= e($btnColor) ?>; }
        a { color: <?= e($accent) ?>; }
    </style>
</head>
<body>
<div class="profile-page">
    <div class="profile-card">
        <?php if ($profile['banner']): ?>
            <div class="profile-banner" style="background-image:url(/uploads/banners/<?= e($profile['banner']) ?>)"></div>
        <?php else: ?>
            <div class="profile-banner" style="background:linear-gradient(135deg,<?= e($cardBg) ?>,<?= e($accent) ?>33)"></div>
        <?php endif; ?>

        <div class="profile-header">
            <?php if ($profile['avatar']): ?>
                <img src="/uploads/avatars/<?= e($profile['avatar']) ?>" alt="<?= e($profile['display_name'] ?: $profile['username']) ?>" class="profile-avatar" width="96" height="96">
            <?php else: ?>
                <div class="profile-avatar-placeholder"><?= e(mb_strtoupper(mb_substr($profile['display_name'] ?: $profile['username'], 0, 1))) ?></div>
            <?php endif; ?>

            <div class="profile-name">
                <?= e($profile['display_name'] ?: $profile['username']) ?>
                <?php if ($profile['is_verified']): ?><span class="verified-badge" title="Verified">✓</span><?php endif; ?>
            </div>
            <div class="profile-username">@<?= e($profile['username']) ?></div>
            <?php if ($profile['pronouns']): ?>
                <div style="font-size:0.85rem;opacity:0.7;margin-bottom:0.25rem"><?= e($profile['pronouns']) ?></div>
            <?php endif; ?>
            <?php if ($profile['bio']): ?>
                <div class="profile-bio"><?= e($profile['bio']) ?></div>
            <?php endif; ?>
            <div class="profile-meta">
                <?php if ($profile['location']): ?><span>📍 <?= e($profile['location']) ?></span><?php endif; ?>
                <span>Joined <?= date('M Y', strtotime($profile['user_created_at'])) ?></span>
                <span><?= format_number((int)$profile['profile_views']) ?> views</span>
            </div>
        </div>

        <?php if ($profile['music_url']): ?>
        <div class="music-player">
            <button type="button" id="music-play" aria-label="Play">▶</button>
            <div class="music-info">
                <div class="music-title"><?= e($profile['music_title'] ?: 'Track') ?></div>
                <?php if ($profile['music_artist']): ?><div class="music-artist"><?= e($profile['music_artist']) ?></div><?php endif; ?>
            </div>
            <audio id="profile-audio" src="<?= e($profile['music_url']) ?>" preload="none"></audio>
        </div>
        <?php endif; ?>

        <div class="profile-links">
            <?php if (empty($links)): ?>
                <p style="text-align:center;opacity:0.5;padding:1rem">Nothing here yet. Embarrassing.</p>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <a href="/click/<?= (int)$link['id'] ?>" class="link-card" rel="noopener noreferrer" target="_blank">
                        <?php if ($link['emoji']): ?><span class="link-emoji"><?= e($link['emoji']) ?></span><?php endif; ?>
                        <div class="link-info">
                            <div class="link-title"><?= e($link['title']) ?></div>
                            <?php if ($link['description']): ?><div class="link-desc"><?= e($link['description']) ?></div><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="profile-footer-stats">
            <?= format_number((int)$profile['profile_views']) ?> totally real visitors · <?= count($links) ?> links
        </div>
    </div>

    <div class="profile-report">
        <button type="button" id="report-btn">Report</button>
        · <a href="/" style="color:inherit;opacity:0.6">pornhub.singles</a>
    </div>
</div>

<div id="report-modal" hidden style="position:fixed;inset:0;background:rgba(0,0,0,0.7);display:flex;align-items:center;justify-content:center;z-index:200;padding:1rem">
    <div class="card" style="max-width:400px;width:100%">
        <h3 style="margin-bottom:1rem">Report profile</h3>
        <form method="post" action="/report">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int)$profile['user_id'] ?>">
            <input type="hidden" name="type" value="profile">
            <div class="form-group">
                <label class="form-label" for="reason">Reason</label>
                <textarea name="reason" id="reason" class="form-textarea" required minlength="10" placeholder="What's wrong?"></textarea>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="button" class="btn btn-secondary" id="report-close">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
