<?php
/**
 * Authentication and Access Logic
 * Manages user logins, role clearances, remember-me cookies, and redirects.
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/helpers.php';

// Restore a persistent login only from a server-side, hashed token.
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    restore_remembered_user($_COOKIE['remember_user']);
}

function normalize_user_role($role) {
    $role = strtolower(trim((string)($role ?? '')));

    if ($role === 'administrator' || $role === 'super admin' || $role === 'super_admin' || $role === 'superadministrator') {
        return 'admin';
    }

    if ($role === 'admin' || $role === 'staff') {
        return 'admin';
    }

    return 'customer';
}

function store_user_session($user) {
    $role = normalize_user_role($user['role'] ?? '');

    $_SESSION['user_id'] = (int)($user['id'] ?? 0);
    $_SESSION['user_name'] = $user['name'] ?? $user['username'] ?? '';
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['username'] = $_SESSION['user_name'];
    $_SESSION['email'] = $_SESSION['user_email'];
    $_SESSION['user_role'] = $role;
    $_SESSION['role'] = $role;
    $_SESSION['avatar'] = $user['avatar'] ?? '';
    $_SESSION['logged_in'] = true;

    return $role;
}

function is_https_request() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

function remember_cookie_options($expires) {
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function clear_remember_cookie() {
    setcookie('remember_user', '', remember_cookie_options(time() - 3600));
    unset($_COOKIE['remember_user']);
}

function forget_remember_token($cookieValue = null) {
    $cookieValue = $cookieValue ?? ($_COOKIE['remember_user'] ?? '');
    $parts = explode(':', (string)$cookieValue, 2);
    if (count($parts) === 2 && ctype_xdigit($parts[0])) {
        try {
            Database::getInstance()->execute('DELETE FROM `auth_tokens` WHERE `selector` = ?', [$parts[0]]);
        } catch (Throwable $e) {
            error_log('Unable to remove persistent login token: ' . $e->getMessage());
        }
    }
    clear_remember_cookie();
}

function create_remember_token($userId) {
    $selector = bin2hex(random_bytes(18));
    $validator = bin2hex(random_bytes(32));
    $expires = time() + (86400 * 30);

    $db = Database::getInstance();
    // Keep one active persistent login per user; issuing a new one revokes the old one.
    $db->execute('DELETE FROM `auth_tokens` WHERE `user_id` = ?', [$userId]);
    $db->execute(
        'INSERT INTO `auth_tokens` (`user_id`, `selector`, `token_hash`, `expires_at`) VALUES (?, ?, ?, FROM_UNIXTIME(?))',
        [$userId, $selector, hash('sha256', $validator), $expires]
    );
    setcookie('remember_user', $selector . ':' . $validator, remember_cookie_options($expires));
}

function restore_remembered_user($cookieValue) {
    $parts = explode(':', (string)$cookieValue, 2);
    if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
        forget_remember_token($cookieValue);
        return;
    }

    [$selector, $validator] = $parts;
    try {
        $db = Database::getInstance();
        $token = $db->queryRow(
            'SELECT t.`token_hash`, t.`expires_at`, u.* FROM `auth_tokens` t JOIN `users` u ON u.`id` = t.`user_id` WHERE t.`selector` = ? LIMIT 1',
            [$selector]
        );

        $valid = $token
            && ($token['status'] ?? '') === 'active'
            && strtotime($token['expires_at']) > time()
            && hash_equals($token['token_hash'], hash('sha256', $validator));

        if (!$valid) {
            if ($token) {
                $db->execute('DELETE FROM `auth_tokens` WHERE `selector` = ?', [$selector]);
            }
            clear_remember_cookie();
            return;
        }

        session_regenerate_id(true);
        store_user_session($token);
        create_remember_token((int)$token['id']); // Rotate after each successful cookie login.
    } catch (Throwable $e) {
        error_log('Persistent login validation failed: ' . $e->getMessage());
        clear_remember_cookie();
    }
}

/**
 * Check if a session has user details.
 * @return bool
 */
function is_logged_in() {
    return !empty($_SESSION['logged_in']) || isset($_SESSION['user_id']);
}

/**
 * Get the currently stored user role from session with backward compatibility.
 * @return string
 */
function current_user_role() {
    $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'guest';
    return normalize_user_role($role);
}

/**
 * Check if the active session is an Admin.
 * @return bool
 */
function is_admin() {
    return is_logged_in() && current_user_role() === 'admin';
}

/**
 * Check if the active session is a Customer.
 * @return bool
 */
function is_customer() {
    return is_logged_in() && current_user_role() === 'customer';
}

/**
 * Guard page from guests.
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Please log in to continue.');
        redirect(BASE_URL . 'login.php');
    }
}

/**
 * Guard page from non-admins.
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Access denied. Admin permissions required.');
        redirect(BASE_URL . 'index.php');
    }
}

/**
 * Guard page from non-customers.
 */
function require_customer() {
    require_login();
    if (!is_customer()) {
        set_flash('danger', 'Access denied. Customer page access only.');
        redirect(BASE_URL . 'index.php');
    }
}

/**
 * Log in a user.
 * @param string $email
 * @param string $password
 * @param bool $remember
 * @return bool
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }

    $db = Database::getInstance();
    return $db->queryRow("SELECT * FROM `users` WHERE `id` = ?", [$_SESSION['user_id']]);
}

function login($email, $password, $remember = false) {
    $db = Database::getInstance();
    $user = $db->queryRow(
        "SELECT * FROM `users` WHERE (LOWER(`email`) = LOWER(?) OR LOWER(`username`) = LOWER(?)) LIMIT 1",
        [$email, $email]
    );

    if (!$user) {
        set_flash('danger', 'Invalid email or password.');
        return false;
    }

    if (($user['status'] ?? 'inactive') !== 'active') {
        set_flash('warning', 'Your account has been disabled.');
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        set_flash('danger', 'Invalid email or password.');
        return false;
    }

    session_regenerate_id(true);
    store_user_session($user);

    $db->execute("UPDATE `users` SET `last_login` = NOW() WHERE `id` = ?", [$user['id']]);

    if ($remember) {
        create_remember_token((int)$user['id']);
    } elseif (isset($_COOKIE['remember_user'])) {
        forget_remember_token();
    }
    return true;
}

/**
 * Clear user session and delete cookie.
 */
function logout() {
    $_SESSION = [];
    $_SESSION['logged_in'] = false;
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    forget_remember_token();
}
