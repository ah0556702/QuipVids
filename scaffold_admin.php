<?php
$structure = [
    'config.php' => '',
    'resources/data/.gitkeep' => '',
    'sql/schema.sql' => '',
    'src/db.php' => '',
    'src/auth.php' => '',
    'src/util.php' => '',
    'admin/_layout.php' => '',
    'admin/admin.css' => '',
    'admin/login.php' => '',
    'admin/logout.php' => '',
    'admin/index.php' => '',
    'admin/users.php' => '',
    'admin/quips.php' => '',
    'tools/seed_admin.php' => '',
    'storage/.gitkeep' => '',
];

foreach ($structure as $path => $content) {
    $full = __DIR__ . '/' . $path;
    $dir = dirname($full);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created dir: $dir\n";
    }
    if (!file_exists($full)) {
        file_put_contents($full, $content);
        echo "Created file: $full\n";
    } else {
        echo "Skipped (exists): $full\n";
    }
}
