<?php
/**
 * AJAX endpoint for updating user status
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
$status = sanitize_input($_POST['status'] ?? '');

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit;
}

if (!in_array($status, ['active', 'inactive', 'banned'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

// Prevent self-action
if ($userId === $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot modify your own account.']);
    exit;
}

try {
    $db->execute("UPDATE `users` SET `status` = ? WHERE `id` = ?", [$status, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'User status updated to ' . $status . '.',
        'user_id' => $userId,
        'status' => $status
    ]);
} catch (Throwable $e) {
    error_log('User status update failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update user status.']);
}
