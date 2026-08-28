<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (current_user() !== null) {
            return;
        }
        if (wants_json()) {
            json_response(['error' => 'Authentication required.'], 401);
        }
        // Send people back where they were headed after they log in.
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && is_string($uri) && str_starts_with($uri, '/')) {
            $_SESSION['_intended'] = $uri;
        }
        flash('error', 'Please log in to continue.');
        redirect('/login');
    }
}
