<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        view('auth.login', ['title' => 'Log in — ' . site_name()]);
    }

    public function login(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        if (!rate_limit('login_' . client_ip(), 10, 300)) {
            http_response_code(429);
            require BASE_PATH . '/resources/views/errors/429.php';
            exit;
        }

        // The field accepts either identifier: the docs hand out the admin
        // *username*, so email-only login looked like "wrong password".
        $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        remember_old(['identifier' => $identifier]);

        if ($identifier === '' || $password === '') {
            flash('error', 'Enter your username or email and your password.');
            redirect('/login');
        }

        $user = str_contains($identifier, '@')
            ? User::findByEmail($identifier)
            : User::findByUsername($identifier);

        // Fall back to the other column so an email-shaped username (or a
        // username typed into a browser-autofilled email field) still works.
        if (!$user) {
            $user = User::findByUsernameOrEmail($identifier);
        }

        if (!$user) {
            // Equalise timing with the password_verify path below so this
            // endpoint does not leak which accounts exist.
            password_verify($password, '$2y$10$QQD9GapoauwyF94OP/5WSueqBgybE9dHu9ZhQWMCv.MDb6Pj1jHgS');
            flash('error', 'Invalid credentials.');
            redirect('/login');
        }

        if ($user['is_disabled']) {
            flash('error', 'This account has been disabled.');
            redirect('/login');
        }

        if ($user['locked_until'] && strtotime((string)$user['locked_until']) > time()) {
            flash('error', 'Too many failed attempts. Try again in a few minutes.');
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

        // Upgrade legacy hashes transparently on successful login.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int)$user['id'], $password);
        }

        User::resetLoginAttempts((int)$user['id']);
        $this->startSession((int)$user['id']);
        flash('success', 'Welcome back, internet celebrity.');
        redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        if (!setting_bool('registration_enabled', true)) {
            flash('error', 'Registration is currently closed.');
            redirect('/');
        }
        view('auth.register', ['title' => 'Create your profile — ' . site_name()]);
    }

    public function register(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        if (!setting_bool('registration_enabled', true)) {
            flash('error', 'Registration is currently closed.');
            redirect('/');
        }

        if (!rate_limit('register_' . client_ip(), 5, 3600)) {
            http_response_code(429);
            require BASE_PATH . '/resources/views/errors/429.php';
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

        remember_old(compact('username', 'email'));

        $errors = [];
        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
            $errors[] = 'Username must be 3–32 characters (letters, numbers, underscores).';
        } else {
            $reserved = require BASE_PATH . '/config/reserved.php';
            if (in_array(strtolower($username), $reserved, true)) {
                $errors[] = 'That username is reserved.';
            } elseif (User::findByUsername($username)) {
                $errors[] = 'That username is already taken.';
            }
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } elseif (User::findByEmail($email)) {
            $errors[] = 'That email is already registered.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/register');
        }

        $id = User::create($username, $email, $password);
        $this->startSession($id);
        flash('success', 'Welcome! Your unnecessarily dramatic profile is ready.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        // Only a POST (with CSRF) may end a session, so a stray <img> or link
        // on another site cannot log people out.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(is_logged_in() ? '/dashboard' : '/');
        }
        (new \App\Middleware\CsrfMiddleware())->handle();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();

        // Start a fresh session purely to carry the goodbye message.
        session_start();
        session_regenerate_id(true);
        flash('success', 'Logged out. The internet will cope.');
        redirect('/');
    }

    /** Regenerate the id on privilege change and seed the new session. */
    private function startSession(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['_last_seen'] = time();
        unset($_SESSION['_old'], $_SESSION['csrf_token']);
        csrf_token();
    }
}
