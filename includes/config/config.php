<?php
/**
 * Global Configuration Settings
 * Initializes sessions, constants, base URLs, and CSRF protection tokens.
 */

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// DB connection details
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_delivery');
define('DB_USER', 'root');
define('DB_PASS', '');

// Detect Base URL dynamically to support standard deployments
$protocol = 'http://';
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $protocol = 'https://';
}
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($host, ',') !== false) {
    $host = trim(explode(',', $host)[0]);
}
$script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

// Extract path relative to root
if (strpos($script, '/admin/') !== false) {
    $path = explode('/admin/', $script)[0];
} elseif (strpos($script, '/pages/admin/') !== false) {
    $path = explode('/pages/admin/', $script)[0];
} elseif (strpos($script, '/pages/customer/') !== false) {
    $path = explode('/pages/customer/', $script)[0];
} elseif (strpos($script, '/pages/restaurant/') !== false) {
    $path = explode('/pages/restaurant/', $script)[0];
} else {
    $path = dirname($script);
}

$path = str_replace('\\', '/', $path);
if ($path === '/') {
    $path = '';
}

$baseUrl = rtrim($protocol . $host . $path, '/') . '/';
define('BASE_URL', $baseUrl);
define('SITE_NAME', 'Food Delivery System');

function asset_url($path = '') {
    $path = ltrim((string) $path, '/');
    if ($path === '') {
        return BASE_URL;
    }

    return rtrim(BASE_URL, '/') . '/' . $path;
}

function image_url($path = '', $fallback = 'assets/images/default_food.jpg') {
    $resolvedPath = !empty($path) ? $path : $fallback;

    if (!empty($resolvedPath) && strpos($resolvedPath, 'http://') !== 0 && strpos($resolvedPath, 'https://') !== 0) {
        $projectRoot = dirname(__DIR__, 2);
        $localPath = $projectRoot . '/' . ltrim($resolvedPath, '/');
        if (file_exists($localPath)) {
            return asset_url($resolvedPath);
        }
    }

    return asset_url($fallback);
}
/**
 * Load and cache the active language dictionary.
 */
function load_language_dictionary(): array {
    static $dictionary = null;
    if ($dictionary !== null) {
        return $dictionary;
    }

    $lang = $_SESSION['language'] ?? 'en';
    if (!in_array($lang, ['en', 'vi'], true)) {
        $lang = 'en';
    }

    $path = __DIR__ . '/../lang/' . $lang . '.php';
    $dictionary = file_exists($path) ? require $path : [];
    return $dictionary;
}

/**
 * Translation helper. Falls back to the key itself if no translation exists.
 */
function __($key) {
    $dictionary = load_language_dictionary();
    return $dictionary[$key] ?? $key;
}
// Generate CSRF token for security forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Production-grade error visibility flags
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
