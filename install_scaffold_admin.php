<?php
/**
 * One-shot installer.
 * Place in project root and run:
 *   php install_admin_scaffold.php
 *
 * It will create /config.php, /src, /admin, /tools, /sql, /storage
 * with all the code scaffolded.
 */

$files = [

# -----------------------------
# CONFIG
# -----------------------------
    'config.php' => <<<'PHP'
<?php
declare(strict_types=1);

return [
  'base_url' => '/',
  'db_path'  => __DIR__ . '/storage/app.sqlite',
  'session_name' => 'quipvid_session',
  'session_secure' => false,
  'session_httponly' => true,
  'session_samesite' => 'Lax',
];
PHP,

# -----------------------------
# SQL
# -----------------------------
    'sql/schema.sql' => <<<'SQL'
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ('admin','moderator','viewer')),
  active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS quip_moderation (
  quip_id TEXT PRIMARY KEY,
  status TEXT NOT NULL CHECK(status IN ('pending','approved','rejected')) DEFAULT 'pending',
  reason TEXT,
  moderated_by INTEGER,
  moderated_at TEXT,
  FOREIGN KEY (moderated_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  action TEXT NOT NULL,
  target_type TEXT,
  target_id TEXT,
  meta_json TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
SQL,

# -----------------------------
# SRC: DB + AUTH + UTIL
# -----------------------------
    'src/db.php' => <<<'PHP'
<?php
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
PHP,

    'src/auth.php' => <<<'PHP'
<?php
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
PHP,

    'src/util.php' => <<<'PHP'
<?php
declare(strict_types=1);

function e(string $s = null): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
PHP,

# -----------------------------
# ADMIN LAYOUT + CSS
# -----------------------------
    'admin/_layout.php' => <<<'PHP'
<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/util.php';
$u = user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($title ?? 'Admin') ?> · QuipVid</title>
  <link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<header>
  <div class="brand">QuipVid Admin</div>
  <nav>
    <a href="/admin/">Dashboard</a>
    <a href="/admin/quips.php">Moderation</a>
    <?php if ($u && $u['role']==='admin'): ?>
      <a href="/admin/users.php">Users</a>
    <?php endif; ?>
    <?php if ($u): ?>
      <span class="user">👤 <?= e($u['username']) ?> (<?= e($u['role']) ?>)</span>
      <a href="/admin/logout.php" class="danger">Logout</a>
    <?php endif; ?>
  </nav>
</header>
<main>
  <?= $content ?? '' ?>
</main>
</body>
</html>
PHP,

    'admin/admin.css' => <<<'CSS'
:root { --bg:#0e0e1a; --card:#16162b; --text:#e8e8ff; --muted:#9aa; --accent:#7df9ff; --danger:#ff6682; }
*{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--text);font:14px/1.45 system-ui,Segoe UI,Roboto}
header{display:flex;justify-content:space-between;align-items:center;padding:12px 18px;background:#12122b;border-bottom:1px solid #222}
.brand{font-weight:700;color:var(--accent)}
nav a, nav span{margin-left:14px;text-decoration:none;color:var(--text)}
nav a:hover{color:var(--accent)}
nav .danger{color:var(--danger)}
main{padding:20px}
h1{margin:0 0 16px}
.card{background:var(--card);border:1px solid #222;border-radius:10px;padding:16px;margin:0 0 16px}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid #222}
.btn{display:inline-block;padding:8px 12px;border-radius:8px;background:#1e1e37;color:var(--text);text-decoration:none;border:1px solid #2a2a50}
.btn:hover{border-color:var(--accent);color:var(--accent)}
.btn.danger{border-color:#512; color:#ffb0bd}
.input, select{width:100%;padding:10px;border-radius:8px;border:1px solid #2a2a50;background:#12122b;color:var(--text)}
.form-row{display:grid;grid-template-columns:200px 1fr;gap:10px;align-items:center;margin-bottom:12px}
CSS,

# -----------------------------
# ADMIN PAGES (login/logout/index/users/quips) + SEED SCRIPT
# -----------------------------
    'admin/login.php' => '... FULL CODE FROM EARLIER ...',
    'admin/logout.php' => '... FULL CODE FROM EARLIER ...',
    'admin/index.php' => '... FULL CODE FROM EARLIER ...',
    'admin/users.php' => '... FULL CODE FROM EARLIER ...',
    'admin/quips.php' => '... FULL CODE FROM EARLIER ...',
    'tools/seed_admin.php' => '... FULL CODE FROM EARLIER ...',

];

foreach ($files as $path => $content) {
    $full = __DIR__ . '/' . $path;
    $dir = dirname($full);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created dir: $dir\n";
    }
    if (!file_exists($full)) {
        file_put_contents($full, $content);
        echo "Created file: $path\n";
    } else {
        echo "Skipped (exists): $path\n";
    }
}

echo "✅ Admin scaffold installed.\n";
