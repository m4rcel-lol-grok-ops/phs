<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Theme;
use App\Models\Link;
use App\Models\Profile;
use App\Models\User;

class DashboardController
{
    private const UPLOAD_TYPES = [
        'avatar' => ['dir' => 'avatars', 'redirect' => '/dashboard/profile'],
        'banner' => ['dir' => 'banners', 'redirect' => '/dashboard/appearance'],
    ];

    public function index(): void
    {
        [$user, $profile] = $this->context();
        $links = Link::getByProfile((int)$profile['id'], false);

        view('dashboard.index', [
            'title' => 'Dashboard — ' . site_name(),
            'user' => $user,
            'profile' => $profile,
            'links' => $links,
            'totalClicks' => array_sum(array_map(static fn($l) => (int)$l['click_count'], $links)),
            'viewSeries' => Profile::viewsByDay((int)$profile['id'], 14),
        ]);
    }

    public function profile(): void
    {
        [$user, $profile] = $this->context();
        view('dashboard.profile', [
            'title' => 'Edit profile — ' . site_name(),
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateProfile(): void
    {
        [$user] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $website = trim($_POST['website'] ?? '');
        if ($website !== '' && !preg_match('#^https?://#i', $website)) {
            $website = 'https://' . $website;
        }
        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            flash('error', 'That website address does not look like a valid URL.');
            redirect('/dashboard/profile');
        }

        Profile::update((int)$user['id'], [
            'display_name' => $this->nullable($_POST['display_name'] ?? '', 64),
            'bio' => $this->nullable($_POST['bio'] ?? '', 500),
            'location' => $this->nullable($_POST['location'] ?? '', 100),
            'website' => $website !== '' ? mb_substr($website, 0, 255) : null,
            'pronouns' => $this->nullable($_POST['pronouns'] ?? '', 32),
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
            'show_in_discover' => isset($_POST['show_in_discover']) ? 1 : 0,
        ]);

        flash('success', 'Profile updated. Looking good (probably).');
        redirect('/dashboard/profile');
    }

    public function links(): void
    {
        [$user, $profile] = $this->context();
        view('dashboard.links', [
            'title' => 'Manage links — ' . site_name(),
            'user' => $user,
            'profile' => $profile,
            'links' => Link::getByProfile((int)$profile['id'], false),
        ]);
    }

    public function createLink(): void
    {
        [, $profile] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $title = trim($_POST['title'] ?? '');
        $url = $this->normalizeUrl($_POST['url'] ?? '');

        if ($title === '' || $url === null) {
            flash('error', 'A link needs a title and a valid URL.');
            redirect('/dashboard/links');
        }

        Link::create((int)$profile['id'], [
            'title' => mb_substr($title, 0, 100),
            'url' => $url,
            'description' => $this->nullable($_POST['description'] ?? '', 255),
            'emoji' => $this->nullable($_POST['emoji'] ?? '', 16),
            'icon' => $this->nullable($_POST['icon'] ?? '', 64),
        ]);

        flash('success', 'Link added. Your collection grows.');
        redirect('/dashboard/links');
    }

    public function updateLink(int $id): void
    {
        [, $profile] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $title = trim($_POST['title'] ?? '');
        $url = $this->normalizeUrl($_POST['url'] ?? '');
        if ($title === '' || $url === null) {
            flash('error', 'A link needs a title and a valid URL.');
            redirect('/dashboard/links');
        }

        // Link::update scopes by profile_id, so one user cannot edit another's.
        Link::update($id, (int)$profile['id'], [
            'title' => mb_substr($title, 0, 100),
            'url' => $url,
            'description' => $this->nullable($_POST['description'] ?? '', 255),
            'emoji' => $this->nullable($_POST['emoji'] ?? '', 16),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
        ]);

        flash('success', 'Link updated.');
        redirect('/dashboard/links');
    }

    public function deleteLink(int $id): void
    {
        [, $profile] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        Link::delete($id, (int)$profile['id']);
        flash('success', 'Link removed.');
        redirect('/dashboard/links');
    }

    /** Keyboard/no-JS alternative to dragging. */
    public function moveLink(int $id): void
    {
        [, $profile] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $direction = ($_POST['direction'] ?? '') === 'up' ? 'up' : 'down';
        Link::move($id, (int)$profile['id'], $direction);
        redirect('/dashboard/links');
    }

    public function reorderLinks(): void
    {
        [, $profile] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $order = json_decode((string)($_POST['order'] ?? '[]'), true);
        if (!is_array($order)) {
            json_response(['ok' => false, 'error' => 'Invalid order payload.'], 422);
        }
        Link::reorder((int)$profile['id'], $order);
        json_response(['ok' => true]);
    }

    public function appearance(): void
    {
        [$user, $profile] = $this->context();
        view('dashboard.appearance', [
            'title' => 'Appearance — ' . site_name(),
            'user' => $user,
            'profile' => $profile,
            'themes' => Theme::all(),
        ]);
    }

    public function updateAppearance(): void
    {
        [$user] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $theme = in_array($_POST['theme'] ?? '', Theme::keys(), true) ? $_POST['theme'] : 'hub';
        $preset = Theme::get($theme);
        $custom = isset($_POST['use_custom_colors']) ? 1 : 0;

        $musicUrl = trim($_POST['music_url'] ?? '');
        if ($musicUrl !== '' && !filter_var($musicUrl, FILTER_VALIDATE_URL)) {
            flash('error', 'The music URL is not a valid address.');
            redirect('/dashboard/appearance');
        }
        if ($musicUrl !== '' && !preg_match('#^https?://#i', $musicUrl)) {
            flash('error', 'The music URL must start with http:// or https://.');
            redirect('/dashboard/appearance');
        }

        // Reject a gradient the renderer would silently drop, so the user is
        // told rather than left wondering why nothing changed.
        $gradientInput = trim($_POST['bg_gradient'] ?? '');
        $gradient = css_gradient($gradientInput, '');
        if ($gradientInput !== '' && $gradient === '') {
            flash('error', 'That gradient contains characters we do not allow. Stick to values like: linear-gradient(135deg, #0a0a0a, #ff9900)');
            redirect('/dashboard/appearance');
        }

        $bgUrlInput = trim($_POST['bg_url'] ?? '');
        $bgUrl = css_url($bgUrlInput);
        if ($bgUrlInput !== '' && $bgUrl === null) {
            flash('error', 'The background image URL must be a plain https:// address with no spaces or quotes.');
            redirect('/dashboard/appearance');
        }

        Profile::update((int)$user['id'], [
            'theme' => $theme,
            'use_custom_colors' => $custom,
            'bg_type' => in_array($_POST['bg_type'] ?? '', ['solid', 'gradient', 'image', 'url'], true)
                ? $_POST['bg_type'] : 'solid',
            'bg_color' => css_color($_POST['bg_color'] ?? null, $preset['bg']),
            'bg_gradient' => $gradient !== '' ? $gradient : null,
            'bg_url' => $bgUrl,
            'card_color' => css_color($_POST['card_color'] ?? null, $preset['card']),
            'accent_color' => css_color($_POST['accent_color'] ?? null, $preset['accent']),
            'text_color' => css_color($_POST['text_color'] ?? null, $preset['text']),
            'button_color' => css_color($_POST['button_color'] ?? null, $preset['button']),
            'font_family' => array_key_exists($_POST['font_family'] ?? '', Theme::FONTS)
                ? $_POST['font_family'] : $preset['font'],
            'effects_enabled' => isset($_POST['effects_enabled']) ? 1 : 0,
            'effect_type' => in_array($_POST['effect_type'] ?? '', Theme::EFFECTS, true)
                ? $_POST['effect_type'] : null,
            'music_url' => $musicUrl !== '' ? mb_substr($musicUrl, 0, 512) : null,
            'music_title' => $this->nullable($_POST['music_title'] ?? '', 128),
            'music_artist' => $this->nullable($_POST['music_artist'] ?? '', 128),
        ]);

        flash('success', 'Appearance saved. Orange intensity calibrated.');
        redirect('/dashboard/appearance');
    }

    public function uploadAvatar(): void
    {
        $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->handleUpload('avatar');
    }

    public function uploadBanner(): void
    {
        $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->handleUpload('banner');
    }

    public function deleteAvatar(): void
    {
        $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->removeImage('avatar');
    }

    public function deleteBanner(): void
    {
        $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();
        $this->removeImage('banner');
    }

    public function account(): void
    {
        [$user, $profile] = $this->context();
        view('dashboard.account', [
            'title' => 'Account — ' . site_name(),
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateAccount(): void
    {
        [$user] = $this->context();
        (new \App\Middleware\CsrfMiddleware())->handle();

        $db = Database::getInstance();
        $action = $_POST['action'] ?? '';

        if ($action === 'visibility') {
            Profile::update((int)$user['id'], [
                'is_public' => isset($_POST['is_public']) ? 1 : 0,
                'show_in_discover' => isset($_POST['show_in_discover']) ? 1 : 0,
            ]);
            flash('success', 'Visibility updated.');
            redirect('/dashboard/account');
        }

        if ($action === 'password') {
            $current = (string)($_POST['current_password'] ?? '');
            $new = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['new_password_confirm'] ?? '');

            $full = User::findById((int)$user['id']);
            if (!$full || !password_verify($current, $full['password_hash'])) {
                flash('error', 'Your current password is incorrect.');
                redirect('/dashboard/account');
            }
            if (strlen($new) < 8) {
                flash('error', 'The new password must be at least 8 characters.');
                redirect('/dashboard/account');
            }
            if ($new !== $confirm) {
                flash('error', 'The new passwords do not match.');
                redirect('/dashboard/account');
            }
            if ($new === $current) {
                flash('error', 'The new password must differ from the current one.');
                redirect('/dashboard/account');
            }

            User::updatePassword((int)$user['id'], $new);
            // Changing a password should invalidate anyone else's stolen session.
            session_regenerate_id(true);
            flash('success', 'Password changed.');
            redirect('/dashboard/account');
        }

        if ($action === 'email') {
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['current_password'] ?? '');

            $full = User::findById((int)$user['id']);
            if (!$full || !password_verify($password, $full['password_hash'])) {
                flash('error', 'Enter your current password to change your email.');
                redirect('/dashboard/account');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'That is not a valid email address.');
                redirect('/dashboard/account');
            }
            $existing = User::findByEmail($email);
            if ($existing && (int)$existing['id'] !== (int)$user['id']) {
                flash('error', 'That email is already in use.');
                redirect('/dashboard/account');
            }
            $db->prepare('UPDATE users SET email = ? WHERE id = ?')->execute([$email, $user['id']]);
            flash('success', 'Email updated.');
            redirect('/dashboard/account');
        }

        if ($action === 'delete') {
            $confirm = trim($_POST['confirm_delete'] ?? '');
            $password = (string)($_POST['current_password'] ?? '');

            $full = User::findById((int)$user['id']);
            if (!$full || !password_verify($password, $full['password_hash'])) {
                flash('error', 'Enter your password to delete your account.');
                redirect('/dashboard/account');
            }
            if ($confirm !== $user['username']) {
                flash('error', 'Type your username exactly to confirm deletion.');
                redirect('/dashboard/account');
            }
            // The last admin deleting themselves would lock everyone out.
            if ($user['role'] === 'admin' && $this->adminCount() <= 1) {
                flash('error', 'You are the only administrator. Promote someone else first.');
                redirect('/dashboard/account');
            }

            $this->deleteUploadsFor((int)$user['id']);
            $db->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);

            // Destroy the old session first, then start a clean one to carry
            // the goodbye message — otherwise the flash dies with the session.
            $_SESSION = [];
            session_destroy();
            session_start();
            session_regenerate_id(true);
            flash('success', 'Account deleted. Goodbye, internet.');
            redirect('/');
        }

        redirect('/dashboard/account');
    }

    // ---------------------------------------------------------------- helpers

    /**
     * Every dashboard action needs the user and their profile row, and must
     * never run with a half-loaded session.
     *
     * @return array{0:array,1:array}
     */
    private function context(): array
    {
        $user = current_user();
        if ($user === null) {
            (new \App\Middleware\AuthMiddleware())->handle();
        }
        $profile = Profile::findOrCreateByUserId((int)$user['id'], (string)$user['username']);
        return [$user, $profile];
    }

    private function nullable(string $value, int $max): ?string
    {
        $value = trim($value);
        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        return mb_substr($url, 0, 512);
    }

    private function adminCount(): int
    {
        return (int)Database::getInstance()
            ->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_disabled = 0")
            ->fetchColumn();
    }

    private function handleUpload(string $type): void
    {
        $user = current_user();
        $config = self::UPLOAD_TYPES[$type];
        $back = $config['redirect'];

        $file = $_FILES[$type] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', $this->uploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
            redirect($back);
        }

        $max = setting_int('max_upload_size', 2097152);
        if ((int)$file['size'] > $max) {
            flash('error', 'That file is too large. Maximum is ' . round($max / 1024 / 1024, 1) . ' MB.');
            redirect($back);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
        if ($ext === null) {
            flash('error', 'Unsupported file type. Use JPG, PNG, or WebP.');
            redirect($back);
        }
        // getimagesize is a second, independent check that this really decodes
        // as an image and is not a payload wearing an image MIME type.
        if (@getimagesize($file['tmp_name']) === false) {
            flash('error', 'That file could not be read as an image.');
            redirect($back);
        }

        $dir = BASE_PATH . '/public/uploads/' . $config['dir'];
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            flash('error', 'The upload directory is not writable. Contact the site operator.');
            redirect($back);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            flash('error', 'Could not save the file.');
            redirect($back);
        }
        @chmod($dir . '/' . $filename, 0644);

        $profile = Profile::findByUserId((int)$user['id']);
        $this->unlinkUpload($config['dir'], $profile[$type] ?? null);

        Profile::update((int)$user['id'], [$type => $filename]);
        flash('success', ucfirst($type) . ' updated.');
        redirect($back);
    }

    private function removeImage(string $type): void
    {
        $user = current_user();
        $config = self::UPLOAD_TYPES[$type];
        $profile = Profile::findByUserId((int)$user['id']);
        $this->unlinkUpload($config['dir'], $profile[$type] ?? null);
        Profile::update((int)$user['id'], [$type => null]);
        flash('success', ucfirst($type) . ' removed.');
        redirect($config['redirect']);
    }

    /** Delete a stored upload, refusing anything that is not a generated name. */
    private function unlinkUpload(string $dir, ?string $filename): void
    {
        $safe = upload_filename($filename);
        if ($safe === null) {
            return;
        }
        $path = BASE_PATH . '/public/uploads/' . $dir . '/' . $safe;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function deleteUploadsFor(int $userId): void
    {
        $profile = Profile::findByUserId($userId);
        if (!$profile) {
            return;
        }
        $this->unlinkUpload('avatars', $profile['avatar'] ?? null);
        $this->unlinkUpload('banners', $profile['banner'] ?? null);
        $this->unlinkUpload('banners', $profile['bg_image'] ?? null);
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_NO_FILE => 'Choose a file first.',
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is larger than the server allows.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Try again.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'The server could not store the file.',
            default => 'Upload failed.',
        };
    }
}
