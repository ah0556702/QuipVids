<?php // /config.php
declare(strict_types=1);

// Always points to project root
define('BASE_PATH', __DIR__);


return [
    'base_url' => '/', // adjust if site lives in subdir
    'db_path'  => __DIR__ . '/storage/app.sqlite',      // ensure /storage is writable
    'session_name' => 'quipvid_session',
    'session_secure' => false, // set true when using HTTPS
    'session_httponly' => true,
    'session_samesite' => 'Lax',
];
