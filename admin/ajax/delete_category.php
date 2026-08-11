<?php
/**
 * AJAX endpoint for deleting a category
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
$categoryId = (int)($_POST['category_id'] ?? 0);

if ($categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category ID.']);
    exit;
}

try {
    // Check for linked foods
    $foodCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `foods` WHERE `category_id` = ?", [$categoryId])['cnt'];
    if ($foodCount > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete: ' . $foodCount . ' food items linked to this category.']);
        exit;
    }

    $category = $db->queryRow("SELECT `image_path` FROM `categories` WHERE `id` = ?", [$categoryId]);

    if (!$category) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
        exit;
    }

    if (!empty($category['image_path']) && strpos($category['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $category['image_path'])) {
        unlink(__DIR__ . '/../../' . $category['image_path']);
    }

    $db->execute("DELETE FROM `categories` WHERE `id` = ?", [$categoryId]);

    echo json_encode([
        'success' => true,
        'message' => 'Category deleted successfully.',
        'category_id' => $categoryId
    ]);
} catch (Throwable $e) {
    error_log('Category deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete category.']);
}
