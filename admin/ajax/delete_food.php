<?php
/**
 * AJAX endpoint for deleting a food item
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security verification failed.']);
    exit;
}

$db = Database::getInstance();
$foodId = (int)($_POST['food_id'] ?? 0);

if ($foodId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid food ID.']);
    exit;
}

try {
    $food = $db->queryRow("SELECT `image_path` FROM `foods` WHERE `id` = ?", [$foodId]);

    if (!$food) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Food item not found.']);
        exit;
    }

    if (!empty($food['image_path']) && strpos($food['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $food['image_path'])) {
        unlink(__DIR__ . '/../../' . $food['image_path']);
    }

    $db->execute("DELETE FROM `foods` WHERE `id` = ?", [$foodId]);

    echo json_encode([
        'success' => true,
        'message' => 'Food item deleted successfully.',
        'food_id' => $foodId
    ]);
} catch (Throwable $e) {
    error_log('Food deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete food item.']);
}
