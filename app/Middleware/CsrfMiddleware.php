<?php
declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (verify_csrf(is_string($token) ? $token : '')) {
            return;
        }

        http_response_code(403);
        if (wants_json()) {
            json_response(['error' => 'Invalid or expired CSRF token.'], 403);
        }
        // Almost always an expired session rather than an attack, so say so.
        $csrf_expired = true;
        require BASE_PATH . '/resources/views/errors/403.php';
        exit;
    }
}
