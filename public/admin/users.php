<?php // /admin/users.php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/util.php';
require_login(); require_role('admin');

$action = $_POST['action'] ?? null;
$msg = null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    if ($action === 'create') {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role     = (string)($_POST['role'] ?? 'viewer');
        if ($username && $password && in_array($role,['admin','moderator','viewer'],true)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db()->prepare('INSERT INTO users(username,password_hash,role) VALUES(?,?,?)')
                ->execute([$username,$hash,$role]);
            audit(user()['id'] ?? null, 'user.create', 'user', $username);
            $msg = "User created.";
        } else $msg = "Missing/invalid fields.";
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE users SET active = CASE active WHEN 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
        audit(user()['id'] ?? null, 'user.toggle', 'user', (string)$id);
        $msg = "User toggled.";
    }

    if ($action === 'resetpw') {
        $id = (int)($_POST['id'] ?? 0);
        $pw = (string)($_POST['newpw'] ?? '');
        if ($pw) {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash,$id]);
            audit(user()['id'] ?? null, 'user.resetpw', 'user', (string)$id);
            $msg = "Password updated.";
        } else $msg = "Provide a new password.";
    }
}

$users = db()->query('SELECT id,username,role,active,created_at FROM users ORDER BY id DESC')->fetchAll();

$title='Users';
ob_start(); ?>
    <h1>Users</h1>
<?php if ($msg): ?><div class="card"><?= e($msg) ?></div><?php endif; ?>

    <div class="card">
        <h3>Create User</h3>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <div class="form-row"><label>Username</label><input class="input" name="username" required></div>
            <div class="form-row"><label>Password</label><input class="input" type="password" name="password" required></div>
            <div class="form-row">
                <label>Role</label>
                <select name="role" class="input">
                    <option value="viewer">viewer</option>
                    <option value="moderator">moderator</option>
                    <option value="admin">admin</option>
                </select>
            </div>
            <button class="btn" type="submit">Create</button>
        </form>
    </div>

    <div class="card">
        <h3>All Users</h3>
        <table>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Active</th><th>Created</th><th>Actions</th></tr>
            <?php foreach($users as $row): ?>
                <tr>
                    <td><?= e((string)$row['id']) ?></td>
                    <td><?= e($row['username']) ?></td>
                    <td><?= e($row['role']) ?></td>
                    <td><?= $row['active'] ? '✅' : '⛔' ?></td>
                    <td><?= e($row['created_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn" type="submit"><?= $row['active']?'Deactivate':'Activate' ?></button>
                        </form>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="resetpw">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <input class="input" style="width:160px" name="newpw" placeholder="new password">
                            <button class="btn" type="submit">Set PW</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';
