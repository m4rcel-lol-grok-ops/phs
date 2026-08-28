<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Resolves a stored profile row into the concrete values the public profile
 * page renders with: theme preset first, per-profile overrides on top.
 *
 * Every value that ends up inside a <style> block passes through the css_*
 * sanitizers, because HTML escaping does not apply inside <style>.
 */
class Theme
{
    public const FONTS = [
        'system' => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
        'mono' => "'SF Mono', ui-monospace, 'Fira Code', 'Cascadia Code', Consolas, monospace",
        'serif' => "'Iowan Old Style', Georgia, 'Times New Roman', serif",
        'rounded' => "ui-rounded, 'SF Pro Rounded', 'Segoe UI Rounded', 'Nunito', system-ui, sans-serif",
    ];

    public const EFFECTS = ['particles', 'gradient', 'glow', 'snow', 'crt', 'scanlines'];

    public static function all(): array
    {
        static $themes = null;
        if ($themes === null) {
            $themes = require BASE_PATH . '/config/themes.php';
        }
        return $themes;
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function get(?string $key): array
    {
        $all = self::all();
        return $all[$key ?? 'hub'] ?? $all['hub'];
    }

    /**
     * @return array{
     *   theme:string, colors:array<string,string>, font:string, radius:string,
     *   border:string, background:string, effect:?string, custom:bool
     * }
     */
    public static function resolve(array $profile): array
    {
        $key = in_array($profile['theme'] ?? '', self::keys(), true) ? $profile['theme'] : 'hub';
        $preset = self::get($key);
        $custom = (int)($profile['use_custom_colors'] ?? 0) === 1;

        $colors = [
            'bg' => $custom ? css_color($profile['bg_color'] ?? null, $preset['bg']) : $preset['bg'],
            'card' => $custom ? css_color($profile['card_color'] ?? null, $preset['card']) : $preset['card'],
            'accent' => $custom ? css_color($profile['accent_color'] ?? null, $preset['accent']) : $preset['accent'],
            'text' => $custom ? css_color($profile['text_color'] ?? null, $preset['text']) : $preset['text'],
            'button' => $custom ? css_color($profile['button_color'] ?? null, $preset['button']) : $preset['button'],
        ];

        // The font follows the theme unless the profile has opted into custom
        // styling, so switching theme does not leave the old font behind.
        $fontKey = $preset['font'];
        if ($custom && isset(self::FONTS[$profile['font_family'] ?? ''])) {
            $fontKey = $profile['font_family'];
        }

        $effect = null;
        if (!empty($profile['effects_enabled']) && in_array($profile['effect_type'] ?? '', self::EFFECTS, true)) {
            $effect = $profile['effect_type'];
        }

        return [
            'theme' => $key,
            'colors' => $colors,
            'font' => self::FONTS[$fontKey] ?? self::FONTS['system'],
            'font_key' => $fontKey,
            'radius' => $preset['radius'],
            'border' => $preset['card_border'],
            'background' => self::background($profile, $preset, $colors['bg']),
            'effect' => $effect,
            'custom' => $custom,
        ];
    }

    /** The `background` shorthand value for <body>, fully sanitized. */
    private static function background(array $profile, array $preset, string $bgColor): string
    {
        switch ($profile['bg_type'] ?? 'solid') {
            case 'gradient':
                $gradient = css_gradient($profile['bg_gradient'] ?? null, '');
                return $gradient !== '' ? $gradient . ' fixed' : $preset['bg_gradient'];

            case 'image':
                $file = upload_filename($profile['bg_image'] ?? null);
                if ($file !== null) {
                    return $bgColor . " url('/uploads/banners/" . $file . "') center / cover no-repeat fixed";
                }
                return $preset['bg_gradient'];

            case 'url':
                $url = css_url($profile['bg_url'] ?? null);
                if ($url !== null) {
                    return $bgColor . " url('" . $url . "') center / cover no-repeat fixed";
                }
                return $preset['bg_gradient'];

            case 'solid':
            default:
                // A profile that has never been customised should still look
                // designed, so "solid" on an untouched profile uses the preset.
                if (($profile['use_custom_colors'] ?? 0) || ($profile['bg_color'] ?? '') !== '') {
                    return $bgColor;
                }
                return $preset['bg_gradient'];
        }
    }

    /** rgba() string from a #rrggbb colour, for translucent surfaces. */
    public static function rgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return 'rgba(255,255,255,' . $alpha . ')';
        }
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        return sprintf('rgba(%d,%d,%d,%s)', $r, $g, $b, rtrim(rtrim(number_format($alpha, 3, '.', ''), '0'), '.'));
    }

    /** Perceived brightness, used to pick readable text on coloured buttons. */
    public static function isLight(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return false;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 150;
    }

    /** Readable foreground (#000/#fff) for a given background colour. */
    public static function contrast(string $hex): string
    {
        return self::isLight($hex) ? '#0a0a0a' : '#ffffff';
    }
}
