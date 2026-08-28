<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Helpers/helpers.php';

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_DATABASE') ?: 'pornhub_singles';
$user = getenv('DB_USERNAME') ?: 'app';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "Migrations completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
