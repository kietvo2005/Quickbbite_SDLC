<?php
/**
 * AJAX endpoint for creating a restaurant
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

$name = sanitize_input($_POST['name'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$cuisineType = sanitize_input($_POST['cuisine_type'] ?? '');
$deliveryFee = (float)($_POST['delivery_fee'] ?? 0.0);

$errors = [];
if (empty($name)) $errors[] = "Restaurant name is required.";
if (empty($address)) $errors[] = "Address is required.";
if (empty($cuisineType)) $errors[] = "Cuisine type is required.";

$nameCheck = $db->queryRow("SELECT `id` FROM `restaurants` WHERE `name` = ?", [$name]);
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

    $imagePath = 'assets/images/default_restaurant.jpg';
    if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['restaurant_image']['tmp_name'];
        $fileName = $_FILES['restaurant_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
            $newFileName = 'rest_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = 'uploads/restaurants/' . $newFileName;
            }
        }
    }

    $db->execute(
        "INSERT INTO `restaurants` (`name`, `address`, `phone`, `email`, `cuisine_type`, `delivery_fee`, `logo_path`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$name, $address, $phone, $email, $cuisineType, $deliveryFee, $imagePath, 'active']
    );

    $restaurantId = (int)$db->lastInsertId();
    $restaurant = $db->queryRow("SELECT * FROM `restaurants` WHERE `id` = ?", [$restaurantId]);

    echo json_encode([
        'success' => true,
        'message' => 'Restaurant created successfully.',
        'restaurant' => $restaurant
    ]);
} catch (Throwable $e) {
    error_log('Restaurant creation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to create restaurant.']);
}
