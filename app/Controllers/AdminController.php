<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Settings;

class AdminController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $stats = [
            'users' => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'profiles' => (int)$db->query('SELECT COUNT(*) FROM profiles')->fetchColumn(),
            'links' => (int)$db->query('SELECT COUNT(*) FROM links')->fetchColumn(),
            'reports' => (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn(),
            'views' => (int)$db->query('SELECT COALESCE(SUM(profile_views),0) FROM profiles')->fetchColumn(),
            'clicks' => (int)$db->query('SELECT COALESCE(SUM(click_count),0) FROM links')->fetchColumn(),
            'disabled' => (int)$db->query('SELECT COUNT(*) FROM users WHERE is_disabled = 1')->fetchColumn(),
            'new_week' => (int)$db->query('SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn(),
        ];

        $recentUsers = $db->query(
            'SELECT id, username, email, role, created_at, is_disabled FROM users ORDER BY created_at DESC LIMIT 5'
        )->fetchAll();

        $recentActions = $db->query(
            'SELECT l.*, u.username AS admin_username FROM admin_logs l
             LEFT JOIN users u ON u.id = l.admin_id
             ORDER BY l.created_at DESC LIMIT 8'
        )->fetchAll();

        view('admin.index', [
            'title' => 'Admin — ' . site_name(),
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentActions' => $recentActions,
        ]);
    }

    public function users(): void
    {
        $db = Database::getInstance();
        $q = trim((string)($_GET['q'] ?? ''));
        $filter = in_array($_GET['filter'] ?? '', ['all', 'admins', 'disabled', 'verified'], true)
            ? $_GET['filter'] : 'all';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.username LIKE ? OR u.email LIKE ?)';
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $where[] = match ($filter) {
            'admins' => "u.role = 'admin'",
            'disabled' => 'u.is_disabled = 1',
            'verified' => 'u.is_verified = 1',
            default => '1=1',
        };
        $whereSql = implode(' AND ', $where);

        $count = $db->prepare("SELECT COUNT(*) FROM users u WHERE $whereSql");
        $count->execute($params);
        $total = (int)$count->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT u.*, p.profile_views, p.display_name
             FROM users u LEFT JOIN profiles p ON p.user_id = u.id
             WHERE $whereSql ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);

        view('admin.users', [
            'title' => 'Users — Admin',
            'users' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'q' => $q,
            'filter' => $filter,
        ]);
    }

    public function userAction(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        $db = Database::getInstance();
        $userId = (int)($_POST['user_id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');
        $admin = current_user();

        if ($userId <= 0) {
            flash('error', 'No user selected.');
            redirect('/admin/users');
        }

        $stmt = $db->prepare('SELECT id, username, role, is_disabled FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $target = $stmt->fetch();
        if (!$target) {
            flash('error', 'That user no longer exists.');
            redirect('/admin/users');
        }

        $isSelf = (int)$target['id'] === (int)$admin['id'];
        $targetIsAdmin = $target['role'] === 'admin';

        $log = function (string $act, ?string $details = null) use ($db, $admin, $userId): void {
            $db->prepare(
                'INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$admin['id'], $act, 'user', $userId, $details, client_ip()]);
        };

        switch ($action) {
            case 'disable':
                if ($isSelf) {
                    flash('error', 'You cannot disable your own account.');
                    break;
                }
                if ($targetIsAdmin) {
                    $this->refuseAdminTarget('disabled');
                }
                $db->prepare('UPDATE users SET is_disabled = 1 WHERE id = ?')->execute([$userId]);
                $log('disable_user', $target['username']);
                flash('success', '@' . $target['username'] . ' disabled.');
                break;

            case 'enable':
                $db->prepare('UPDATE users SET is_disabled = 0, login_attempts = 0, locked_until = NULL WHERE id = ?')
                    ->execute([$userId]);
                $log('enable_user', $target['username']);
                flash('success', '@' . $target['username'] . ' enabled.');
                break;

            case 'verify':
                $db->prepare('UPDATE users SET is_verified = 1 WHERE id = ?')->execute([$userId]);
                $log('verify_user', $target['username']);
                flash('success', '@' . $target['username'] . ' verified.');
                break;

            case 'unverify':
                $db->prepare('UPDATE users SET is_verified = 0 WHERE id = ?')->execute([$userId]);
                $log('unverify_user', $target['username']);
                flash('success', 'Verification removed from @' . $target['username'] . '.');
                break;

            case 'promote':
                $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$userId]);
                $log('promote_user', $target['username']);
                flash('success', '@' . $target['username'] . ' is now an administrator.');
                break;

            case 'demote':
                if ($isSelf) {
                    flash('error', 'You cannot remove your own administrator role.');
                    break;
                }
                if ($this->adminCount() <= 1) {
                    flash('error', 'There must always be at least one administrator.');
                    break;
                }
                $db->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$userId]);
                $log('demote_user', $target['username']);
                flash('success', '@' . $target['username'] . ' is no longer an administrator.');
                break;

            case 'delete':
                if ($isSelf) {
                    flash('error', 'Delete your own account from the account page.');
                    break;
                }
                if ($targetIsAdmin) {
                    $this->refuseAdminTarget('deleted');
                }
                $this->deleteUploadsFor($userId);
                $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
                $log('delete_user', $target['username']);
                flash('success', '@' . $target['username'] . ' deleted.');
                break;

            case 'reset_password':
                if ($targetIsAdmin && !$isSelf) {
                    $this->refuseAdminTarget('reset');
                }
                $newPass = bin2hex(random_bytes(8));
                $db->prepare('UPDATE users SET password_hash = ?, login_attempts = 0, locked_until = NULL WHERE id = ?')
                    ->execute([password_hash($newPass, PASSWORD_DEFAULT), $userId]);
                $log('reset_password', $target['username']);
                flash('success', 'Temporary password for @' . $target['username'] . ': ' . $newPass);
                break;

            case 'unlock':
                $db->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?')->execute([$userId]);
                $log('unlock_user', $target['username']);
                flash('success', '@' . $target['username'] . ' unlocked.');
                break;

            default:
                flash('error', 'Unknown action.');
        }

        redirect('/admin/users');
    }

    public function reports(): void
    {
        $db = Database::getInstance();
        $status = in_array($_GET['status'] ?? '', ['pending', 'reviewed', 'dismissed', 'actioned'], true)
            ? $_GET['status'] : 'pending';

        $stmt = $db->prepare(
            'SELECT r.*, u.username AS reported_username, ru.username AS reporter_username,
                    au.username AS reviewer_username
             FROM reports r
             JOIN users u ON u.id = r.reported_user_id
             LEFT JOIN users ru ON ru.id = r.reporter_id
             LEFT JOIN users au ON au.id = r.reviewed_by
             WHERE r.status = ? ORDER BY r.created_at DESC LIMIT 100'
        );
        $stmt->execute([$status]);

        $counts = [];
        foreach ($db->query('SELECT status, COUNT(*) AS n FROM reports GROUP BY status')->fetchAll() as $row) {
            $counts[$row['status']] = (int)$row['n'];
        }

        view('admin.reports', [
            'title' => 'Reports — Admin',
            'reports' => $stmt->fetchAll(),
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function reportAction(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        $db = Database::getInstance();
        $reportId = (int)($_POST['report_id'] ?? 0);
        $action = (string)($_POST['action'] ?? '');
        $admin = current_user();
        $notes = mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 2000);

        $stmt = $db->prepare('SELECT * FROM reports WHERE id = ?');
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();
        if (!$report) {
            flash('error', 'That report no longer exists.');
            redirect('/admin/reports');
        }

        $status = match ($action) {
            'dismiss' => 'dismissed',
            'action' => 'actioned',
            'review' => 'reviewed',
            default => null,
        };
        if ($status === null) {
            flash('error', 'Unknown action.');
            redirect('/admin/reports');
        }

        $db->prepare('UPDATE reports SET status = ?, reviewed_by = ?, admin_notes = ? WHERE id = ?')
            ->execute([$status, $admin['id'], $notes !== '' ? $notes : null, $reportId]);

        if ($status === 'actioned' && !empty($_POST['disable_user'])) {
            // Never let a report disable an administrator.
            $db->prepare("UPDATE users SET is_disabled = 1 WHERE id = ? AND role != 'admin'")
                ->execute([$report['reported_user_id']]);
            $db->prepare(
                'INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$admin['id'], 'disable_user', 'user', $report['reported_user_id'], 'via report #' . $reportId, client_ip()]);
        }

        flash('success', 'Report marked as ' . $status . '.');
        redirect('/admin/reports?status=' . urlencode((string)$report['status']));
    }

    public function settings(): void
    {
        view('admin.settings', [
            'title' => 'Site settings — Admin',
            'settings' => [
                'site_name' => setting('site_name'),
                'site_description' => setting('site_description'),
                'registration_enabled' => setting_bool('registration_enabled', true),
                'discovery_enabled' => setting_bool('discovery_enabled', true),
                'maintenance_mode' => setting_bool('maintenance_mode', false),
                'max_upload_size' => setting_int('max_upload_size', 2097152),
            ],
        ]);
    }

    public function updateSettings(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        $siteName = mb_substr(trim((string)($_POST['site_name'] ?? '')), 0, 64);
        if ($siteName === '') {
            $siteName = 'pornhub.singles';
        }

        // Cap uploads at whatever PHP itself will accept; a larger number here
        // would just produce confusing failures at upload time.
        $phpMax = min(
            $this->iniBytes(ini_get('upload_max_filesize') ?: '2M'),
            $this->iniBytes(ini_get('post_max_size') ?: '8M')
        );
        $uploadSize = (int)($_POST['max_upload_size'] ?? 2097152);
        $uploadSize = max(65536, min($uploadSize, $phpMax));

        Settings::set('site_name', $siteName);
        Settings::set('site_description', mb_substr(trim((string)($_POST['site_description'] ?? '')), 0, 255));
        Settings::set('registration_enabled', isset($_POST['registration_enabled']) ? '1' : '0');
        Settings::set('discovery_enabled', isset($_POST['discovery_enabled']) ? '1' : '0');
        Settings::set('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
        Settings::set('max_upload_size', (string)$uploadSize);

        Database::getInstance()->prepare(
            'INSERT INTO admin_logs (admin_id, action, target_type, details, ip_address) VALUES (?, ?, ?, ?, ?)'
        )->execute([current_user()['id'], 'update_settings', 'settings', null, client_ip()]);

        flash('success', 'Settings saved and applied immediately.');
        redirect('/admin/settings');
    }

    // ---------------------------------------------------------------- helpers

    /**
     * Guard rail: administrator accounts are not editable from the user list,
     * so one admin cannot lock another one out.
     */
    private function refuseAdminTarget(string $verb): never
    {
        flash('error', "Administrator accounts cannot be $verb here. Remove the administrator role first.");
        redirect('/admin/users');
    }

    private function adminCount(): int
    {
        return (int)Database::getInstance()
            ->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_disabled = 0")
            ->fetchColumn();
    }

    private function deleteUploadsFor(int $userId): void
    {
        $stmt = Database::getInstance()->prepare('SELECT avatar, banner, bg_image FROM profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        if (!$profile) {
            return;
        }
        $files = [
            'avatars' => [$profile['avatar'] ?? null],
            'banners' => [$profile['banner'] ?? null, $profile['bg_image'] ?? null],
        ];
        foreach ($files as $dir => $names) {
            foreach ($names as $name) {
                $safe = upload_filename($name);
                if ($safe === null) {
                    continue;
                }
                $path = BASE_PATH . '/public/uploads/' . $dir . '/' . $safe;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    /** Convert a php.ini shorthand size ("8M", "512K") to bytes. */
    private function iniBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int)$value;
        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
