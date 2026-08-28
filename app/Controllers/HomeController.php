<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Profile;
use Throwable;

class HomeController
{
    public function index(): void
    {
        // A few real profiles on the landing page, so a running instance never
        // looks like an empty brochure.
        $featured = [];
        if (setting_bool('discovery_enabled', true)) {
            try {
                $featured = Profile::getDiscover('popular', 1, 3)['items'];
            } catch (Throwable) {
                $featured = [];
            }
        }

        view('pages.home', [
            'title' => site_name() . ' — Your links deserve better.',
            'description' => 'Build your own ridiculous little corner of the internet. A completely unnecessary bio-link platform.',
            'featured' => $featured,
            'stats' => $this->publicStats(),
        ]);
    }

    public function features(): void
    {
        view('pages.features', [
            'title' => 'Features — ' . site_name(),
            'description' => 'Themes, music, effects, stats, and more. Everything you need for an unnecessarily dramatic profile.',
        ]);
    }

    public function about(): void
    {
        view('pages.about', [
            'title' => 'About — ' . site_name(),
            'description' => 'Parody & independence notice. ' . site_name() . ' is an independent humor project.',
        ]);
    }

    public function contentPolicy(): void
    {
        view('pages.content-policy', [
            'title' => 'Content Policy — ' . site_name(),
            'description' => 'Content disclosure and rules for ' . site_name() . '.',
        ]);
    }

    public function privacy(): void
    {
        view('pages.privacy', [
            'title' => 'Privacy Policy — ' . site_name(),
            'description' => 'What ' . site_name() . ' stores, and what it does not.',
        ]);
    }

    public function terms(): void
    {
        view('pages.terms', [
            'title' => 'Terms of Service — ' . site_name(),
            'description' => 'The rules for using ' . site_name() . '.',
        ]);
    }

    public function contact(): void
    {
        view('pages.contact', [
            'title' => 'Contact — ' . site_name(),
            'description' => 'Questions, feedback, or reports of abuse.',
        ]);
    }

    /** Machine-readable liveness probe for Docker/uptime monitors. */
    public function health(): void
    {
        $db = 'down';
        try {
            Database::getInstance()->query('SELECT 1');
            $db = 'up';
        } catch (Throwable) {
            $db = 'down';
        }
        json_response([
            'status' => $db === 'up' ? 'ok' : 'degraded',
            'database' => $db,
            'time' => gmdate('c'),
        ], $db === 'up' ? 200 : 503);
    }

    public function sitemap(): void
    {
        $urls = ['/', '/discover', '/features', '/about', '/content-policy', '/privacy', '/terms', '/contact'];

        $profiles = [];
        if (setting_bool('discovery_enabled', true)) {
            try {
                $stmt = Database::getInstance()->query(
                    'SELECT u.username, p.updated_at FROM profiles p
                     JOIN users u ON u.id = p.user_id
                     WHERE p.is_public = 1 AND p.show_in_discover = 1 AND u.is_disabled = 0
                     ORDER BY p.profile_views DESC LIMIT 5000'
                );
                $profiles = $stmt->fetchAll();
            } catch (Throwable) {
                $profiles = [];
            }
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $path) {
            echo '  <url><loc>' . e(url($path)) . '</loc></url>' . "\n";
        }
        foreach ($profiles as $p) {
            echo '  <url><loc>' . e(url($p['username'])) . '</loc>'
                . '<lastmod>' . e(date('Y-m-d', strtotime((string)$p['updated_at']) ?: time())) . '</lastmod></url>' . "\n";
        }
        echo '</urlset>';
        exit;
    }

    private function publicStats(): array
    {
        try {
            $db = Database::getInstance();
            return [
                'profiles' => (int)$db->query('SELECT COUNT(*) FROM users WHERE is_disabled = 0')->fetchColumn(),
                'links' => (int)$db->query('SELECT COUNT(*) FROM links')->fetchColumn(),
                'views' => (int)$db->query('SELECT COALESCE(SUM(profile_views), 0) FROM profiles')->fetchColumn(),
            ];
        } catch (Throwable) {
            return ['profiles' => 0, 'links' => 0, 'views' => 0];
        }
    }
}
