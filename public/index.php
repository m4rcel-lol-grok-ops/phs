<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Helpers/helpers.php';

// Load .env (Docker Compose env_file already exports these; this covers
// non-Docker installs). Existing environment variables always win.
$envFile = BASE_PATH . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

$debug = in_array(strtolower((string)getenv('APP_DEBUG')), ['true', '1', 'yes'], true);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
if (is_dir($logDir) && is_writable($logDir)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

try {
    (new App\Core\Application())->run();
} catch (Throwable $e) {
    $msg = get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine()
        . "\n" . $e->getTraceAsString();
    error_log('[phs] ' . $msg);
    @file_put_contents($logDir . '/app-error.log', date('c') . ' ' . $msg . "\n\n", FILE_APPEND);

    if (!headers_sent()) {
        http_response_code(500);
    }
    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Error\n\n" . $msg;
    } elseif (file_exists(BASE_PATH . '/resources/views/errors/500.php')) {
        require BASE_PATH . '/resources/views/errors/500.php';
    } else {
        echo '500 — check storage/logs/app-error.log';
    }
}
