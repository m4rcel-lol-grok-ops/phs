<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Theme;
use App\Models\Link;
use App\Models\Profile;

class ProfileController
{
    public function show(string $username): void
    {
        $profile = Profile::findByUsername($username);
        if (!$profile) {
            $this->notFound();
            return;
        }

        $viewer = current_user();
        $isOwner = $viewer !== null && (int)$viewer['id'] === (int)$profile['user_id'];

        // Owners (and admins) can still open a private profile — otherwise
        // toggling "public" off makes your own page look deleted.
        if (!$profile['is_public'] && !$isOwner && !is_admin()) {
            $this->notFound();
            return;
        }

        // Don't let people inflate their own view counter.
        if (!$isOwner) {
            Profile::incrementViews((int)$profile['id'], client_ip());
            $profile['profile_views'] = (int)$profile['profile_views'] + 1;
        }

        $links = Link::getByProfile((int)$profile['id']);
        $displayName = $profile['display_name'] ?: $profile['username'];

        view('profile.show', [
            'title' => $displayName . ' (@' . $profile['username'] . ') — ' . site_name(),
            'description' => $profile['bio']
                ? mb_substr((string)$profile['bio'], 0, 160)
                : 'See ' . $displayName . '\'s links on ' . site_name() . '.',
            'profile' => $profile,
            'links' => $links,
            'theme' => Theme::resolve($profile),
            'isOwner' => $isOwner,
            'og_image' => upload_filename($profile['avatar'] ?? null)
                ? url('uploads/avatars/' . $profile['avatar'])
                : null,
        ]);
    }

    public function click(int $id): void
    {
        $link = Link::find($id);
        if (!$link || !$link['is_enabled']) {
            $this->notFound();
            return;
        }

        $url = (string)$link['url'];
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        // Never redirect to anything that is not a well-formed http(s) URL.
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            $this->notFound();
            return;
        }

        Link::recordClick($id, client_ip());

        header('Referrer-Policy: no-referrer');
        header('Cache-Control: no-store');
        header('Location: ' . $url, true, 302);
        exit;
    }

    public function report(): void
    {
        (new \App\Middleware\CsrfMiddleware())->handle();

        if (!rate_limit('report_' . client_ip(), 10, 3600)) {
            flash('error', 'You have submitted a lot of reports. Try again later.');
            redirect_back('/');
        }

        $reportedUserId = (int)($_POST['user_id'] ?? 0);
        $type = $_POST['type'] ?? 'profile';
        $reason = trim($_POST['reason'] ?? '');
        $targetId = !empty($_POST['target_id']) ? (int)$_POST['target_id'] : null;

        if ($reportedUserId <= 0 || mb_strlen($reason) < 10) {
            flash('error', 'Please describe the problem in at least 10 characters.');
            redirect_back('/');
        }

        $db = Database::getInstance();

        // Reject reports against users that do not exist.
        $exists = $db->prepare('SELECT id FROM users WHERE id = ?');
        $exists->execute([$reportedUserId]);
        if (!$exists->fetch()) {
            flash('error', 'That profile no longer exists.');
            redirect_back('/');
        }

        $allowedTypes = ['profile', 'link', 'avatar', 'banner', 'biography', 'other'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'other';
        }

        $reporterId = is_logged_in() ? (int)$_SESSION['user_id'] : null;

        // Collapse duplicate pending reports from the same person.
        if ($reporterId !== null) {
            $dupe = $db->prepare(
                'SELECT id FROM reports
                 WHERE reporter_id = ? AND reported_user_id = ? AND status = \'pending\' LIMIT 1'
            );
            $dupe->execute([$reporterId, $reportedUserId]);
            if ($dupe->fetch()) {
                flash('success', 'You have already reported this profile. Our moderators are on it.');
                redirect_back('/');
            }
        }

        $db->prepare(
            'INSERT INTO reports (reporter_id, reported_user_id, report_type, target_id, reason)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$reporterId, $reportedUserId, $type, $targetId, mb_substr($reason, 0, 2000)]);

        flash('success', 'Report submitted. Thanks for helping keep the internet slightly less terrible.');
        redirect_back('/');
    }

    private function notFound(): void
    {
        http_response_code(404);
        require BASE_PATH . '/resources/views/errors/404.php';
    }
}
