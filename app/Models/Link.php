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
        $db->beginTransaction();
        try {
            foreach (array_values($order) as $i => $id) {
                if (!is_numeric($id)) {
                    continue;
                }
                $stmt->execute([$i, (int)$id, $profileId]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Swap a link with its neighbour. Backs the up/down buttons, which are the
     * keyboard-accessible equivalent of dragging.
     */
    public static function move(int $id, int $profileId, string $direction): bool
    {
        $db = Database::getInstance();
        $links = self::getByProfile($profileId, false);
        $index = null;
        foreach ($links as $i => $link) {
            if ((int)$link['id'] === $id) {
                $index = $i;
                break;
            }
        }
        if ($index === null) {
            return false;
        }
        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($links)) {
            return false;
        }
        [$links[$index], $links[$target]] = [$links[$target], $links[$index]];
        self::reorder($profileId, array_column($links, 'id'));
        return true;
    }

    /** Total clicks across a profile's links. */
    public static function totalClicks(int $profileId): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = ?');
        $stmt->execute([$profileId]);
        return (int)$stmt->fetchColumn();
    }

    public static function recordClick(int $linkId, string $ip): void
    {
        $db = Database::getInstance();
        // Ignore repeats from the same IP within a minute so a double-click or
        // a back-and-click-again does not inflate the counter.
        $recent = $db->prepare(
            'SELECT id FROM link_clicks
             WHERE link_id = ? AND clicker_ip = ? AND clicked_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) LIMIT 1'
        );
        $recent->execute([$linkId, $ip]);
        if ($recent->fetch()) {
            return;
        }
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
