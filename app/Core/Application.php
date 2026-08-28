<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\MaintenanceMiddleware;

class Application
{
    private Router $router;
    private array $config;

    public function __construct()
    {
        $this->loadEnv();
        $this->config = require BASE_PATH . '/config/app.php';
        $this->configureSession();
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function loadEnv(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\"'");
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    private function configureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = $this->isHttps();
            session_set_cookie_params([
                'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 7200),
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('phs_session');
            session_start();
        }
    }

    private function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        return false;
    }

    private function registerRoutes(): void
    {
        require BASE_PATH . '/routes/web.php';
    }

    public function run(): void
    {
        // Global middleware
        (new MaintenanceMiddleware())->handle();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rtrim($uri, '/') ?: '/';

        $this->router->dispatch($method, $uri);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
