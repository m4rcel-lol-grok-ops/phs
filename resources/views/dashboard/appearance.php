<?php ob_start(); ?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <div class="dash-content">
        <div class="dash-header"><h1>Appearance</h1></div>
        <p class="text-muted mb-2">Make it yours. Or make it unhinged. Your call.</p>

        <form method="post" action="/dashboard/appearance">
            <?= csrf_field() ?>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Theme</h3>
                <div class="theme-grid">
                    <?php
                    $themes = [
                        'hub' => ['Hub', 'linear-gradient(135deg,#0a0a0a,#ff9900)'],
                        'midnight' => ['Midnight', 'linear-gradient(135deg,#000,#1a1a2e)'],
                        'terminal' => ['Terminal', 'linear-gradient(135deg,#0a0a0a,#00ff00)'],
                        'corporate' => ['Corporate', 'linear-gradient(135deg,#1a1a1a,#ff9900)'],
                        'degenerate' => ['Degenerate', 'linear-gradient(135deg,#ff006e,#8338ec,#ff9900)'],
                        'minimal' => ['Minimal', 'linear-gradient(135deg,#111,#eee)'],
                    ];
                    $current = $profile['theme'] ?? 'hub';
                    foreach ($themes as $key => [$label, $swatch]):
                    ?>
                    <label class="theme-option <?= $current === $key ? 'selected' : '' ?>">
                        <input type="radio" name="theme" value="<?= $key ?>" <?= $current === $key ? 'checked' : '' ?>>
                        <div class="theme-swatch" style="background:<?= $swatch ?>"></div>
                        <span><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Colors</h3>
                <div class="color-row">
                    <div class="color-field">
                        <label>Background</label>
                        <input type="color" name="bg_color" value="<?= e($profile['bg_color'] ?? '#0a0a0a') ?>">
                    </div>
                    <div class="color-field">
                        <label>Card</label>
                        <input type="color" name="card_color" value="<?= e($profile['card_color'] ?? '#1a1a1a') ?>">
                    </div>
                    <div class="color-field">
                        <label>Accent</label>
                        <input type="color" name="accent_color" value="<?= e($profile['accent_color'] ?? '#ff9900') ?>">
                    </div>
                    <div class="color-field">
                        <label>Text</label>
                        <input type="color" name="text_color" value="<?= e($profile['text_color'] ?? '#ffffff') ?>">
                    </div>
                    <div class="color-field">
                        <label>Button</label>
                        <input type="color" name="button_color" value="<?= e($profile['button_color'] ?? '#ff9900') ?>">
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Background type</h3>
                <div class="form-group">
                    <select name="bg_type" class="form-select">
                        <?php foreach (['solid','gradient','image','url'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($profile['bg_type'] ?? 'solid') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Gradient CSS (if gradient)</label>
                    <input type="text" name="bg_gradient" class="form-input" value="<?= e($profile['bg_gradient'] ?? '') ?>" placeholder="linear-gradient(135deg, #0a0a0a, #1a0a00)">
                </div>
                <div class="form-group">
                    <label class="form-label">Remote background URL</label>
                    <input type="url" name="bg_url" class="form-input" value="<?= e($profile['bg_url'] ?? '') ?>">
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Font</h3>
                <select name="font_family" class="form-select">
                    <?php foreach (['system'=>'System','mono'=>'Monospace','serif'=>'Serif','rounded'=>'Rounded'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($profile['font_family'] ?? 'system') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Effects</h3>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="effects_enabled" value="1" <?= ($profile['effects_enabled'] ?? 0) ? 'checked' : '' ?>>
                        Enable visual effects
                    </label>
                </div>
                <div class="form-group">
                    <select name="effect_type" class="form-select">
                        <option value="">None</option>
                        <?php foreach (['particles','gradient','glow','snow','crt','scanlines'] as $e): ?>
                            <option value="<?= $e ?>" <?= ($profile['effect_type'] ?? '') === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <h3 style="margin-bottom:1rem">Music (optional)</h3>
                <div class="form-group">
                    <label class="form-label">Audio URL</label>
                    <input type="url" name="music_url" class="form-input" value="<?= e($profile['music_url'] ?? '') ?>" placeholder="https://example.com/track.mp3">
                </div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="music_title" class="form-input" value="<?= e($profile['music_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Artist</label>
                    <input type="text" name="music_artist" class="form-input" value="<?= e($profile['music_artist'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Appearance</button>
        </form>

        <div class="card" style="margin-top:1.5rem">
            <h3 style="margin-bottom:1rem">Banner</h3>
            <?php if ($profile['banner']): ?>
                <img src="/uploads/banners/<?= e($profile['banner']) ?>" alt="" style="max-height:80px;border-radius:8px;margin-bottom:1rem">
            <?php endif; ?>
            <form method="post" action="/dashboard/banner" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="file" name="banner" accept="image/jpeg,image/png,image/webp" class="form-input" style="margin-bottom:0.75rem">
                <button type="submit" class="btn btn-secondary btn-sm">Upload Banner</button>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
