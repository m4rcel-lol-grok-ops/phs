<?php ob_start(); ?>
<section class="section-tight">
    <div class="container container-md">
        <div class="dash-header">
            <div>
                <h1>Site settings</h1>
                <p>Saved to the database and applied immediately — no restart needed.</p>
            </div>
        </div>

        <?php require __DIR__ . '/_nav.php'; ?>

        <?php if ($settings['maintenance_mode']): ?>
            <div class="alert alert-error mb-3" role="status">
                Maintenance mode is <strong>on</strong>. Visitors see a 503 page;
                administrators keep full access.
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/settings">
            <?= csrf_field() ?>

            <div class="card mb-3">
                <div class="card-head"><h3>Identity</h3></div>
                <div class="form-group">
                    <label class="form-label" for="site_name">Site name</label>
                    <input type="text" id="site_name" name="site_name" class="form-input"
                           maxlength="64" value="<?= e($settings['site_name'] ?? '') ?>">
                    <p class="form-hint">Used in page titles and the footer.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="site_description">Site description</label>
                    <input type="text" id="site_description" name="site_description" class="form-input"
                           maxlength="255" value="<?= e($settings['site_description'] ?? '') ?>">
                    <p class="form-hint">The default meta description and footer tagline.</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head"><h3>Access</h3></div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="registration_enabled" value="1" <?= $settings['registration_enabled'] ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            Registration open
                            <small>When off, the sign-up page is closed and existing users can still log in.</small>
                        </span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="discovery_enabled" value="1" <?= $settings['discovery_enabled'] ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            Discover page enabled
                            <small>When off, profiles stay reachable by direct link but are not browsable or listed in the sitemap.</small>
                        </span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            Maintenance mode
                            <small>Everyone but administrators gets a 503 page. You keep access to /admin and /login.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head"><h3>Uploads</h3></div>
                <div class="form-group">
                    <label class="form-label" for="max_upload_size">Maximum upload size (bytes)</label>
                    <input type="number" id="max_upload_size" name="max_upload_size" class="form-input"
                           min="65536" step="1024" value="<?= (int)$settings['max_upload_size'] ?>">
                    <p class="form-hint">
                        Currently <?= e(number_format($settings['max_upload_size'] / 1048576, 2)) ?> MB.
                        PHP itself allows at most <?= e(ini_get('upload_max_filesize')) ?> per file
                        (<code>upload_max_filesize</code>), and larger values here are capped to that.
                    </p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save settings</button>
        </form>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
