<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/admin/_nav.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Site Settings</h1>

        <form action="<?= e(url('/admin/settings')) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="card mb-4">
                <h2>Identity</h2>

                <div class="input-group">
                    <label for="site_name">Site name</label>
                    <input type="text" id="site_name" name="site_name" class="input"
                           maxlength="64" value="<?= e($settings['site_name'] ?? '') ?>">
                </div>

                <div class="input-group">
                    <label for="site_description">Site description</label>
                    <input type="text" id="site_description" name="site_description" class="input"
                           maxlength="255" value="<?= e($settings['site_description'] ?? '') ?>">
                    <p class="form-help">Used as the default meta description and footer tagline.</p>
                </div>
            </div>

            <div class="card mb-4">
                <h2>General</h2>

                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="registration_enabled" name="registration_enabled" value="1"
                           <?= !empty($settings['registration_enabled']) ? 'checked' : '' ?>>
                    <label for="registration_enabled">Enable user registration</label>
                </div>

                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="discovery_enabled" name="discovery_enabled" value="1"
                           <?= !empty($settings['discovery_enabled']) ? 'checked' : '' ?>>
                    <label for="discovery_enabled">Enable public discovery directory</label>
                </div>

                <div class="input-group mt-4">
                    <label style="color:#ef4444;" for="maintenance_mode">Maintenance Mode</label>
                    <div style="flex-direction: row; align-items: center; display: flex;">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1"
                               <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                        <label for="maintenance_mode" style="margin-left: 0.5rem;">Take site offline for non-admins</label>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h2>Uploads</h2>
                <div class="input-group">
                    <label for="max_upload_size">Maximum upload size (bytes)</label>
                    <input type="number" id="max_upload_size" name="max_upload_size" class="input"
                           min="65536" step="1024" value="<?= (int)($settings['max_upload_size'] ?? 2097152) ?>">
                    <p class="form-help">
                        Currently <?= e(number_format(((int)($settings['max_upload_size'] ?? 2097152)) / 1048576, 2)) ?> MB.
                        Capped to what PHP itself allows per upload.
                    </p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
