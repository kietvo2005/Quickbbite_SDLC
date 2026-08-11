<?php
/**
 * AJAX endpoint for updating a restaurant
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
$name = sanitize_input($_POST['name'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$cuisineType = sanitize_input($_POST['cuisine_type'] ?? '');
$deliveryFee = (float)($_POST['delivery_fee'] ?? 0.0);

$errors = [];
if ($restaurantId <= 0) $errors[] = "Invalid restaurant ID.";
if (empty($name)) $errors[] = "Restaurant name is required.";
if (empty($address)) $errors[] = "Address is required.";
if (empty($cuisineType)) $errors[] = "Cuisine type is required.";

$nameCheck = $db->queryRow("SELECT `id` FROM `restaurants` WHERE `name` = ? AND `id` != ?", [$name, $restaurantId]);
if ($nameCheck) {
    $errors[] = "A restaurant named '" . $name . "' already exists.";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $uploadDir = __DIR__ . '/../../uploads/restaurants/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $existing = $db->queryRow("SELECT `logo_path` FROM `restaurants` WHERE `id` = ?", [$restaurantId]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Restaurant not found.']);
        exit;
    }

    $imagePath = $existing['logo_path'];

    if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['restaurant_image']['tmp_name'];
        $fileName = $_FILES['restaurant_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
            $newFileName = 'rest_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                if (!empty($existing['logo_path']) && strpos($existing['logo_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $existing['logo_path'])) {
                    unlink(__DIR__ . '/../../' . $existing['logo_path']);
                }
                $imagePath = 'uploads/restaurants/' . $newFileName;
            }
        }
    }

    $db->execute(
        "UPDATE `restaurants` SET `name` = ?, `address` = ?, `phone` = ?, `email` = ?, `cuisine_type` = ?, `delivery_fee` = ?, `logo_path` = ? WHERE `id` = ?",
        [$name, $address, $phone, $email, $cuisineType, $deliveryFee, $imagePath, $restaurantId]
    );

    $restaurant = $db->queryRow("SELECT * FROM `restaurants` WHERE `id` = ?", [$restaurantId]);

    echo json_encode([
        'success' => true,
        'message' => 'Restaurant updated successfully.',
        'restaurant' => $restaurant
    ]);
} catch (Throwable $e) {
    error_log('Restaurant update failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update restaurant.']);
}
