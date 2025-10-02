<?php // /admin/logout.php
require_once __DIR__ . '/../src/auth.php';
logout();
header('Location: /admin/login.php'); exit;
