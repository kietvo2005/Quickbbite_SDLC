<?php
/**
 * Global Helper Functions
 * Includes safety utilities, session flash alerts, redirects, and number format overrides.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * XSS Protection Escaping helper.
 * @param string $data
 * @return string
 */
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formats float/decimal to currency layout.
 * @param float $amount
 * @return string
 */
function format_currency($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Outputs standard hidden CSRF form input.
 * @return string
 */
function csrf_input() {
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Validates CSRF token from post against session storage.
 * @param string $token
 * @return bool
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Set flash messages inside session.
 * @param string $type ('success', 'danger', 'warning', 'info')
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Output and clear flash messages.
 */
function display_flash() {
    if (!empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $flash) {
            echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">';
            echo e($flash['message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        unset($_SESSION['flash_messages']);
    }
}

/**
 * Simple redirect helper.
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Safe string trimmer and sanitizer.
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
