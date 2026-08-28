<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

// Error reporting based on env
$errorReporting = getenv('APP_DEBUG') === 'true' ? E_ALL : 0;
error_reporting($errorReporting);
ini_set('display_errors', getenv('APP_DEBUG') === 'true' ? '1' : '0');

// Autoload
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

// Load helpers
require BASE_PATH . '/app/Helpers/helpers.php';

// Bootstrap
try {
    $app = new App\Core\Application();
    $app->run();
} catch (Throwable $e) {
    if (getenv('APP_DEBUG') === 'true') {
        http_response_code(500);
        echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        require BASE_PATH . '/resources/views/errors/500.php';
    }
}
