<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Helpers/helpers.php';

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_DATABASE') ?: 'pornhub_singles';
$user = getenv('DB_USERNAME') ?: 'app';
$pass = getenv('DB_PASSWORD') ?: '';

/**
 * Add a column only when it is missing, so migrate.php stays safe to run on
 * every container start (it is wired into the entrypoint).
 */
function add_column(PDO $pdo, string $database, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$database, $table, $column]);
    if ((int)$stmt->fetchColumn() > 0) {
        return;
    }
    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    echo "  + $table.$column\n";
}

function add_index(PDO $pdo, string $database, string $table, string $index, string $definition): void
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $stmt->execute([$database, $table, $index]);
    if ((int)$stmt->fetchColumn() > 0) {
        return;
    }
    $pdo->exec("ALTER TABLE `$table` ADD INDEX `$index` $definition");
    echo "  + index $table.$index\n";
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // Base schema (all statements are IF NOT EXISTS).
    $pdo->exec((string)file_get_contents(__DIR__ . '/schema.sql'));

    // Incremental changes for databases created before these columns existed.
    add_column($pdo, $db, 'profiles', 'use_custom_colors', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER font_family');
    add_index($pdo, $db, 'link_clicks', 'idx_link_date', '(link_id, clicked_at)');

    echo "Migrations completed successfully.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Migration error: ' . $e->getMessage() . "\n");
    exit(1);
}
