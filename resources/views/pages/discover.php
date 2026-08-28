<?php
ob_start();
$link = static function (array $extra) use ($sort, $search, $page): string {
    $params = array_merge(['sort' => $sort, 'q' => $search, 'page' => $page], $extra);
    $params = array_filter($params, static fn($v) => $v !== null && $v !== '' && $v !== 1);
    return '/discover' . ($params ? '?' . http_build_query($params) : '');
};
?>
<section class="section-tight">
    <div class="container">
        <div class="section-head">
            <h1 class="section-title">Discover</h1>
            <p class="section-sub">Public profiles from people who decided to be unnecessarily visible.</p>
        </div>

        <div class="toolbar">
            <form method="get" action="/discover">
                <input type="search" name="q" class="form-input" value="<?= e($search ?? '') ?>"
                       placeholder="Search profiles…" maxlength="64" aria-label="Search profiles">
                <input type="hidden" name="sort" value="<?= e($sort) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="/discover?sort=<?= e($sort) ?>" class="btn btn-ghost btn-sm">Clear</a>
                <?php endif; ?>
            </form>
            <div class="filter-tabs">
                <?php foreach (['popular' => 'Popular', 'new' => 'New', 'random' => 'Random'] as $key => $label): ?>
                    <a href="<?= e($link(['sort' => $key, 'page' => 1])) ?>" class="<?= $sort === $key ? 'active' : '' ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($search)): ?>
            <p class="text-muted text-sm mb-2">
                <?= e(format_number($total)) ?> result<?= $total === 1 ? '' : 's' ?> for “<?= e($search) ?>”
            </p>
        <?php endif; ?>

        <?php if (empty($profiles)): ?>
            <div class="empty-state">
                <div class="icon" aria-hidden="true">🔍</div>
                <h3><?= !empty($search) ? 'No matches' : 'Nothing here yet' ?></h3>
                <p>
                    <?= !empty($search)
                        ? 'No public profile matches that search. Try a different word.'
                        : 'Nobody has made a public profile on this instance yet. Embarrassing.' ?>
                </p>
                <?php if (!empty($search)): ?>
                    <a href="/discover" class="btn btn-secondary btn-sm">Show all profiles</a>
                <?php elseif (!is_logged_in() && setting_bool('registration_enabled', true)): ?>
                    <a href="/register" class="btn btn-primary btn-sm">Be the first</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="discover-grid">
                <?php foreach ($profiles as $p): ?>
                    <?php $avatar = upload_filename($p['avatar'] ?? null); ?>
                    <a href="/<?= e($p['username']) ?>" class="discover-card">
                        <?php if ($avatar !== null): ?>
                            <img src="/uploads/avatars/<?= e($avatar) ?>" alt="" class="discover-avatar"
                                 width="72" height="72" loading="lazy" decoding="async">
                        <?php else: ?>
                            <div class="discover-avatar-ph" aria-hidden="true">
                                <?= e(mb_strtoupper(mb_substr((string)($p['display_name'] ?: $p['username']), 0, 1))) ?>
                            </div>
                        <?php endif; ?>
                        <div class="discover-name">
                            <?= e($p['display_name'] ?: $p['username']) ?>
                            <?php if ($p['is_verified']): ?><span class="verified-badge" title="Verified">✓</span><?php endif; ?>
                        </div>
                        <div class="discover-user">@<?= e($p['username']) ?></div>
                        <div class="discover-bio"><?= e($p['bio'] ?? '') ?></div>
                        <div class="discover-foot">
                            <span><?= e(format_number((int)$p['profile_views'])) ?> views</span>
                            <?php if (!empty($p['location'])): ?><span>📍 <?= e($p['location']) ?></span><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= e($link(['page' => $page - 1])) ?>" rel="prev">← Prev</a>
                    <?php endif; ?>
                    <span class="current">Page <?= (int)$page ?> of <?= (int)$totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= e($link(['page' => $page + 1])) ?>" rel="next">Next →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
