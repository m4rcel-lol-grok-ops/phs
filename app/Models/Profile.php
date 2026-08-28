<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Profile
{
    private const UPDATABLE = [
        'display_name', 'bio', 'location', 'website', 'pronouns', 'avatar', 'banner',
        'theme', 'bg_type', 'bg_color', 'bg_gradient', 'bg_image', 'bg_url',
        'card_color', 'accent_color', 'text_color', 'button_color', 'font_family',
        'use_custom_colors', 'effects_enabled', 'effect_type',
        'music_url', 'music_title', 'music_artist',
        'is_public', 'show_in_discover',
    ];

    public static function findByUserId(int $userId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT p.*, u.username, u.is_verified, u.role, u.created_at AS user_created_at
             FROM profiles p JOIN users u ON u.id = p.user_id WHERE p.user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * A profile row exists for every user, but a database restored from an old
     * backup (or a failed signup) can be missing one. Create it on demand so
     * the dashboard never fatals on a null profile.
     */
    public static function findOrCreateByUserId(int $userId, string $username): array
    {
        $profile = self::findByUserId($userId);
        if ($profile !== null) {
            return $profile;
        }
        Database::getInstance()
            ->prepare('INSERT INTO profiles (user_id, display_name) VALUES (?, ?)')
            ->execute([$userId, $username]);
        return self::findByUserId($userId) ?? [];
    }

    public static function findByUsername(string $username): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT p.*, u.username, u.is_verified, u.is_disabled, u.created_at AS user_created_at
             FROM profiles p JOIN users u ON u.id = p.user_id WHERE u.username = ?'
        );
        $stmt->execute([$username]);
        $profile = $stmt->fetch();
        if (!$profile || $profile['is_disabled']) {
            return null;
        }
        return $profile;
    }

    public static function update(int $userId, array $data): void
    {
        $db = Database::getInstance();
        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, self::UPDATABLE, true)) {
                $sets[] = "`$key` = ?";
                $values[] = $value;
            }
        }
        if (!$sets) {
            return;
        }
        $values[] = $userId;
        $db->prepare('UPDATE profiles SET ' . implode(', ', $sets) . ' WHERE user_id = ?')->execute($values);
    }

    public static function incrementViews(int $profileId, string $ip): void
    {
        $db = Database::getInstance();
        // One counted view per IP per hour.
        $stmt = $db->prepare(
            'SELECT id FROM profile_views
             WHERE profile_id = ? AND viewer_ip = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1'
        );
        $stmt->execute([$profileId, $ip]);
        if ($stmt->fetch()) {
            return;
        }
        $db->prepare('INSERT INTO profile_views (profile_id, viewer_ip, user_agent) VALUES (?, ?, ?)')
            ->execute([$profileId, $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512)]);
        $db->prepare('UPDATE profiles SET profile_views = profile_views + 1 WHERE id = ?')->execute([$profileId]);
    }

    /** Views per day for the last N days, for the dashboard sparkline. */
    public static function viewsByDay(int $profileId, int $days = 14): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT DATE(viewed_at) AS day, COUNT(*) AS total
             FROM profile_views
             WHERE profile_id = ? AND viewed_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(viewed_at)'
        );
        $stmt->execute([$profileId, $days - 1]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[(string)$row['day']] = (int)$row['total'];
        }
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i day"));
            $series[] = ['day' => $day, 'total' => $rows[$day] ?? 0];
        }
        return $series;
    }

    public static function getDiscover(string $sort = 'popular', int $page = 1, int $perPage = 12, ?string $search = null): array
    {
        $db = Database::getInstance();
        $perPage = max(1, min(48, $perPage));
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = 'p.is_public = 1 AND p.show_in_discover = 1 AND u.is_disabled = 0';
        $params = [];
        if ($search !== null && $search !== '') {
            $where .= ' AND (u.username LIKE ? OR p.display_name LIKE ? OR p.bio LIKE ?)';
            // Escape LIKE wildcards so a search for "100%" is a literal search.
            $q = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
            $params = [$q, $q, $q];
        }
        $order = match ($sort) {
            'new' => 'u.created_at DESC, p.id DESC',
            'random' => 'RAND()',
            default => 'p.profile_views DESC, p.id DESC',
        };

        $countStmt = $db->prepare("SELECT COUNT(*) FROM profiles p JOIN users u ON u.id = p.user_id WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // LIMIT/OFFSET are ints derived above, never raw input.
        $sql = "SELECT p.*, u.username, u.is_verified
                FROM profiles p JOIN users u ON u.id = p.user_id
                WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int)ceil($total / $perPage)),
        ];
    }
}
