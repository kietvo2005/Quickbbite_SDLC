<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/login.php';
require 'includes/config/config.php';
require 'includes/database/Database.php';
require 'includes/functions/helpers.php';
require 'includes/functions/auth.php';

$db = Database::getInstance();
$user = $db->queryRow("SELECT id, name, username, email, password, role, status FROM users WHERE email = ? LIMIT 1", ['admin@admin.com']);
var_export($user);
echo PHP_EOL;

$ok = login('admin@admin.com', '123', false);
echo 'login_ok=' . ($ok ? '1' : '0') . PHP_EOL;
echo 'role=' . ($_SESSION['role'] ?? 'none') . PHP_EOL;
echo 'user_role=' . ($_SESSION['user_role'] ?? 'none') . PHP_EOL;
echo 'logged_in=' . (($_SESSION['logged_in'] ?? 0) ? '1' : '0') . PHP_EOL;
echo 'user_id=' . ($_SESSION['user_id'] ?? 'none') . PHP_EOL;
