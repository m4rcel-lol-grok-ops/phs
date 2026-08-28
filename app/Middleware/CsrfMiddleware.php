<?php
declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!verify_csrf($token)) {
                http_response_code(403);
                if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                    json_response(['error' => 'Invalid CSRF token'], 403);
                }
                require BASE_PATH . '/resources/views/errors/403.php';
                exit;
            }
        }
    }
}
