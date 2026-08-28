<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Single lookup across both identifier columns, for the login form. */
    public static function findByUsernameOrEmail(string $identifier): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $username, string $email, string $password): int
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        // A user without a profile row breaks every dashboard page, so both
        // inserts succeed together or not at all.
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, verification_token) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hash, $token]);
            $id = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO profiles (user_id, display_name) VALUES (?, ?)')->execute([$id, $username]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
        return $id;
    }

    public static function updatePassword(int $id, string $password): void
    {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?')
            ->execute([$hash, $id]);
    }

    public static function incrementLoginAttempts(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare('UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?')->execute([$id]);
    }

    public static function resetLoginAttempts(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')->execute([$id]);
    }

    public static function lockAccount(int $id, int $minutes = 15): void
    {
        $db = Database::getInstance();
        $db->prepare('UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?')->execute([$minutes, $id]);
    }
}
