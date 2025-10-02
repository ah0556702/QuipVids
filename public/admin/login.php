<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/src/db.php';
require_once BASE_PATH . '/src/auth.php';
require_once BASE_PATH . '/src/util.php';

start_session();
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /admin/index.php'); exit;
    } else {
        $err = 'Invalid credentials or inactive account.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login · QuipVid</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at top left, #0e0e1a, #050510 80%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e8e8ff;
        }
        .login-card {
            background: #16162b;
            padding: 40px;
            border-radius: 16px;
            width: 340px;
            box-shadow: 0 0 25px rgba(125,249,255,0.2);
            animation: fadeIn 0.6s ease;
        }
        .login-card h1 {
            margin: 0 0 20px;
            text-align: center;
            font-size: 1.6rem;
            color: #7df9ff;
            text-shadow: 0 0 8px #7df9ff;
        }
        .msg {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background: #2a1a25;
            color: #ff6682;
            font-size: 0.9rem;
            text-align: center;
        }
        .form-row {
            position: relative;
            margin-bottom: 20px;
        }
        .form-row input {
            width: 100%;
            padding: 14px 12px;
            background: #0e0e1a;
            border: 1px solid #2a2a50;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-row input:focus {
            border-color: #7df9ff;
            box-shadow: 0 0 10px rgba(125,249,255,0.4);
        }
        .form-row label {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            font-size: 0.9rem;
            color: #9aa;
            pointer-events: none;
            transition: all 0.2s ease;
        }
        .form-row input:focus + label,
        .form-row input:not(:placeholder-shown) + label {
            top: -6px;
            left: 8px;
            font-size: 0.75rem;
            background: #16162b;
            padding: 0 6px;
            color: #7df9ff;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7df9ff, #5d5dfc);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 0 15px rgba(125,249,255,0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(125,249,255,0.8);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="login-card">
    <h1>Admin Login</h1>
    <?php if ($err): ?><div class="msg"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-row">
            <input type="text" name="username" required placeholder=" ">
            <label>Username</label>
        </div>
        <div class="form-row">
            <input type="password" name="password" required placeholder=" ">
            <label>Password</label>
        </div>
        <button class="btn" type="submit">Sign In</button>
    </form>
</div>
</body>
</html>
