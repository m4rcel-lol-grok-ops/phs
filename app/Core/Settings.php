<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Site settings live in the site_settings table so the admin panel can change
 * them at runtime. Environment variables act as the fallback (and as the
 * bootstrap default before the row exists), which keeps the documented .env
 * knobs working on a fresh install.
 */
class Settings
{
    private const ENV_MAP = [
        'site_name' => 'APP_NAME',
        'site_description' => null,
        'registration_enabled' => 'REGISTRATION_ENABLED',
        'discovery_enabled' => 'DISCOVERY_ENABLED',
        'maintenance_mode' => 'MAINTENANCE_MODE',
        'max_upload_size' => 'UPLOAD_MAX_SIZE',
    ];

    private const DEFAULTS = [
        'site_name' => 'pornhub.singles',
        'site_description' => 'A completely unnecessary bio-link website.',
        'registration_enabled' => '1',
        'discovery_enabled' => '1',
        'maintenance_mode' => '0',
        'max_upload_size' => '2097152',
    ];

    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                $stmt = Database::getInstance()->query('SELECT setting_key, setting_value FROM site_settings');
                foreach ($stmt->fetchAll() as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Throwable) {
                // Database not reachable yet (first boot, migrations running).
                // Fall through to env/defaults rather than taking the site down.
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }
        $envKey = self::ENV_MAP[$key] ?? null;
        if ($envKey !== null) {
            $envValue = getenv($envKey);
            if ($envValue !== false && $envValue !== '') {
                return self::normalize($envValue);
            }
        }
        return $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');
        return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key, (string)$default);
        return is_numeric($value) ? (int)$value : $default;
    }

    public static function set(string $key, string $value): void
    {
        Database::getInstance()
            ->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
            ->execute([$key, $value]);
        self::$cache[$key] = $value;
    }

    /** Turn env-style booleans into the '1'/'0' the settings table stores. */
    private static function normalize(string $value): string
    {
        return match (strtolower($value)) {
            'true', 'yes', 'on' => '1',
            'false', 'no', 'off' => '0',
            default => $value,
        };
    }
}
