<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\MaintenanceMiddleware;

class Application
{
    private Router $router;
    private array $config;

    public function __construct()
    {
        // .env is loaded once in public/index.php before the autoloader runs.
        $this->config = require BASE_PATH . '/config/app.php';
        $this->configureSession();
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function configureSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $sessionPath = BASE_PATH . '/storage/sessions';
        if (!is_dir($sessionPath)) {
            @mkdir($sessionPath, 0775, true);
        }
        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            session_save_path($sessionPath);
        }

        $secure = $this->isHttps();
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie; idle expiry is enforced below.
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('phs_session');
        session_start();

        $this->enforceSessionLifetime();
    }

    /**
     * Log out sessions idle longer than SESSION_LIFETIME. The cookie itself is
     * a session cookie, so this is what actually bounds a login.
     */
    private function enforceSessionLifetime(): void
    {
        $lifetime = (int)(env('SESSION_LIFETIME', 7200) ?: 7200);
        $now = time();
        if (isset($_SESSION['_last_seen']) && $now - (int)$_SESSION['_last_seen'] > $lifetime) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['_last_seen'] = $now;
    }

    private function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (env('TRUST_PROXY', true)) {
            $proto = strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
            if ($proto === 'https') {
                return true;
            }
        }
        return false;
    }

    /**
     * Set security headers from PHP so they apply even when the app is served
     * by a web server that does not read the .htaccess file.
     */
    private function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header_remove('X-Powered-By');
        if ($this->isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    private function registerRoutes(): void
    {
        $app = $this; // routes/web.php expects $app
        require BASE_PATH . '/routes/web.php';
    }

    public function run(): void
    {
        $this->sendSecurityHeaders();

        // Opportunistic cleanup of expired rate-limit files (~1 request in 200).
        if (random_int(1, 200) === 1) {
            rate_limit_gc();
        }

        (new MaintenanceMiddleware())->handle();

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);

        // Normalise trailing slashes with a redirect so each page has one URL.
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $trimmed = rtrim($uri, '/') ?: '/';
            if ($method === 'GET' || $method === 'HEAD') {
                $query = $_SERVER['QUERY_STRING'] ?? '';
                redirect($trimmed . ($query !== '' ? '?' . $query : ''), 301);
            }
            $uri = $trimmed;
        }

        $this->router->dispatch($method, $uri);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
