<?php ob_start(); ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <?php require BASE_PATH . '/resources/views/dashboard/_sidebar.php'; ?>
    </aside>
    <section class="dashboard-content">
        <h1 class="mb-4">Appearance</h1>
        
        <form action="<?= e(url('/dashboard/appearance')) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="card mb-4">
                <h2>Theme</h2>
                <div class="input-group">
                    <label for="theme">Base Theme</label>
                    <select id="theme" name="theme" class="select">
                        <?php foreach ($themes as $key => $themeDef): ?>
                            <option value="<?= e($key) ?>" <?= $profile['theme'] === $key ? 'selected' : '' ?>><?= e($themeDef['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card mb-4">
                <h2>Background</h2>
                <div class="input-group">
                    <label for="bg_type">Background Type</label>
                    <select id="bg_type" name="bg_type" class="select" data-conditions='{"bg-color-group": ["solid"], "bg-gradient-group": ["gradient"], "bg-url-group": ["url"]}'>
                        <option value="solid" <?= $profile['bg_type'] === 'solid' ? 'selected' : '' ?>>Solid Color</option>
                        <option value="gradient" <?= $profile['bg_type'] === 'gradient' ? 'selected' : '' ?>>CSS Gradient</option>
                        <option value="url" <?= $profile['bg_type'] === 'url' ? 'selected' : '' ?>>Image URL</option>
                    </select>
                </div>

                <div id="bg-color-group" class="input-group" <?= $profile['bg_type'] !== 'solid' ? 'hidden' : '' ?>>
                    <label for="bg_color">Background Color (Hex)</label>
                    <input type="color" id="bg_color" name="bg_color" class="input" value="<?= e($profile['bg_color'] ?? '#0b0a09') ?>" style="height: 50px;">
                </div>

                <div id="bg-gradient-group" class="input-group" <?= $profile['bg_type'] !== 'gradient' ? 'hidden' : '' ?>>
                    <label for="bg_gradient">CSS Gradient Value</label>
                    <input type="text" id="bg_gradient" name="bg_gradient" class="input" value="<?= e($profile['bg_gradient']) ?>" placeholder="linear-gradient(45deg, #000, #ff9900)">
                </div>

                <div id="bg-url-group" class="input-group" <?= $profile['bg_type'] !== 'url' ? 'hidden' : '' ?>>
                    <label for="bg_url">Image URL</label>
                    <input type="url" id="bg_url" name="bg_url" class="input" value="<?= e($profile['bg_url']) ?>" placeholder="https://example.com/bg.jpg">
                </div>
            </div>

            <div class="card mb-4">
                <h2>Custom Colors</h2>
                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="use_custom_colors" name="use_custom_colors" value="1" <?= $profile['use_custom_colors'] ? 'checked' : '' ?> data-conditions='{"custom-colors-wrapper": [true]}'>
                    <label for="use_custom_colors">Override theme colors</label>
                </div>

                <div id="custom-colors-wrapper" <?= !$profile['use_custom_colors'] ? 'hidden' : '' ?>>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <div class="input-group">
                            <label>Card Color</label>
                            <input type="color" name="card_color" class="input" value="<?= e($profile['card_color'] ?? '#151413') ?>" style="height: 50px;">
                        </div>
                        <div class="input-group">
                            <label>Accent Color</label>
                            <input type="color" name="accent_color" class="input" value="<?= e($profile['accent_color'] ?? '#ff9900') ?>" style="height: 50px;">
                        </div>
                        <div class="input-group">
                            <label>Text Color</label>
                            <input type="color" name="text_color" class="input" value="<?= e($profile['text_color'] ?? '#f5f5f5') ?>" style="height: 50px;">
                        </div>
                        <div class="input-group">
                            <label>Button Color</label>
                            <input type="color" name="button_color" class="input" value="<?= e($profile['button_color'] ?? '#1f1e1d') ?>" style="height: 50px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h2>Effects</h2>
                <div class="input-group" style="flex-direction: row; align-items: center;">
                    <input type="checkbox" id="effects_enabled" name="effects_enabled" value="1" <?= $profile['effects_enabled'] ? 'checked' : '' ?> data-conditions='{"effects-wrapper": [true]}'>
                    <label for="effects_enabled">Enable visual effects</label>
                </div>

                <div id="effects-wrapper" <?= !$profile['effects_enabled'] ? 'hidden' : '' ?>>
                    <div class="input-group mt-4">
                        <label for="effect_type">Effect Type</label>
                        <select id="effect_type" name="effect_type" class="select">
                            <option value="particles" <?= $profile['effect_type'] === 'particles' ? 'selected' : '' ?>>Particles (Floating orbs)</option>
                            <option value="snow" <?= $profile['effect_type'] === 'snow' ? 'selected' : '' ?>>Snow (Falling white dots)</option>
                            <option value="gradient" <?= $profile['effect_type'] === 'gradient' ? 'selected' : '' ?>>Gradient overlay</option>
                            <option value="glow" <?= $profile['effect_type'] === 'glow' ? 'selected' : '' ?>>Vignette glow</option>
                            <option value="crt" <?= $profile['effect_type'] === 'crt' ? 'selected' : '' ?>>CRT monitor</option>
                            <option value="scanlines" <?= $profile['effect_type'] === 'scanlines' ? 'selected' : '' ?>>Scanlines</option>
                        </select>
                        <div class="form-help">Effects are disabled for users who prefer reduced motion.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Appearance</button>
        </form>
    </section>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
