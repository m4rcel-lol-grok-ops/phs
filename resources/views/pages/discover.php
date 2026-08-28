<?php ob_start(); ?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Discover</h1>
        <p class="section-sub">Public profiles from people who decided to be unnecessarily visible.</p>

        <div class="filters">
            <form method="get" action="/discover" style="display:flex;gap:0.5rem;flex-wrap:wrap;flex:1">
                <input type="search" name="q" class="form-input" placeholder="Search profiles..." value="<?= e($search ?? '') ?>" style="max-width:240px">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
            <div class="filter-tabs">
                <a href="?sort=popular<?= $search ? '&q='.urlencode($search) : '' ?>" class="<?= ($sort ?? '') === 'popular' ? 'active' : '' ?>">Popular</a>
                <a href="?sort=new<?= $search ? '&q='.urlencode($search) : '' ?>" class="<?= ($sort ?? '') === 'new' ? 'active' : '' ?>">New</a>
                <a href="?sort=random<?= $search ? '&q='.urlencode($search) : '' ?>" class="<?= ($sort ?? '') === 'random' ? 'active' : '' ?>">Random</a>
            </div>
        </div>

        <?php if (empty($profiles)): ?>
            <p class="text-center text-muted" style="padding:3rem 0">Nothing here yet. Embarrassing.</p>
        <?php else: ?>
            <div class="discover-grid">
                <?php foreach ($profiles as $p): ?>
                    <a href="/<?= e($p['username']) ?>" class="discover-card">
                        <?php if ($p['avatar']): ?>
                            <img src="/uploads/avatars/<?= e($p['avatar']) ?>" alt="" class="discover-avatar" width="72" height="72">
                        <?php else: ?>
                            <div class="discover-avatar-ph"><?= e(mb_strtoupper(mb_substr($p['display_name'] ?: $p['username'], 0, 1))) ?></div>
                        <?php endif; ?>
                        <div class="discover-name">
                            <?= e($p['display_name'] ?: $p['username']) ?>
                            <?php if ($p['is_verified']): ?><span class="verified-badge" title="Verified">✓</span><?php endif; ?>
                        </div>
                        <div class="discover-user">@<?= e($p['username']) ?></div>
                        <?php if ($p['bio']): ?><div class="discover-bio"><?= e($p['bio']) ?></div><?php endif; ?>
                        <div class="discover-views"><?= format_number((int)$p['profile_views']) ?> views</div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php
            $totalPages = max(1, (int)ceil($total / $perPage));
            if ($totalPages > 1):
            ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&sort=<?= e($sort) ?><?= $search ? '&q='.urlencode($search) : '' ?>">← Prev</a>
                <?php endif; ?>
                <span class="active"><?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&sort=<?= e($sort) ?><?= $search ? '&q='.urlencode($search) : '' ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
