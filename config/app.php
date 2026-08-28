<?php
declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'pornhub.singles'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8080'),
    'key' => env('APP_KEY', 'change-me'),
    'registration_enabled' => env('REGISTRATION_ENABLED', true),
    'discovery_enabled' => env('DISCOVERY_ENABLED', true),
    'maintenance_mode' => env('MAINTENANCE_MODE', false),
    'upload_max_size' => (int)env('UPLOAD_MAX_SIZE', 2097152),
    'allowed_image_types' => explode(',', env('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp')),
];
