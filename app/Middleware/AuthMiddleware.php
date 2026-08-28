<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!is_logged_in()) {
            flash('error', 'Please log in to continue.');
            redirect('/login');
        }
    }
}
