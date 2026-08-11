<?php
/**
 * AJAX endpoint for deleting a restaurant
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
$restaurantId = (int)($_POST['restaurant_id'] ?? 0);

if ($restaurantId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid restaurant ID.']);
    exit;
}

try {
    $restaurant = $db->queryRow("SELECT `logo_path` FROM `restaurants` WHERE `id` = ?", [$restaurantId]);

    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Restaurant not found.']);
        exit;
    }

    if (!empty($restaurant['logo_path']) && strpos($restaurant['logo_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $restaurant['logo_path'])) {
        unlink(__DIR__ . '/../../' . $restaurant['logo_path']);
    }

    $db->execute("DELETE FROM `restaurants` WHERE `id` = ?", [$restaurantId]);

    echo json_encode([
        'success' => true,
        'message' => 'Restaurant deleted successfully.',
        'restaurant_id' => $restaurantId
    ]);
} catch (Throwable $e) {
    error_log('Restaurant deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete restaurant.']);
}
