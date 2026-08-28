<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Helpers/helpers.php';

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_DATABASE') ?: 'pornhub_singles';
$user = getenv('DB_USERNAME') ?: 'app';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Seed site settings
    $settings = [
        ['site_name', 'pornhub.singles'],
        ['site_description', 'A completely unnecessary bio-link website.'],
        ['registration_enabled', '1'],
        ['discovery_enabled', '1'],
        ['maintenance_mode', '0'],
        ['max_upload_size', '2097152'],
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($settings as $s) {
        $stmt->execute($s);
    }

    // Create admin if not exists
    $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@pornhub.singles';
    $adminPass = getenv('ADMIN_PASSWORD') ?: 'change-me-admin-password';
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $check->execute([$adminEmail, 'admin']);
    if (!$check->fetch()) {
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (username, email, password_hash, role, is_verified, email_verified_at) VALUES (?, ?, ?, ?, 1, NOW())')
            ->execute(['admin', $adminEmail, $hash, 'admin']);
        $adminId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO profiles (user_id, display_name, bio, theme) VALUES (?, ?, ?, ?)')
            ->execute([$adminId, 'Admin', 'Site administrator. Making the internet unnecessarily orange since forever.', 'hub']);
        echo "Admin user created (username: admin).\n";
    } else {
        echo "Admin already exists.\n";
    }

    // Demo user
    $demoCheck = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $demoCheck->execute(['marcel']);
    if (!$demoCheck->fetch()) {
        $hash = password_hash('demo1234', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (username, email, password_hash, role, is_verified, email_verified_at) VALUES (?, ?, ?, ?, 1, NOW())')
            ->execute(['marcel', 'marcel@example.com', $hash, 'user']);
        $uid = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO profiles (user_id, display_name, bio, location, theme, profile_views) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$uid, 'Marcel', 'Professional website enjoyer. Making questionable websites since forever.', 'Internet', 'hub', 12483]);
        $pid = (int)$pdo->lastInsertId();
        $links = [
            ['GitHub', 'https://github.com', 'My code lives here', 'github', '💻', 0],
            ['Discord', 'https://discord.com', 'Come hang out', 'discord', '🎮', 1],
            ['X / Twitter', 'https://x.com', 'Thoughts in 280 chars', 'x', '🐦', 2],
            ['Personal Site', 'https://example.com', 'The real deal', 'globe', '🌐', 3],
        ];
        $lstmt = $pdo->prepare('INSERT INTO links (profile_id, title, url, description, icon, emoji, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($links as $l) {
            $lstmt->execute([$pid, $l[0], $l[1], $l[2], $l[3], $l[4], $l[5]]);
        }
        echo "Demo user 'marcel' created (password: demo1234).\n";
    }

    echo "Seeding complete.\n";
} catch (PDOException $e) {
    echo "Seed error: " . $e->getMessage() . "\n";
    exit(1);
}
