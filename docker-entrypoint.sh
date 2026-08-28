#!/bin/bash
set -e

echo "Waiting for database..."
until php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo 'DB ready';
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    sleep 2
done

echo "Running database migrations..."
php /var/www/html/database/migrate.php

echo "Seeding admin if needed..."
php /var/www/html/database/seed.php

# Ensure upload dirs exist and are writable
mkdir -p /var/www/html/public/uploads/avatars /var/www/html/public/uploads/banners
chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage
chmod -R 775 /var/www/html/public/uploads /var/www/html/storage

exec "$@"
