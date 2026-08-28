<?php ob_start(); ?>
<section class="section">
    <div class="container container-md">
        <div class="dash-header">
            <h1>Site Settings</h1>
            <a href="/admin" class="btn btn-ghost btn-sm">← Admin</a>
        </div>
        <div class="card">
            <form method="post" action="/admin/settings">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label">Site name</label>
                    <input type="text" name="site_name" class="form-input" value="<?= e($settings['site_name'] ?? 'pornhub.singles') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Site description</label>
                    <input type="text" name="site_description" class="form-input" value="<?= e($settings['site_description'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="registration_enabled" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Registration enabled
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="discovery_enabled" value="1" <?= ($settings['discovery_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Discovery enabled
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                        Maintenance mode
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Max upload size (bytes)</label>
                    <input type="number" name="max_upload_size" class="form-input" value="<?= e($settings['max_upload_size'] ?? '2097152') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</section>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
