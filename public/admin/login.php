<?php // /admin/login.php
require_once __DIR__ . '/../../config.php';

require_once BASE_PATH . '/src/db.php';
require_once BASE_PATH . '/src/auth.php';
require_once BASE_PATH . '/src/util.php';


start_session();
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /admin/'); exit;
    } else {
        $err = 'Invalid credentials or inactive account.';
    }
}
$title = 'Login';
ob_start(); ?>
    <div class="card" style="max-width:420px;margin:40px auto">
        <h1>Login</h1>
        <?php if ($err): ?><div style="color:#ff7288;margin-bottom:10px"><?= e($err) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="form-row">
                <label>Username</label>
                <input class="input" name="username" required>
            </div>
            <div class="form-row">
                <label>Password</label>
                <input class="input" type="password" name="password" required>
            </div>
            <button class="btn" type="submit">Sign in</button>
        </form>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/_layout.php';
