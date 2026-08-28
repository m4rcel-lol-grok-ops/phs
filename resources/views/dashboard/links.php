<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <div class="dash-content">
        <div class="dash-header">
            <h1>Links</h1>
        </div>

        <div class="card" style="margin-bottom:1.5rem">
            <h3 style="margin-bottom:1rem">Add a link</h3>
            <form method="post" action="/dashboard/links">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-input" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="url">URL</label>
                    <input type="url" id="url" name="url" class="form-input" required maxlength="512" placeholder="https://">
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description (optional)</label>
                    <input type="text" id="description" name="description" class="form-input" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label" for="emoji">Emoji (optional)</label>
                    <input type="text" id="emoji" name="emoji" class="form-input" maxlength="16" placeholder="🔗" style="max-width:100px">
                </div>
                <button type="submit" class="btn btn-primary">Add Link</button>
            </form>
        </div>

        <?php if (empty($links)): ?>
            <p class="text-muted">Nothing here yet. Embarrassing.</p>
        <?php else: ?>
            <div id="link-list">
                <?php foreach ($links as $link): ?>
                    <div class="link-item" data-id="<?= (int)$link['id'] ?>">
                        <span class="handle" title="Drag to reorder">⠿</span>
                        <div style="flex:1;min-width:0">
                            <strong><?= e($link['emoji'] ? $link['emoji'].' ' : '') ?><?= e($link['title']) ?></strong>
                            <div class="text-muted" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis"><?= e($link['url']) ?></div>
                            <div class="text-muted" style="font-size:0.75rem"><?= format_number((int)$link['click_count']) ?> clicks · <?= $link['is_enabled'] ? 'Enabled' : 'Disabled' ?></div>
                        </div>
                        <div class="link-actions">
                            <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this link?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--error)">Delete</button>
                            </form>
                        </div>
                    </div>
                    <details style="margin-bottom:0.75rem">
                        <summary style="cursor:pointer;color:var(--text-muted);font-size:0.85rem;padding:0.25rem 0">Edit</summary>
                        <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>" class="card" style="margin-top:0.5rem">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-input" value="<?= e($link['title']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL</label>
                                <input type="url" name="url" class="form-input" value="<?= e($link['url']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-input" value="<?= e($link['description'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Emoji</label>
                                <input type="text" name="emoji" class="form-input" value="<?= e($link['emoji'] ?? '') ?>" style="max-width:80px">
                            </div>
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="is_enabled" value="1" <?= $link['is_enabled'] ? 'checked' : '' ?>>
                                    Enabled
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </details>
                <?php endforeach; ?>
            </div>
            <p class="form-hint mt-2">Drag links to reorder.</p>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
