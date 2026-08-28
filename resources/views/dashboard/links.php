<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/dashboard/_sidebar.php'; ?>
    </aside>
    <section class="dashboard-content">
        <div class="flex items-center justify-between mb-4">
            <h1>Manage Links</h1>
        </div>

        <div class="card mb-8">
            <h2>Add New Link</h2>
            <form action="<?= e(url('/dashboard/links')) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="flex gap-4" style="flex-wrap: wrap;">
                    <div class="input-group" style="flex: 1; min-width: 200px;">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" class="input" required>
                    </div>
                    <div class="input-group" style="flex: 2; min-width: 250px;">
                        <label for="url">URL *</label>
                        <input type="url" id="url" name="url" class="input" required>
                    </div>
                </div>
                <div class="flex gap-4" style="flex-wrap: wrap;">
                    <div class="input-group" style="flex: 1; min-width: 100px;">
                        <label for="emoji">Emoji</label>
                        <input type="text" id="emoji" name="emoji" class="input" maxlength="2">
                    </div>
                    <div class="input-group" style="flex: 3; min-width: 250px;">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" class="input">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Link</button>
            </form>
        </div>

        <h2>Your Links</h2>
        <?php if (empty($links)): ?>
            <div class="empty-state">
                <h3>No links yet</h3>
                <p>Add your first link above.</p>
            </div>
        <?php else: ?>
            <div class="sortable-list">
                <?php foreach ($links as $index => $link): ?>
                    <div class="link-row" data-id="<?= e($link['id']) ?>">
                        <div class="link-drag-handle" title="Drag to reorder">⣿</div>
                        <div class="link-info" style="<?= !$link['is_enabled'] ? 'opacity: 0.5;' : '' ?>">
                            <strong><?= $link['emoji'] ? e($link['emoji']) . ' ' : '' ?><?= e($link['title']) ?></strong>
                            <small><?= e($link['url']) ?></small>
                        </div>
                        <div class="link-actions">
                            <form action="<?= e(url('/dashboard/links/' . $link['id'] . '/move')) ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-sm" aria-label="Move up" <?= $index === 0 ? 'disabled' : '' ?>>▲</button>
                            </form>
                            <form action="<?= e(url('/dashboard/links/' . $link['id'] . '/move')) ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-sm" aria-label="Move down" <?= $index === count($links) - 1 ? 'disabled' : '' ?>>▼</button>
                            </form>
                        </div>
                    </div>
                    <details class="link-edit-details">
                        <summary style="cursor: pointer; font-weight: 600; margin-bottom: 1rem;">Edit details</summary>
                        <form action="<?= e(url('/dashboard/links/' . $link['id'])) ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="flex gap-4" style="flex-wrap: wrap;">
                                <div class="input-group" style="flex: 1; min-width: 200px;">
                                    <label>Title</label>
                                    <input type="text" name="title" class="input" value="<?= e($link['title']) ?>" required>
                                </div>
                                <div class="input-group" style="flex: 2; min-width: 250px;">
                                    <label>URL</label>
                                    <input type="url" name="url" class="input" value="<?= e($link['url']) ?>" required>
                                </div>
                            </div>
                            <div class="flex gap-4" style="flex-wrap: wrap;">
                                <div class="input-group" style="flex: 1; min-width: 100px;">
                                    <label>Emoji</label>
                                    <input type="text" name="emoji" class="input" value="<?= e($link['emoji']) ?>" maxlength="2">
                                </div>
                                <div class="input-group" style="flex: 3; min-width: 250px;">
                                    <label>Description</label>
                                    <input type="text" name="description" class="input" value="<?= e($link['description']) ?>">
                                </div>
                            </div>
                            <div class="input-group" style="flex-direction: row; align-items: center;">
                                <input type="checkbox" name="is_enabled" id="enabled_<?= $link['id'] ?>" value="1" <?= $link['is_enabled'] ? 'checked' : '' ?>>
                                <label for="enabled_<?= $link['id'] ?>">Enabled</label>
                            </div>
                            <div class="flex justify-between mt-4">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                        <form action="<?= e(url('/dashboard/links/' . $link['id'] . '/delete')) ?>" method="POST" style="margin-top: 1rem;" data-confirm="Are you sure you want to delete this link?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Delete Link</button>
                        </form>
                    </details>
                <?php endforeach; ?>
            </div>
            <p class="text-muted mt-4"><small>Drag and drop links or use the arrows to reorder.</small></p>
        <?php endif; ?>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
