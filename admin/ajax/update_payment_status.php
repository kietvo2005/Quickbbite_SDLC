<?php
/**
 * AJAX endpoint for updating an order payment status without reloading the page.
 * Returns JSON for the admin dashboard status dropdown workflow.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Security verification failed.'
    ]);
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$paymentStatus = sanitize_input($_POST['payment_status'] ?? '');

if ($orderId <= 0 || !in_array($paymentStatus, ['pending', 'paid', 'failed'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request payload.'
    ]);
    exit;
}

try {
    $db = Database::getInstance();

    $db->execute(
        "UPDATE `orders` SET `payment_status` = ? WHERE `id` = ?",
        [$paymentStatus, $orderId]
    );

    $db->execute(
        "UPDATE `payments` SET `payment_status` = ? WHERE `order_id` = ?",
        [$paymentStatus, $orderId]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Payment status updated.'
    ]);
} catch (Throwable $e) {
    error_log('Failed to update payment status: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to update payment status.'
    ]);
}
