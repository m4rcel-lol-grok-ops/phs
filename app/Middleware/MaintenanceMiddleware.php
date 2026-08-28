<?php
declare(strict_types=1);

namespace App\Middleware;

class MaintenanceMiddleware
{
    public function handle(): void
    {
        if (env('MAINTENANCE_MODE', false) && !is_admin()) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $allowed = ['/login', '/admin'];
            if (!in_array($uri, $allowed, true) && !str_starts_with($uri, '/admin')) {
                http_response_code(503);
                require BASE_PATH . '/resources/views/errors/maintenance.php';
                exit;
            }
        }
    }
}
