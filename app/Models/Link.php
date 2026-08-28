<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Link
{
    public static function getByProfile(int $profileId, bool $enabledOnly = true): array
    {
        $db = Database::getInstance();
        $sql = 'SELECT * FROM links WHERE profile_id = ?';
        if ($enabledOnly) {
            $sql .= ' AND is_enabled = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute([$profileId]);
        return $stmt->fetchAll();
    }

    public static function create(int $profileId, array $data): int
    {
        $db = Database::getInstance();
        $maxOrder = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM links WHERE profile_id = ?');
        $maxOrder->execute([$profileId]);
        $order = (int)$maxOrder->fetchColumn() + 1;

        $stmt = $db->prepare('INSERT INTO links (profile_id, title, url, description, icon, emoji, is_enabled, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $profileId,
            $data['title'],
            $data['url'],
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['emoji'] ?? null,
            $data['is_enabled'] ?? 1,
            $order,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, int $profileId, array $data): bool
    {
        $db = Database::getInstance();
        $allowed = ['title', 'url', 'description', 'icon', 'emoji', 'is_enabled'];
        $sets = [];
        $values = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $sets[] = "$k = ?";
                $values[] = $v;
            }
        }
        if (empty($sets)) return false;
        $values[] = $id;
        $values[] = $profileId;
        $stmt = $db->prepare('UPDATE links SET ' . implode(', ', $sets) . ' WHERE id = ? AND profile_id = ?');
        return $stmt->execute($values);
    }

    public static function delete(int $id, int $profileId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM links WHERE id = ? AND profile_id = ?');
        return $stmt->execute([$id, $profileId]);
    }

    public static function reorder(int $profileId, array $order): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE links SET sort_order = ? WHERE id = ? AND profile_id = ?');
        foreach ($order as $i => $id) {
            $stmt->execute([$i, (int)$id, $profileId]);
        }
    }

    public static function recordClick(int $linkId, string $ip): void
    {
        $db = Database::getInstance();
        $db->prepare('INSERT INTO link_clicks (link_id, clicker_ip) VALUES (?, ?)')->execute([$linkId, $ip]);
        $db->prepare('UPDATE links SET click_count = click_count + 1 WHERE id = ?')->execute([$linkId]);
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM links WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
