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

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    // Prevent open redirects
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST);
        if ($host !== $appHost) {
            $url = '/';
        }
    }
    header("Location: $url", true, $code);
    exit;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
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

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare('SELECT id, username, email, role, is_verified, is_disabled, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user || $user['is_disabled']) {
            session_destroy();
            return null;
        }
    }
    return $user;
}

function is_admin(): bool
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
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
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function rate_limit(string $key, int $max, int $windowSeconds): bool
{
    $dir = BASE_PATH . '/storage/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . '/rl_' . md5($key) . '.json';
    $now = time();
    $data = ['count' => 0, 'start' => $now];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
        if ($now - $data['start'] > $windowSeconds) {
            $data = ['count' => 0, 'start' => $now];
        }
    }
    $data['count']++;
    file_put_contents($file, json_encode($data), LOCK_EX);
    return $data['count'] <= $max;
}

function client_ip(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function format_number(int $n): string
{
    if ($n >= 1000000) {
        return round($n / 1000000, 1) . 'M';
    }
    if ($n >= 1000) {
        return round($n / 1000, 1) . 'K';
    }
    return (string)$n;
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M Y', strtotime($datetime));
}
