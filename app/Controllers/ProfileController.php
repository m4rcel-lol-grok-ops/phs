<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Profile;
use App\Models\Link;

class ProfileController
{
    public function show(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile || !$profile['is_public']) {
            http_response_code(404);
            require BASE_PATH . '/resources/views/errors/404.php';
            return;
        }

        Profile::incrementViews((int)$profile['id'], client_ip());

        $links = Link::getByProfile((int)$profile['id']);

        view('profile.show', [
            'title' => ($profile['display_name'] ?: $profile['username']) . ' — pornhub.singles',
            'description' => $profile['bio'] ? mb_substr($profile['bio'], 0, 160) : 'Check out this profile on pornhub.singles',
            'profile' => $profile,
            'links' => $links,
            'og_image' => $profile['avatar'] ? url('uploads/avatars/' . $profile['avatar']) : null,
        ]);
    }

    public function click(int $id): void
    {
        $link = Link::find($id);
        if (!$link || !$link['is_enabled']) {
            http_response_code(404);
            exit;
        }
        Link::recordClick($id, client_ip());
        // Open redirect protection: only allow http/https
        $url = $link['url'];
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        header('Location: ' . $url, true, 302);
        exit;
    }

    public function report(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();
        $reportedUserId = (int)($_POST['user_id'] ?? 0);
        $type = $_POST['type'] ?? 'profile';
        $reason = trim($_POST['reason'] ?? '');
        $targetId = !empty($_POST['target_id']) ? (int)$_POST['target_id'] : null;

        if (!$reportedUserId || strlen($reason) < 10) {
            flash('error', 'Please provide a valid reason (min 10 characters).');
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        $allowedTypes = ['profile', 'link', 'avatar', 'banner', 'biography', 'other'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'other';
        }

        $db = \App\Core\Database::getInstance();
        $db->prepare('INSERT INTO reports (reporter_id, reported_user_id, report_type, target_id, reason) VALUES (?, ?, ?, ?, ?)')
            ->execute([
                is_logged_in() ? $_SESSION['user_id'] : null,
                $reportedUserId,
                $type,
                $targetId,
                $reason,
            ]);

        flash('success', 'Report submitted. Thank you for helping keep the internet slightly less terrible.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
