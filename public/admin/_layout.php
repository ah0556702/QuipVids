<?php // /admin/_layout.php

require_once __DIR__ . '/../../config.php';

require_once BASE_PATH . '/src/auth.php';
require_once BASE_PATH . '/src/util.php';
$u = user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= e($title ?? 'Admin') ?> · QuipVid</title>
    <link rel="stylesheet" href="/public/adminc/admin/admin.css">
</head>
<body>
<header>
    <div class="brand">QuipVid Admin</div>
    <nav>
        <a href="/public/adminc/admin">Dashboard</a>
        <a href="/public/adminc/admin/quips.php">Moderation</a>
        <?php if ($u && $u['role']==='admin'): ?>
            <a href="/public/adminc/admin/users.php">Users</a>
        <?php endif; ?>
        <?php if ($u): ?>
            <span class="user">👤 <?= e($u['username']) ?> (<?= e($u['role']) ?>)</span>
            <a href="/public/adminc/admin/logout.php" class="danger">Logout</a>
        <?php endif; ?>
    </nav>
</header>
<main>
    <?= $content ?? '' ?>
</main>
</body>
</html>
