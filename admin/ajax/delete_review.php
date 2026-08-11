<?php
/**
 * AJAX endpoint for deleting a review
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
$reviewId = (int)($_POST['review_id'] ?? 0);

if ($reviewId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid review ID.']);
    exit;
}

try {
    $review = $db->queryRow("SELECT `id` FROM `reviews` WHERE `id` = ?", [$reviewId]);

    if (!$review) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Review not found.']);
        exit;
    }

    $db->execute("DELETE FROM `reviews` WHERE `id` = ?", [$reviewId]);

    echo json_encode([
        'success' => true,
        'message' => 'Review deleted successfully.',
        'review_id' => $reviewId
    ]);
} catch (Throwable $e) {
    error_log('Review deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete review.']);
}
