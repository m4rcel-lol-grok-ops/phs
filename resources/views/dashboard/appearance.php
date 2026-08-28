<?php
use App\Core\Theme;

ob_start();
$current = $profile['theme'] ?? 'hub';
$custom = (int)($profile['use_custom_colors'] ?? 0) === 1;
$preset = Theme::get($current);
$banner = upload_filename($profile['banner'] ?? null);
?>
<div class="dash-layout">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <h1>Appearance</h1>
                <p>Make it yours. Or make it unhinged. Your call.</p>
            </div>
            <a href="/<?= e($user['username']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Preview ↗</a>
        </div>

        <form method="post" action="/dashboard/appearance">
            <?= csrf_field() ?>

            <div class="card mb-3">
                <div class="card-head">
                    <h3>Theme</h3>
                    <span class="text-dim text-sm">Sets the whole palette</span>
                </div>
                <div class="theme-grid">
                    <?php foreach ($themes as $key => $t): ?>
                        <label class="theme-option">
                            <input type="radio" name="theme" value="<?= e($key) ?>" <?= $current === $key ? 'checked' : '' ?>>
                            <span class="theme-swatch" style="background:<?= $t['swatch'] ?>" aria-hidden="true"></span>
                            <span class="theme-name"><?= e($t['label']) ?></span>
                            <span class="theme-blurb"><?= e($t['blurb']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head"><h3>Colours</h3></div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="use_custom_colors" value="1"
                               data-toggles="custom-colors" <?= $custom ? 'checked' : '' ?>>
                        <span class="form-check-text">
                            Override the theme with my own colours
                            <small>Leave this off to always follow the theme you picked above.</small>
                        </span>
                    </label>
                </div>

                <div id="custom-colors" class="conditional-group<?= $custom ? ' is-visible' : '' ?>">
                    <div class="color-row mb-2">
                        <div class="color-field">
                            <label for="bg_color">Background</label>
                            <input type="color" id="bg_color" name="bg_color"
                                   value="<?= e(css_color($profile['bg_color'] ?? null, $preset['bg'])) ?>">
                        </div>
                        <div class="color-field">
                            <label for="card_color">Card</label>
                            <input type="color" id="card_color" name="card_color"
                                   value="<?= e(css_color($profile['card_color'] ?? null, $preset['card'])) ?>">
                        </div>
                        <div class="color-field">
                            <label for="accent_color">Accent</label>
                            <input type="color" id="accent_color" name="accent_color"
                                   value="<?= e(css_color($profile['accent_color'] ?? null, $preset['accent'])) ?>">
                        </div>
                        <div class="color-field">
                            <label for="text_color">Text</label>
                            <input type="color" id="text_color" name="text_color"
                                   value="<?= e(css_color($profile['text_color'] ?? null, $preset['text'])) ?>">
                        </div>
                        <div class="color-field">
                            <label for="button_color">Buttons</label>
                            <input type="color" id="button_color" name="button_color"
                                   value="<?= e(css_color($profile['button_color'] ?? null, $preset['button'])) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="font_family">Font</label>
                        <select name="font_family" id="font_family" class="form-select">
                            <?php foreach (['system' => 'System', 'mono' => 'Monospace', 'serif' => 'Serif', 'rounded' => 'Rounded'] as $k => $label): ?>
                                <option value="<?= e($k) ?>" <?= ($profile['font_family'] ?? 'system') === $k ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head"><h3>Background</h3></div>
                <div class="form-group">
                    <label class="form-label" for="bg_type">Background type</label>
                    <select name="bg_type" id="bg_type" class="form-select">
                        <option value="solid" <?= ($profile['bg_type'] ?? 'solid') === 'solid' ? 'selected' : '' ?>>Theme default / solid colour</option>
                        <option value="gradient" <?= ($profile['bg_type'] ?? '') === 'gradient' ? 'selected' : '' ?>>Custom gradient</option>
                        <option value="image" <?= ($profile['bg_type'] ?? '') === 'image' ? 'selected' : '' ?>>Uploaded image (uses your banner)</option>
                        <option value="url" <?= ($profile['bg_type'] ?? '') === 'url' ? 'selected' : '' ?>>Image from a URL</option>
                    </select>
                </div>

                <div class="conditional-group" data-shows-when="bg_type:gradient">
                    <div class="form-group">
                        <label class="form-label" for="bg_gradient">Gradient</label>
                        <input type="text" id="bg_gradient" name="bg_gradient" class="form-input"
                               value="<?= e($profile['bg_gradient'] ?? '') ?>"
                               placeholder="linear-gradient(135deg, #0a0a0a, #ff9900)">
                        <p class="form-hint">
                            Letters, numbers and <code>, . % # ( ) -</code> only. Quotes, semicolons
                            and <code>url()</code> are rejected.
                        </p>
                    </div>
                </div>

                <div class="conditional-group" data-shows-when="bg_type:url">
                    <div class="form-group">
                        <label class="form-label" for="bg_url">Image URL</label>
                        <input type="url" id="bg_url" name="bg_url" class="form-input"
                               value="<?= e($profile['bg_url'] ?? '') ?>" placeholder="https://example.com/wallpaper.jpg">
                        <p class="form-hint">Must be a plain https:// address with no spaces or quotes.</p>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head">
                    <h3>Effects</h3>
                    <span class="text-dim text-sm">Off for reduced-motion visitors</span>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="effects_enabled" value="1"
                               data-toggles="effect-picker" <?= ($profile['effects_enabled'] ?? 0) ? 'checked' : '' ?>>
                        <span class="form-check-text">Enable a visual effect on my page</span>
                    </label>
                </div>
                <div id="effect-picker" class="conditional-group<?= ($profile['effects_enabled'] ?? 0) ? ' is-visible' : '' ?>">
                    <label class="form-label" for="effect_type">Effect</label>
                    <select name="effect_type" id="effect_type" class="form-select">
                        <option value="">None</option>
                        <?php
                        $effectLabels = [
                            'particles' => 'Particles — floating accent dots',
                            'gradient' => 'Gradient — slow rotating colour wash',
                            'glow' => 'Glow — breathing halo behind the card',
                            'snow' => 'Snow — falling white flecks',
                            'crt' => 'CRT — scanlines with a rolling band',
                            'scanlines' => 'Scanlines — static, no motion',
                        ];
                        foreach ($effectLabels as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($profile['effect_type'] ?? '') === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-head">
                    <h3>Music</h3>
                    <span class="text-dim text-sm">Never autoplays</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="music_url">Audio URL</label>
                    <input type="url" id="music_url" name="music_url" class="form-input"
                           value="<?= e($profile['music_url'] ?? '') ?>" placeholder="https://example.com/track.mp3">
                    <p class="form-hint">A direct link to an audio file. Visitors press play themselves.</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="music_title">Track title</label>
                        <input type="text" id="music_title" name="music_title" class="form-input"
                               maxlength="128" value="<?= e($profile['music_title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="music_artist">Artist</label>
                        <input type="text" id="music_artist" name="music_artist" class="form-input"
                               maxlength="128" value="<?= e($profile['music_artist'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save appearance</button>
        </form>

        <div class="card mt-3">
            <div class="card-head"><h3>Banner image</h3></div>
            <?php if ($banner !== null): ?>
                <img src="/uploads/banners/<?= e($banner) ?>" alt="Your current banner"
                     style="max-height:110px;width:100%;object-fit:cover;border-radius:var(--radius-sm)" class="mb-2">
            <?php endif; ?>
            <form method="post" action="/dashboard/banner" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="sr-only" for="banner">Banner image file</label>
                    <input type="file" id="banner" name="banner" class="form-file"
                           accept="image/jpeg,image/png,image/webp" required>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Upload banner</button>
            </form>
            <?php if ($banner !== null): ?>
                <form method="post" action="/dashboard/banner/delete" class="mt-2" data-confirm="Remove your banner?">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-ghost btn-xs">Remove banner</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';
