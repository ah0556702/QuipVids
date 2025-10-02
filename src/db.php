<?php // /src/db.php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $cfg = require __DIR__ . '/../config.php';
    $pdo = new PDO('sqlite:' . $cfg['db_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    return $pdo;
}

function audit(int $userId = null, string $action = '', string $targetType = null, string $targetId = null, array $meta = []): void {
    $stmt = db()->prepare('INSERT INTO audit_log(user_id, action, target_type, target_id, meta_json) VALUES(?,?,?,?,?)');
    $stmt->execute([$userId, $action, $targetType, $targetId, json_encode($meta)]);
}
