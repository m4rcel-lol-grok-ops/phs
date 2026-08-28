<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Profile;
use App\Models\Link;
use App\Models\User;
use App\Core\Database;

class DashboardController
{
    public function index(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        $links = Link::getByProfile((int)$profile['id'], false);
        $totalClicks = array_sum(array_column($links, 'click_count'));

        view('dashboard.index', [
            'title' => 'Dashboard — pornhub.singles',
            'user' => $user,
            'profile' => $profile,
            'links' => $links,
            'totalClicks' => $totalClicks,
        ]);
    }

    public function profile(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        view('dashboard.profile', [
            'title' => 'Edit Profile — pornhub.singles',
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateProfile(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $data = [
            'display_name' => trim($_POST['display_name'] ?? '') ?: null,
            'bio' => trim($_POST['bio'] ?? '') ?: null,
            'location' => trim($_POST['location'] ?? '') ?: null,
            'website' => trim($_POST['website'] ?? '') ?: null,
            'pronouns' => trim($_POST['pronouns'] ?? '') ?: null,
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
            'show_in_discover' => isset($_POST['show_in_discover']) ? 1 : 0,
        ];
        // Sanitize bio length
        if ($data['bio'] && mb_strlen($data['bio']) > 500) {
            $data['bio'] = mb_substr($data['bio'], 0, 500);
        }
        Profile::update((int)$user['id'], $data);
        flash('success', 'Profile updated. Looking good (probably).');
        redirect('/dashboard/profile');
    }

    public function links(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        $links = Link::getByProfile((int)$profile['id'], false);
        view('dashboard.links', [
            'title' => 'Manage Links — pornhub.singles',
            'user' => $user,
            'profile' => $profile,
            'links' => $links,
        ]);
    }

    public function createLink(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        if (!$title || !$url) {
            flash('error', 'Title and URL are required.');
            redirect('/dashboard/links');
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        Link::create((int)$profile['id'], [
            'title' => mb_substr($title, 0, 100),
            'url' => mb_substr($url, 0, 512),
            'description' => mb_substr(trim($_POST['description'] ?? ''), 0, 255) ?: null,
            'emoji' => mb_substr(trim($_POST['emoji'] ?? ''), 0, 16) ?: null,
            'icon' => trim($_POST['icon'] ?? '') ?: null,
        ]);
        flash('success', 'Link added. Your collection grows.');
        redirect('/dashboard/links');
    }

    public function updateLink(int $id): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        $data = [
            'title' => mb_substr(trim($_POST['title'] ?? ''), 0, 100),
            'url' => mb_substr(trim($_POST['url'] ?? ''), 0, 512),
            'description' => mb_substr(trim($_POST['description'] ?? ''), 0, 255) ?: null,
            'emoji' => mb_substr(trim($_POST['emoji'] ?? ''), 0, 16) ?: null,
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
        ];
        if (!preg_match('#^https?://#i', $data['url'])) {
            $data['url'] = 'https://' . $data['url'];
        }
        Link::update($id, (int)$profile['id'], $data);
        flash('success', 'Link updated.');
        redirect('/dashboard/links');
    }

    public function deleteLink(int $id): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        Link::delete($id, (int)$profile['id']);
        flash('success', 'Link removed.');
        redirect('/dashboard/links');
    }

    public function reorderLinks(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        $order = json_decode($_POST['order'] ?? '[]', true);
        if (is_array($order)) {
            Link::reorder((int)$profile['id'], $order);
        }
        json_response(['ok' => true]);
    }

    public function appearance(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        $user = current_user();
        $profile = Profile::findByUserId((int)$user['id']);
        view('dashboard.appearance', [
            'title' => 'Appearance — pornhub.singles',
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateAppearance(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $themes = ['hub', 'midnight', 'terminal', 'corporate', 'degenerate', 'minimal'];
        $theme = $_POST['theme'] ?? 'hub';
        if (!in_array($theme, $themes, true)) $theme = 'hub';

        $hex = fn($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', $v ?? '') ? $v : $d;

        $data = [
            'theme' => $theme,
            'bg_type' => in_array($_POST['bg_type'] ?? '', ['solid','gradient','image','url'], true) ? $_POST['bg_type'] : 'solid',
            'bg_color' => $hex($_POST['bg_color'] ?? null, '#0a0a0a'),
            'bg_gradient' => trim($_POST['bg_gradient'] ?? '') ?: null,
            'bg_url' => filter_var($_POST['bg_url'] ?? '', FILTER_VALIDATE_URL) ? $_POST['bg_url'] : null,
            'card_color' => $hex($_POST['card_color'] ?? null, '#1a1a1a'),
            'accent_color' => $hex($_POST['accent_color'] ?? null, '#ff9900'),
            'text_color' => $hex($_POST['text_color'] ?? null, '#ffffff'),
            'button_color' => $hex($_POST['button_color'] ?? null, '#ff9900'),
            'font_family' => in_array($_POST['font_family'] ?? '', ['system','mono','serif','rounded'], true) ? $_POST['font_family'] : 'system',
            'effects_enabled' => isset($_POST['effects_enabled']) ? 1 : 0,
            'effect_type' => in_array($_POST['effect_type'] ?? '', ['particles','gradient','glow','snow','crt','scanlines'], true) ? $_POST['effect_type'] : null,
            'music_url' => filter_var($_POST['music_url'] ?? '', FILTER_VALIDATE_URL) ? $_POST['music_url'] : null,
            'music_title' => mb_substr(trim($_POST['music_title'] ?? ''), 0, 128) ?: null,
            'music_artist' => mb_substr(trim($_POST['music_artist'] ?? ''), 0, 128) ?: null,
        ];
        Profile::update((int)$user['id'], $data);
        flash('success', 'Appearance saved. Orange intensity calibrated.');
        redirect('/dashboard/appearance');
    }

    public function uploadAvatar(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->handleUpload('avatar');
    }

    public function uploadBanner(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->handleUpload('banner');
    }

    private function handleUpload(string $type): void
    {
        $user = current_user();
        $field = $type === 'avatar' ? 'avatar' : 'banner';
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Upload failed.');
            redirect('/dashboard/' . ($type === 'avatar' ? 'profile' : 'appearance'));
        }
        $file = $_FILES[$field];
        $max = (int)env('UPLOAD_MAX_SIZE', 2097152);
        if ($file['size'] > $max) {
            flash('error', 'File too large. Max ' . round($max / 1024 / 1024, 1) . 'MB.');
            redirect('/dashboard/profile');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            flash('error', 'Invalid file type. Use JPG, PNG, or WebP.');
            redirect('/dashboard/profile');
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $dir = BASE_PATH . '/public/uploads/' . ($type === 'avatar' ? 'avatars' : 'banners');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            flash('error', 'Failed to save file.');
            redirect('/dashboard/profile');
        }
        // Delete old
        $profile = Profile::findByUserId((int)$user['id']);
        $old = $profile[$type] ?? null;
        if ($old && file_exists($dir . '/' . $old)) {
            @unlink($dir . '/' . $old);
        }
        Profile::update((int)$user['id'], [$type => $filename]);
        flash('success', ucfirst($type) . ' updated.');
        redirect('/dashboard/' . ($type === 'avatar' ? 'profile' : 'appearance'));
    }

    public function account(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        $user = current_user();
        view('dashboard.account', [
            'title' => 'Account — pornhub.singles',
            'user' => $user,
        ]);
    }

    public function updateAccount(): void
    {
        (new \App\Middleware\AuthMiddleware())->handle();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $user = current_user();
        $db = Database::getInstance();
        $action = $_POST['action'] ?? '';

        if ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['new_password_confirm'] ?? '';
            $full = User::findById((int)$user['id']);
            if (!password_verify($current, $full['password_hash'])) {
                flash('error', 'Current password is incorrect.');
                redirect('/dashboard/account');
            }
            if (strlen($new) < 8 || $new !== $confirm) {
                flash('error', 'New password must be 8+ characters and match.');
                redirect('/dashboard/account');
            }
            User::updatePassword((int)$user['id'], $new);
            flash('success', 'Password changed.');
            redirect('/dashboard/account');
        }

        if ($action === 'email') {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email.');
                redirect('/dashboard/account');
            }
            $existing = User::findByEmail($email);
            if ($existing && (int)$existing['id'] !== (int)$user['id']) {
                flash('error', 'Email already in use.');
                redirect('/dashboard/account');
            }
            $db->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $user['id']]);
            flash('success', 'Email updated.');
            redirect('/dashboard/account');
        }

        if ($action === 'delete') {
            $confirm = $_POST['confirm_delete'] ?? '';
            if ($confirm !== $user['username']) {
                flash('error', 'Please type your username to confirm deletion.');
                redirect('/dashboard/account');
            }
            $db->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);
            $_SESSION = [];
            session_destroy();
            flash('success', 'Account deleted. Goodbye, internet.');
            redirect('/');
        }

        redirect('/dashboard/account');
    }
}
