<?php
/**
 * AJAX endpoint for deleting a user
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
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit;
}

// Prevent self-deletion
if ($userId === $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
    exit;
}

try {
    $user = $db->queryRow("SELECT `id` FROM `users` WHERE `id` = ?", [$userId]);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $db->execute("DELETE FROM `users` WHERE `id` = ?", [$userId]);

    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully.',
        'user_id' => $userId
    ]);
} catch (Throwable $e) {
    error_log('User deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete user.']);
}
