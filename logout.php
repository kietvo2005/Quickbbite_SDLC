<?php
/**
 * Logout Page - Food Delivery System
 * Destroys session variables and redirects the user based on role.
 */

require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/functions/auth.php';
require_once __DIR__ . '/includes/functions/helpers.php';

$redirectTarget = is_admin() ? BASE_URL . 'login.php' : BASE_URL . 'index.php';
logout();

session_start();
set_flash('info', __('logout_success'));
redirect($redirectTarget);