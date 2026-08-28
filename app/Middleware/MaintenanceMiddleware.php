<?php
declare(strict_types=1);

namespace App\Middleware;

class MaintenanceMiddleware
{
    public function handle(): void
    {
        if (!setting_bool('maintenance_mode', false)) {
            return;
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Admins need to reach the panel to turn maintenance back off, and
        // everyone needs the login page and the stylesheet to get there.
        if (str_starts_with($uri, '/admin') || $uri === '/login' || $uri === '/logout' || $uri === '/health') {
            return;
        }
        if (is_admin()) {
            return;
        }

        http_response_code(503);
        header('Retry-After: 3600');
        require BASE_PATH . '/resources/views/errors/maintenance.php';
        exit;
    }
}
