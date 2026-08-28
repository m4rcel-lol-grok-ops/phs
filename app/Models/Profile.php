<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Profile
{
    public static function findByUserId(int $userId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT p.*, u.username, u.is_verified, u.created_at as user_created_at FROM profiles p JOIN users u ON u.id = p.user_id WHERE p.user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT p.*, u.username, u.is_verified, u.is_disabled, u.created_at as user_created_at FROM profiles p JOIN users u ON u.id = p.user_id WHERE u.username = ?');
        $stmt->execute([$username]);
        $profile = $stmt->fetch();
        if ($profile && $profile['is_disabled']) {
            return null;
        }
        return $profile ?: null;
    }

    public static function update(int $userId, array $data): void
    {
        $db = Database::getInstance();
        $allowed = [
            'display_name', 'bio', 'location', 'website', 'pronouns', 'avatar', 'banner',
            'theme', 'bg_type', 'bg_color', 'bg_gradient', 'bg_image', 'bg_url',
            'card_color', 'accent_color', 'text_color', 'button_color', 'font_family',
            'effects_enabled', 'effect_type', 'music_url', 'music_title', 'music_artist',
            'is_public', 'show_in_discover'
        ];
        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "$key = ?";
                $values[] = $value;
            }
        }
        if (empty($sets)) return;
        $values[] = $userId;
        $db->prepare('UPDATE profiles SET ' . implode(', ', $sets) . ' WHERE user_id = ?')->execute($values);
    }

    public static function incrementViews(int $profileId, string $ip): void
    {
        $db = Database::getInstance();
        // Simple dedup: one view per IP per hour
        $stmt = $db->prepare('SELECT id FROM profile_views WHERE profile_id = ? AND viewer_ip = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1');
        $stmt->execute([$profileId, $ip]);
        if (!$stmt->fetch()) {
            $db->prepare('INSERT INTO profile_views (profile_id, viewer_ip, user_agent) VALUES (?, ?, ?)')
                ->execute([$profileId, $ip, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512)]);
            $db->prepare('UPDATE profiles SET profile_views = profile_views + 1 WHERE id = ?')->execute([$profileId]);
        }
    }

    public static function getDiscover(string $sort = 'popular', int $page = 1, int $perPage = 12, ?string $search = null): array
    {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        $where = 'p.is_public = 1 AND p.show_in_discover = 1 AND u.is_disabled = 0';
        $params = [];
        if ($search) {
            $where .= ' AND (u.username LIKE ? OR p.display_name LIKE ? OR p.bio LIKE ?)';
            $q = '%' . $search . '%';
            $params = [$q, $q, $q];
        }
        $order = match ($sort) {
            'new' => 'u.created_at DESC',
            'random' => 'RAND()',
            default => 'p.profile_views DESC',
        };
        $countStmt = $db->prepare("SELECT COUNT(*) FROM profiles p JOIN users u ON u.id = p.user_id WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT p.*, u.username, u.is_verified FROM profiles p JOIN users u ON u.id = p.user_id WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
}
