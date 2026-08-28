<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($profile['display_name'] ?: $profile['username']) ?> - <?= e(site_name()) ?></title>
    <meta name="description" content="<?= e(mb_substr($profile['bio'] ?? 'Check out my profile.', 0, 160)) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/profile.css')) ?>">
    <style>
        .profile-body {
            --p-bg: <?= css_color($theme['colors']['bg'], '#0b0a09') ?>;
            --p-card: <?= css_color($theme['colors']['card'], '#151413') ?>;
            --p-accent: <?= css_color($theme['colors']['accent'], '#ff9900') ?>;
            --p-text: <?= css_color($theme['colors']['text'], '#f5f5f5') ?>;
            --p-button: <?= css_color($theme['colors']['button'], '#1f1e1d') ?>;
            --p-radius: <?= e($theme['radius']) ?>;
            --p-border: <?= e($theme['border']) ?>;
            background: <?= $theme['background'] ?>;
            font-family: <?= e($theme['font']) ?>;
        }
    </style>
    <script src="<?= e(asset('js/profile.js')) ?>" defer></script>
</head>
<body class="profile-body theme-<?= e($theme['theme']) ?>">
    <div id="sr-announcer" class="sr-only" aria-live="polite"></div>

    <?php if (isset($isOwner) && $isOwner): ?>
        <div class="owner-bar">
            <?= $profile['is_public'] ? 'This is your public profile.' : 'Your profile is private — only you can see this.' ?>
            <a href="<?= e(url('/dashboard/appearance')) ?>">Edit appearance</a>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <?php $bannerFile = upload_filename($profile['banner'] ?? null); $avatarFile = upload_filename($profile['avatar'] ?? null); ?>
        <header class="profile-header">
            <?php if ($bannerFile !== null): ?>
                <img src="/uploads/banners/<?= e($bannerFile) ?>" alt="" class="profile-banner">
            <?php else: ?>
                <div class="profile-banner"></div>
            <?php endif; ?>

            <div class="profile-avatar-wrap">
                <?php if ($avatarFile !== null): ?>
                    <img src="/uploads/avatars/<?= e($avatarFile) ?>" alt="<?= e($profile['username']) ?>" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <?= e(strtoupper(substr($profile['display_name'] ?: $profile['username'], 0, 1))) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="profile-name">
                <?= e($profile['display_name'] ?: $profile['username']) ?>
                <?php if ($profile['is_verified']): ?>
                    <svg class="verified-badge" viewBox="0 0 24 24" fill="currentColor" aria-label="Verified"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-2 15l-5-5 1.4-1.4 3.6 3.6 7.6-7.6L19 8l-9 9z"/></svg>
                <?php endif; ?>
            </h1>
            <div class="profile-handle">@<?= e($profile['username']) ?> <?= $profile['pronouns'] ? '· ' . e($profile['pronouns']) : '' ?></div>
            
            <?php if ($profile['bio']): ?>
                <div class="profile-bio"><?= e($profile['bio']) ?></div>
            <?php endif; ?>

            <div class="profile-meta">
                <?php if ($profile['location']): ?>
                    <span>📍 <?= e($profile['location']) ?></span>
                <?php endif; ?>
                <?php if ($profile['website']): ?>
                    <span>🔗 <a href="<?= e($profile['website']) ?>" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline;"><?= e(link_host($profile['website'])) ?></a></span>
                <?php endif; ?>
                <span>🗓️ Joined <?= e(time_ago($profile['user_created_at'])) ?></span>
            </div>
        </header>

        <?php if ($profile['music_url']): ?>
            <div class="card text-center" style="background:var(--p-card);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:1rem;">
                <div style="font-size:0.9rem;opacity:0.8;margin-bottom:0.5rem;">🎵 <?= e($profile['music_title'] ?: 'Music') ?> <?= $profile['music_artist'] ? ' - ' . e($profile['music_artist']) : '' ?></div>
                <audio controls src="<?= e(css_url($profile['music_url']) ? $profile['music_url'] : '') ?>" style="width:100%;height:32px;"></audio>
            </div>
        <?php endif; ?>

        <ul class="profile-links">
            <?php if (empty($links)): ?>
                <li class="empty-state" style="background:transparent;border:none;">
                    <p>Nothing here yet. Embarrassing.</p>
                </li>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <?php if ($link['is_enabled']): ?>
                    <li>
                        <a href="<?= e($link['url']) ?>" class="profile-link" target="_blank" rel="noopener noreferrer">
                            <?php if ($link['emoji']): ?>
                                <span class="link-emoji"><?= e($link['emoji']) ?></span>
                            <?php endif; ?>
                            <div class="link-content">
                                <div class="link-title"><?= e($link['title']) ?></div>
                                <?php if ($link['description']): ?>
                                    <div class="link-desc"><?= e($link['description']) ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <footer class="profile-footer">
            <div class="stats-line">totally real visitors: <?= e(format_number($profile['profile_views'])) ?></div>
            <div class="profile-actions">
                <a href="<?= e(url('/')) ?>">Create your own</a>
                <?php if (!isset($isOwner) || !$isOwner): ?>
                    · <button type="button" id="report-trigger">Report profile</button>
                <?php endif; ?>
            </div>
        </footer>
    </div>

    <?php if ($theme['effect']): ?>
        <div id="effects-layer" data-effect="<?= e($theme['effect']) ?>"></div>
    <?php endif; ?>

    <?php if (!isset($isOwner) || !$isOwner): ?>
        <div id="report-modal" class="modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="modal-content" role="document">
                <h2 id="report-modal-title">Report Profile</h2>
                <form action="<?= e(url('/report')) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= e($profile['user_id']) ?>">
                    
                    <div class="input-group">
                        <label for="report_type">Reason for reporting</label>
                        <select name="type" id="report_type" required>
                            <option value="">Select a reason...</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="explicit">Explicit content (banned)</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="report_reason">Details</label>
                        <textarea name="reason" id="report_reason" rows="4" minlength="10" required placeholder="Please provide more details (min 10 chars)"></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn modal-btn-cancel" id="report-cancel">Cancel</button>
                        <button type="submit" class="modal-btn modal-btn-submit">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?= flash_alerts() ?>
</body>
</html>
