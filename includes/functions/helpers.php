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

function get_food_rating($food): string {
    $base = 4.2;
    if (!empty($food['is_popular'])) {
        $base += 0.3;
    }
    if (!empty($food['is_latest'])) {
        $base += 0.1;
    }

    $seed = (int)($food['id'] ?? 0);
    $base += ($seed % 5) * 0.1;

    return number_format(min(4.9, max(4.2, $base)), 1);
}

function get_food_delivery_time($food): string {
    $base = 20;
    $category = strtolower((string)($food['category_name'] ?? $food['category'] ?? ''));

    if (strpos($category, 'pizza') !== false) {
        $base = 25;
    } elseif (strpos($category, 'dessert') !== false) {
        $base = 18;
    } elseif (strpos($category, 'drink') !== false) {
        $base = 12;
    } elseif (strpos($category, 'healthy') !== false) {
        $base = 22;
    } elseif (strpos($category, 'burger') !== false || strpos($category, 'fast') !== false) {
        $base = 24;
    } elseif (strpos($category, 'japanese') !== false || strpos($category, 'korean') !== false || strpos($category, 'chinese') !== false) {
        $base = 28;
    }

    if (!empty($food['is_popular'])) {
        $base = max(12, $base - 2);
    }

    return $base . ' min';
}

function get_restaurant_rating($restaurant): string {
    $cuisine = strtolower((string)($restaurant['cuisine_type'] ?? ''));
    $base = 4.5;

    if (strpos($cuisine, 'dessert') !== false) {
        $base = 4.7;
    } elseif (strpos($cuisine, 'pizza') !== false || strpos($cuisine, 'italian') !== false) {
        $base = 4.6;
    } elseif (strpos($cuisine, 'japanese') !== false || strpos($cuisine, 'korean') !== false) {
        $base = 4.8;
    } elseif (strpos($cuisine, 'healthy') !== false) {
        $base = 4.6;
    }

    return number_format($base, 1);
}

function get_restaurant_delivery($restaurant): string {
    $cuisine = strtolower((string)($restaurant['cuisine_type'] ?? ''));
    $base = 25;

    if (strpos($cuisine, 'dessert') !== false || strpos($cuisine, 'drink') !== false) {
        $base = 20;
    } elseif (strpos($cuisine, 'pizza') !== false || strpos($cuisine, 'italian') !== false) {
        $base = 30;
    } elseif (strpos($cuisine, 'healthy') !== false) {
        $base = 24;
    } elseif (strpos($cuisine, 'japanese') !== false || strpos($cuisine, 'korean') !== false || strpos($cuisine, 'chinese') !== false) {
        $base = 32;
    }

    return $base . '-' . ($base + 8) . ' min';
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
