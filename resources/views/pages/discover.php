<?php ob_start(); ?>
<div class="container">
    <div class="flex items-center justify-between mb-4" style="flex-wrap: wrap; gap: 1rem;">
        <h1>Discover</h1>
        <form action="<?= e(url('/discover')) ?>" method="GET" class="flex gap-2">
            <input type="text" name="search" class="input" placeholder="Search profiles..." value="<?= e($search ?? '') ?>">
            <select name="sort" class="select" aria-label="Sort by">
                <option value="popular" <?= ($sort ?? '') === 'popular' ? 'selected' : '' ?>>Popular</option>
                <option value="new" <?= ($sort ?? '') === 'new' ? 'selected' : '' ?>>Newest</option>
                <option value="random" <?= ($sort ?? '') === 'random' ? 'selected' : '' ?>>Random</option>
            </select>
            <button type="submit" class="btn btn-primary">Go</button>
        </form>
    </div>

    <?php if (empty($profiles)): ?>
        <div class="empty-state">
            <h3>No profiles found</h3>
            <p>Try a different search term or check back later.</p>
            <?php if ($search): ?>
                <a href="<?= e(url('/discover')) ?>" class="btn">Clear search</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
            <?php foreach ($profiles as $profile): ?>
                <a href="<?= e(url('/' . $profile['username'])) ?>" class="card" style="text-align: center; text-decoration: none; transition: transform 0.2s, border-color 0.2s;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: var(--bg-elevated); margin: 0 auto 1rem auto;">
                        <?php $avatarFile = upload_filename($profile['avatar'] ?? null); ?>
                        <?php if ($avatarFile !== null): ?>
                            <img src="<?= e('/uploads/avatars/' . $avatarFile) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--accent);color:#000;font-weight:bold;font-size:2rem;">
                                <?= e(strtoupper(substr($profile['display_name'] ?: $profile['username'], 0, 1))) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= e($profile['display_name'] ?: $profile['username']) ?>
                        <?php if ($profile['is_verified']): ?><span style="color:var(--accent);">✓</span><?php endif; ?>
                    </div>
                    <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">@<?= e($profile['username']) ?></div>
                    <?php if ($profile['bio']): ?>
                        <div style="color: var(--text-main); font-size: 0.85rem; opacity: 0.8; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 1rem; line-height: 1.4;">
                            <?= e($profile['bio']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-muted" style="font-size: 0.8rem;">
                        <?= e(format_number($profile['profile_views'])) ?> views
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="flex justify-between items-center mt-8">
                <?php if ($page > 1): ?>
                    <a href="<?= e(url('/discover?page=' . ($page - 1) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search ?? ''))) ?>" class="btn">Previous</a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                
                <span class="text-muted">Page <?= e($page) ?> of <?= e($totalPages) ?></span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= e(url('/discover?page=' . ($page + 1) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search ?? ''))) ?>" class="btn">Next</a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
