<?php
ob_start();
$total = count($links);
?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <h1>Links</h1>
                <p><?= $total ?> link<?= $total === 1 ? '' : 's' ?> on your page.</p>
            </div>
            <a href="/<?= e($user['username']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Preview ↗</a>
        </div>

        <details class="card mb-3" <?= $total === 0 ? 'open' : '' ?>>
            <summary style="cursor:pointer;font-weight:800;list-style:none">＋ Add a link</summary>
            <form method="post" action="/dashboard/links" class="mt-3">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-input"
                               required maxlength="100" placeholder="My GitHub">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="url">URL</label>
                        <input type="text" id="url" name="url" class="form-input"
                               required maxlength="512" placeholder="https://github.com/you">
                        <p class="form-hint">https:// is added automatically if you omit it.</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="description">Description <span class="text-dim">(optional)</span></label>
                        <input type="text" id="description" name="description" class="form-input" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="emoji">Emoji <span class="text-dim">(optional)</span></label>
                        <input type="text" id="emoji" name="emoji" class="form-input"
                               maxlength="16" placeholder="💻" style="max-width:110px">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add link</button>
            </form>
        </details>

        <?php if ($total === 0): ?>
            <div class="empty-state">
                <div class="icon" aria-hidden="true">🔗</div>
                <h3>No links yet</h3>
                <p>Your page works, it is just very quiet. Add your first link above.</p>
            </div>
        <?php else: ?>
            <p class="form-hint mb-2">
                Drag the handle to reorder, or use the arrows. Changes save automatically.
            </p>
            <p id="reorder-status" class="sr-only" role="status" aria-live="polite"></p>

            <div id="link-list" class="link-list">
                <?php foreach ($links as $i => $link): ?>
                    <div class="link-item<?= $link['is_enabled'] ? '' : ' is-disabled' ?>" data-id="<?= (int)$link['id'] ?>">
                        <span class="link-handle" title="Drag to reorder" aria-hidden="true">⠿</span>

                        <div class="link-move">
                            <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>/move">
                                <?= csrf_field() ?>
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" title="Move up" aria-label="Move <?= e($link['title']) ?> up"
                                        <?= $i === 0 ? 'disabled' : '' ?>>▲</button>
                            </form>
                            <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>/move">
                                <?= csrf_field() ?>
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" title="Move down" aria-label="Move <?= e($link['title']) ?> down"
                                        <?= $i === $total - 1 ? 'disabled' : '' ?>>▼</button>
                            </form>
                        </div>

                        <span class="link-emoji-badge" aria-hidden="true"><?= e($link['emoji'] ?: '🔗') ?></span>

                        <div class="link-body">
                            <strong><?= e($link['title']) ?></strong>
                            <span class="link-url"><?= e($link['url']) ?></span>
                            <div class="link-meta">
                                <?= e(format_number((int)$link['click_count'])) ?> clicks ·
                                <span class="badge <?= $link['is_enabled'] ? 'badge-on' : 'badge-off' ?>">
                                    <?= $link['is_enabled'] ? 'Visible' : 'Hidden' ?>
                                </span>
                            </div>
                        </div>

                        <div class="link-actions">
                            <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>/delete"
                                  data-confirm="Delete &quot;<?= e($link['title']) ?>&quot;? This cannot be undone.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--error)">Delete</button>
                            </form>
                        </div>
                    </div>

                    <details class="link-editor">
                        <summary>Edit</summary>
                        <form method="post" action="/dashboard/links/<?= (int)$link['id'] ?>" class="card">
                            <?= csrf_field() ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="title-<?= (int)$link['id'] ?>">Title</label>
                                    <input type="text" id="title-<?= (int)$link['id'] ?>" name="title" class="form-input"
                                           required maxlength="100" value="<?= e($link['title']) ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="url-<?= (int)$link['id'] ?>">URL</label>
                                    <input type="text" id="url-<?= (int)$link['id'] ?>" name="url" class="form-input"
                                           required maxlength="512" value="<?= e($link['url']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="desc-<?= (int)$link['id'] ?>">Description</label>
                                    <input type="text" id="desc-<?= (int)$link['id'] ?>" name="description" class="form-input"
                                           maxlength="255" value="<?= e($link['description'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="emoji-<?= (int)$link['id'] ?>">Emoji</label>
                                    <input type="text" id="emoji-<?= (int)$link['id'] ?>" name="emoji" class="form-input"
                                           maxlength="16" value="<?= e($link['emoji'] ?? '') ?>" style="max-width:110px">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="is_enabled" value="1" <?= $link['is_enabled'] ? 'checked' : '' ?>>
                                    <span class="form-check-text">
                                        Visible on my page
                                        <small>Uncheck to hide it without deleting it.</small>
                                    </span>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                        </form>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
