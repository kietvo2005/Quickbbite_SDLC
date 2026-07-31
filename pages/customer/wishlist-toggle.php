<?php
/**
 * AJAX endpoint: toggles a food item in/out of the current customer's wishlist.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'customer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in first.']);
    exit;
}

$data = json_decode(file_get_contents('php://input') ?: '', true);
$foodId = isset($data['food_id']) ? (int) $data['food_id'] : 0;
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($foodId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid food ID.']);
    exit;
}

$db = Database::getInstance();

$existing = $db->queryRow(
    'SELECT id FROM `wishlist` WHERE user_id = ? AND food_id = ? LIMIT 1',
    [$userId, $foodId]
);

if ($existing) {
    $db->execute('DELETE FROM `wishlist` WHERE id = ?', [$existing['id']]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $db->execute(
        'INSERT INTO `wishlist` (user_id, food_id) VALUES (?, ?)',
        [$userId, $foodId]
    );
    echo json_encode(['success' => true, 'action' => 'added']);
}