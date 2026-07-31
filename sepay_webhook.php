<?php
/**
 * SePay Webhook / IPN endpoint.
 *
 * Register this URL in the SePay dashboard:
 *   https://[your-domain]/sepay_webhook.php
 *
 * Must return HTTP 200/201 with {"success": true} within 30 seconds.
 */

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/database/Database.php';
require_once __DIR__ . '/includes/functions/sepay_functions.php';

header('Content-Type: application/json; charset=utf-8');

function sepay_webhook_response(int $status, bool $success, string $message = ''): void
{
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sepay_webhook_response(405, false, 'Method not allowed');
}

if (!verify_sepay_webhook_auth()) {
    sepay_webhook_response(401, false, 'Unauthorized');
}

$body = file_get_contents('php://input') ?: '';
$data = json_decode($body, true);

if (!is_array($data)) {
    sepay_webhook_response(400, false, 'Invalid JSON payload');
}

try {
    process_sepay_webhook($data);
    sepay_webhook_response(200, true);
} catch (Throwable $e) {
    error_log('SePay webhook error: ' . $e->getMessage());
    sepay_webhook_response(500, false, 'Internal server error');
}
