<?php
/**
 * AJAX endpoint for deleting a contact message
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
$messageId = (int)($_POST['message_id'] ?? 0);

if ($messageId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid message ID.']);
    exit;
}

try {
    $message = $db->queryRow("SELECT `id` FROM `contact_messages` WHERE `id` = ?", [$messageId]);

    if (!$message) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found.']);
        exit;
    }

    $db->execute("DELETE FROM `contact_messages` WHERE `id` = ?", [$messageId]);

    echo json_encode([
        'success' => true,
        'message' => 'Message deleted successfully.',
        'message_id' => $messageId
    ]);
} catch (Throwable $e) {
    error_log('Message deletion failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete message.']);
}
