<?php // /admin/index.php

require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/util.php';

require_login();

$u = user();
$title = 'Dashboard';
ob_start(); ?>
    <h1>Dashboard</h1>
    <div class="card">
        <p>Welcome, <strong><?= e($u['username']) ?></strong> (<?= e($u['role']) ?>).</p>
        <ul>
            <li><a class="btn" href="/public/adminc/admin/quips.php">Review Quips</a></li>
            <?php if ($u['role']==='admin'): ?>
                <li><a class="btn" href="/public/adminc/admin/users.php">Manage Users</a></li>
            <?php endif; ?>
        </ul>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';
