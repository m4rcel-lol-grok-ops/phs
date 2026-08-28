<?php
declare(strict_types=1);

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!is_logged_in() || !is_admin()) {
            http_response_code(403);
            require BASE_PATH . '/resources/views/errors/403.php';
            exit;
        }
    }
}
