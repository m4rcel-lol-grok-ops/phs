<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\User;
use App\Models\Profile;

class AdminController
{
    public function index(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        $db = Database::getInstance();
        $stats = [
            'users' => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'profiles' => (int)$db->query('SELECT COUNT(*) FROM profiles')->fetchColumn(),
            'links' => (int)$db->query('SELECT COUNT(*) FROM links')->fetchColumn(),
            'reports' => (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
            'views' => (int)$db->query('SELECT COALESCE(SUM(profile_views),0) FROM profiles')->fetchColumn(),
        ];
        view('admin.index', [
            'title' => 'Admin — pornhub.singles',
            'stats' => $stats,
        ]);
    }

    public function users(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        $db = Database::getInstance();
        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $where = '1=1';
        $params = [];
        if ($q) {
            $where .= ' AND (u.username LIKE ? OR u.email LIKE ?)';
            $params = ["%$q%", "%$q%"];
        }
        $count = $db->prepare("SELECT COUNT(*) FROM users u WHERE $where");
        $count->execute($params);
        $total = (int)$count->fetchColumn();
        $stmt = $db->prepare("SELECT u.*, p.profile_views, p.display_name FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE $where ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        view('admin.users', [
            'title' => 'Users — Admin',
            'users' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'q' => $q,
        ]);
    }

    public function userAction(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $db = Database::getInstance();
        $userId = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $admin = current_user();

        if (!$userId) {
            redirect('/admin/users');
        }

        $log = function (string $act, ?string $details = null) use ($db, $admin, $userId) {
            $db->prepare('INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$admin['id'], $act, 'user', $userId, $details, client_ip()]);
        };

        match ($action) {
            'disable' => (function () use ($db, $userId, $log) {
                $db->prepare('UPDATE users SET is_disabled = 1 WHERE id = ? AND role != "admin"')->execute([$userId]);
                $log('disable_user');
                flash('success', 'User disabled.');
            })(),
            'enable' => (function () use ($db, $userId, $log) {
                $db->prepare('UPDATE users SET is_disabled = 0 WHERE id = ?')->execute([$userId]);
                $log('enable_user');
                flash('success', 'User enabled.');
            })(),
            'verify' => (function () use ($db, $userId, $log) {
                $db->prepare('UPDATE users SET is_verified = 1 WHERE id = ?')->execute([$userId]);
                $log('verify_user');
                flash('success', 'User verified.');
            })(),
            'unverify' => (function () use ($db, $userId, $log) {
                $db->prepare('UPDATE users SET is_verified = 0 WHERE id = ?')->execute([$userId]);
                $log('unverify_user');
                flash('success', 'Verification removed.');
            })(),
            'delete' => (function () use ($db, $userId, $log, $admin) {
                if ((int)$userId === (int)$admin['id']) {
                    flash('error', 'Cannot delete yourself.');
                    return;
                }
                $db->prepare('DELETE FROM users WHERE id = ? AND role != "admin"')->execute([$userId]);
                $log('delete_user');
                flash('success', 'User deleted.');
            })(),
            'reset_password' => (function () use ($db, $userId, $log) {
                $newPass = bin2hex(random_bytes(8));
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
                $log('reset_password');
                flash('success', "Password reset. Temporary password: $newPass");
            })(),
            default => flash('error', 'Unknown action.'),
        };
        redirect('/admin/users');
    }

    public function reports(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        $db = Database::getInstance();
        $status = $_GET['status'] ?? 'pending';
        $stmt = $db->prepare("SELECT r.*, u.username as reported_username, ru.username as reporter_username FROM reports r JOIN users u ON u.id = r.reported_user_id LEFT JOIN users ru ON ru.id = r.reporter_id WHERE r.status = ? ORDER BY r.created_at DESC LIMIT 50");
        $stmt->execute([$status]);
        view('admin.reports', [
            'title' => 'Reports — Admin',
            'reports' => $stmt->fetchAll(),
            'status' => $status,
        ]);
    }

    public function reportAction(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $db = Database::getInstance();
        $reportId = (int)($_POST['report_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $admin = current_user();
        $notes = trim($_POST['notes'] ?? '');

        $report = $db->prepare('SELECT * FROM reports WHERE id = ?');
        $report->execute([$reportId]);
        $r = $report->fetch();
        if (!$r) {
            redirect('/admin/reports');
        }

        if ($action === 'dismiss') {
            $db->prepare('UPDATE reports SET status = ?, reviewed_by = ?, admin_notes = ? WHERE id = ?')
                ->execute(['dismissed', $admin['id'], $notes, $reportId]);
            flash('success', 'Report dismissed.');
        } elseif ($action === 'action') {
            $db->prepare('UPDATE reports SET status = ?, reviewed_by = ?, admin_notes = ? WHERE id = ?')
                ->execute(['actioned', $admin['id'], $notes, $reportId]);
            // Optionally disable user
            if (!empty($_POST['disable_user'])) {
                $db->prepare('UPDATE users SET is_disabled = 1 WHERE id = ? AND role != "admin"')->execute([$r['reported_user_id']]);
            }
            flash('success', 'Report actioned.');
        }
        redirect('/admin/reports');
    }

    public function settings(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        $db = Database::getInstance();
        $stmt = $db->query('SELECT setting_key, setting_value FROM site_settings');
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        view('admin.settings', [
            'title' => 'Settings — Admin',
            'settings' => $settings,
        ]);
    }

    public function updateSettings(): void
    {
        (new \App\Middleware\AdminMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $db = Database::getInstance();
        $keys = ['site_name', 'site_description', 'registration_enabled', 'discovery_enabled', 'maintenance_mode', 'max_upload_size'];
        $stmt = $db->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($keys as $key) {
            $val = $_POST[$key] ?? '';
            if (in_array($key, ['registration_enabled', 'discovery_enabled', 'maintenance_mode'], true)) {
                $val = isset($_POST[$key]) ? '1' : '0';
            }
            $stmt->execute([$key, $val]);
        }
        flash('success', 'Settings saved.');
        redirect('/admin/settings');
    }
}
