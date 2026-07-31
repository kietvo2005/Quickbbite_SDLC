<?php
/**
 * JSON endpoint for polling bank-transfer payment status.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'customer') {
    http_response_code(401);
    echo json_encode(['paid' => false, 'error' => 'Unauthorized']);
    exit;
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['paid' => false, 'error' => 'Invalid order ID']);
    exit;
}

$db = Database::getInstance();
$order = $db->queryRow(
    'SELECT `payment_status`, `payment_method` FROM `orders` WHERE `id` = ? AND `user_id` = ? LIMIT 1',
    [$orderId, $userId]
);

if (!$order) {
    http_response_code(404);
    echo json_encode(['paid' => false, 'error' => 'Order not found']);
    exit;
}

echo json_encode([
    'paid' => $order['payment_method'] === 'bank_transfer' && $order['payment_status'] === 'paid',
]);
