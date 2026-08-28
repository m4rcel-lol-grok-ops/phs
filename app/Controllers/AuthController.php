<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Core\Database;

class AuthController
{
    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        view('auth.login', ['title' => 'Login — pornhub.singles']);
    }

    public function login(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        if (!rate_limit('login_' . client_ip(), 10, 300)) {
            http_response_code(429);
            require BASE_PATH . '/resources/views/errors/429.php';
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['_old'] = ['email' => $email];

        if (!$email || !$password) {
            flash('error', 'Email and password are required.');
            redirect('/login');
        }

        $user = User::findByEmail($email);
        if (!$user) {
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        if ($user['is_disabled']) {
            flash('error', 'This account has been disabled.');
            redirect('/login');
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            flash('error', 'Account temporarily locked. Try again later.');
            redirect('/login');
        }

        if (!password_verify($password, $user['password_hash'])) {
            User::incrementLoginAttempts((int)$user['id']);
            if ((int)$user['login_attempts'] + 1 >= 5) {
                User::lockAccount((int)$user['id']);
            }
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        User::resetLoginAttempts((int)$user['id']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        unset($_SESSION['_old']);
        flash('success', 'Welcome back, internet celebrity.');
        redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        if (!env('REGISTRATION_ENABLED', true)) {
            flash('error', 'Registration is currently disabled.');
            redirect('/');
        }
        view('auth.register', ['title' => 'Create Profile — pornhub.singles']);
    }

    public function register(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        if (!env('REGISTRATION_ENABLED', true)) {
            flash('error', 'Registration is currently disabled.');
            redirect('/');
        }

        if (!rate_limit('register_' . client_ip(), 5, 3600)) {
            http_response_code(429);
            require BASE_PATH . '/resources/views/errors/429.php';
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $_SESSION['_old'] = compact('username', 'email');

        $errors = [];
        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
            $errors[] = 'Username must be 3-32 characters (letters, numbers, underscores).';
        }
        $reserved = require BASE_PATH . '/config/reserved.php';
        if (in_array(strtolower($username), $reserved, true)) {
            $errors[] = 'This username is reserved.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (User::findByUsername($username)) {
            $errors[] = 'Username already taken.';
        }
        if (User::findByEmail($email)) {
            $errors[] = 'Email already registered.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/register');
        }

        $id = User::create($username, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        unset($_SESSION['_old']);
        flash('success', 'Welcome! Your unnecessarily dramatic profile is ready.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('/');
    }
}
