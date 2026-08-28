<?php
declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return match (strtolower($value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => $value,
    };
}

/** Runtime site setting (database first, env fallback). */
function setting(string $key, ?string $default = null): ?string
{
    return \App\Core\Settings::get($key, $default);
}

function setting_bool(string $key, bool $default = false): bool
{
    return \App\Core\Settings::bool($key, $default);
}

function setting_int(string $key, int $default = 0): int
{
    return \App\Core\Settings::int($key, $default);
}

function site_name(): string
{
    return setting('site_name', 'pornhub.singles') ?: 'pornhub.singles';
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/*
 * ---------------------------------------------------------------------------
 * CSS sanitizers
 *
 * Profile appearance values are user supplied and get inlined into a <style>
 * block. HTML escaping does not apply inside <style>, so anything containing
 * "<", "}" or quotes could terminate the element and inject markup. These
 * helpers whitelist instead of escape.
 * ---------------------------------------------------------------------------
 */

/** A #rrggbb (or #rgb) colour, or the fallback. */
function css_color(?string $value, string $fallback): string
{
    $value = trim((string)$value);
    return preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) ? $value : $fallback;
}

/**
 * A CSS gradient/colour expression limited to a safe character set. Rejects
 * anything with markup, comments, at-rules, semicolons or url() calls.
 */
function css_gradient(?string $value, string $fallback = ''): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return $fallback;
    }
    if (strlen($value) > 400) {
        return $fallback;
    }
    if (preg_match('/[<>{};@\\\\"\']|\/\*|url\s*\(|expression|javascript:|behaviou?r/i', $value)) {
        return $fallback;
    }
    // Letters, digits, whitespace and the punctuation gradients legitimately use.
    if (!preg_match('/^[a-zA-Z0-9\s,.%#()\-]+$/', $value)) {
        return $fallback;
    }
    if (substr_count($value, '(') !== substr_count($value, ')')) {
        return $fallback;
    }
    return $value;
}

/** An absolute http(s) image URL safe to place inside url("..."). */
function css_url(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '' || strlen($value) > 512) {
        return null;
    }
    if (!preg_match('#^https?://#i', $value)) {
        return null;
    }
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return null;
    }
    // Anything that could close the url(), the declaration or the <style>.
    if (preg_match('/["\'()<>{};\\\\]|\s/', $value)) {
        return null;
    }
    return $value;
}

/** A locally uploaded filename — hex name plus known extension only. */
function upload_filename(?string $value): ?string
{
    $value = (string)$value;
    return preg_match('/^[a-f0-9]{16,64}\.(jpg|png|webp)$/i', $value) ? $value : null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function redirect(string $url, int $code = 302): never
{
    // Prevent open redirects: absolute URLs must point at this host, and
    // protocol-relative URLs (//evil.com) are never allowed.
    if (str_starts_with($url, '//')) {
        $url = '/';
    } elseif (preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url)) {
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url((string)env('APP_URL', 'http://localhost'), PHP_URL_HOST);
        if (!$host || $host !== $appHost) {
            $url = '/';
        }
    } elseif (!str_starts_with($url, '/')) {
        $url = '/';
    }
    header("Location: $url", true, $code);
    exit;
}

/** Redirect back to the referring page, but only within this site. */
function redirect_back(string $fallback = '/'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $appHost = parse_url((string)env('APP_URL', ''), PHP_URL_HOST);
        $host = parse_url($referer, PHP_URL_HOST);
        if ($host && $appHost && $host === $appHost) {
            $path = parse_url($referer, PHP_URL_PATH) ?: '/';
            $query = parse_url($referer, PHP_URL_QUERY);
            redirect($path . ($query ? '?' . $query : ''));
        }
    }
    redirect($fallback);
}

/** Remember submitted input for one redirect so forms can be repopulated. */
function remember_old(array $values): void
{
    $_SESSION['_old'] = $values;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

/** Called once per rendered page: old input must not survive a second view. */
function clear_old(): void
{
    unset($_SESSION['_old']);
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

/** Render any pending success/error flashes as alert markup. */
function flash_alerts(): string
{
    $html = '';
    foreach (['success' => 'alert-success', 'error' => 'alert-error', 'info' => 'alert-info'] as $key => $class) {
        if ($msg = flash($key)) {
            $html .= '<div class="alert ' . $class . '" role="alert">' . e($msg) . '</div>';
        }
    }
    return $html;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    static $user = null;
    static $resolved = false;

    if ($resolved) {
        return $user;
    }
    if (!is_logged_in()) {
        $resolved = true;
        return null;
    }

    $resolved = true;
    try {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare('SELECT id, username, email, role, is_verified, is_disabled, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }

    // Account deleted or disabled while the session was still alive: log out
    // cleanly instead of leaving a half-authenticated request.
    if (!$row || $row['is_disabled']) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $user = null;
        return null;
    }

    $user = $row;
    return $user;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $url = '/assets/' . ltrim($path, '/');
    // Cache-bust on deploy so stylesheet changes are picked up immediately.
    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }
    return $url;
}

function url(string $path = ''): string
{
    $base = rtrim((string)env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function view(string $name, array $data = []): void
{
    extract($data);
    $viewFile = BASE_PATH . '/resources/views/' . str_replace('.', '/', $name) . '.php';
    if (!file_exists($viewFile)) {
        throw new RuntimeException("View not found: $name");
    }
    require $viewFile;
    clear_old();
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function wants_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

/**
 * Fixed-window rate limiter. Reads and writes under a single exclusive lock so
 * concurrent requests cannot both read a stale count.
 */
function rate_limit(string $key, int $max, int $windowSeconds): bool
{
    $dir = BASE_PATH . '/storage/cache';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return true; // Cannot track: fail open rather than lock everyone out.
    }
    $file = $dir . '/rl_' . hash('sha256', $key) . '.json';
    $handle = @fopen($file, 'c+');
    if ($handle === false) {
        return true;
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            return true;
        }
        $now = time();
        $raw = stream_get_contents($handle) ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['count'], $data['start']) || $now - (int)$data['start'] > $windowSeconds) {
            $data = ['count' => 0, 'start' => $now];
        }
        $data['count'] = (int)$data['count'] + 1;
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data));
        fflush($handle);
        flock($handle, LOCK_UN);
        return $data['count'] <= $max;
    } finally {
        fclose($handle);
    }
}

/** Drop rate-limit files older than a day so storage/cache cannot grow forever. */
function rate_limit_gc(): void
{
    $dir = BASE_PATH . '/storage/cache';
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '/rl_*.json') ?: [] as $file) {
        if (@filemtime($file) < time() - 86400) {
            @unlink($file);
        }
    }
}

function client_ip(): string
{
    // Only trust forwarding headers when the app is actually behind a proxy.
    if (env('TRUST_PROXY', true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function format_number(int $n): string
{
    if ($n >= 1000000) {
        return rtrim(rtrim(number_format($n / 1000000, 1, '.', ''), '0'), '.') . 'M';
    }
    if ($n >= 1000) {
        return rtrim(rtrim(number_format($n / 1000, 1, '.', ''), '0'), '.') . 'K';
    }
    return (string)$n;
}

function time_ago(string $datetime): string
{
    $ts = strtotime($datetime);
    if ($ts === false) {
        return 'unknown';
    }
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M Y', $ts);
}

/** Hostname of an external link, for display next to link cards. */
function link_host(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST);
    return $host ? preg_replace('/^www\./', '', $host) : '';
}
