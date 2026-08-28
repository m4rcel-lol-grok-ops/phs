<?php
declare(strict_types=1);

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        // Not logged in at all: send to login rather than showing a bare 403.
        if (current_user() === null) {
            (new AuthMiddleware())->handle();
            return;
        }
        if (is_admin()) {
            return;
        }
        http_response_code(403);
        if (wants_json()) {
            json_response(['error' => 'Administrator access required.'], 403);
        }
        require BASE_PATH . '/resources/views/errors/403.php';
        exit;
    }
}
