<?php // /src/auth.php
declare(strict_types=1);

function start_session(): void {
    $cfg = require __DIR__ . '/../config.php';
    session_name($cfg['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $cfg['session_secure'],
        'httponly' => $cfg['session_httponly'],
        'samesite' => $cfg['session_samesite'],
    ]);
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    start_session();
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(419);
        exit('Invalid CSRF token');
    }
}

function login(string $username, string $password): bool {
    start_session();
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND active = 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
        $_SESSION['user'] = ['id'=>$u['id'], 'username'=>$u['username'], 'role'=>$u['role']];
        audit($u['id'], 'login', 'user', (string)$u['id']);
        return true;
    }
    return false;
}

function logout(): void {
    start_session();
    $uid = $_SESSION['user']['id'] ?? null;
    if ($uid) audit($uid, 'logout', 'user', (string)$uid);
    $_SESSION = [];
    session_destroy();
}

function user(): ?array {
    start_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!user()) {
        header('Location: /admin/login.php'); exit;
    }
}

function require_role(string ...$roles): void {
    $u = user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
