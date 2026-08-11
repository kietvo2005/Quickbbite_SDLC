<?php
/**
 * AJAX endpoint for creating a food item
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
$description = sanitize_input($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0.0);
$restaurantId = (int)($_POST['restaurant_id'] ?? 0);
$categoryId = (int)($_POST['category_id'] ?? 0);
$isPopular = isset($_POST['is_popular']) ? 1 : 0;
$isLatest = isset($_POST['is_latest']) ? 1 : 0;
$isAvailable = isset($_POST['is_available']) ? 1 : 0;

$errors = [];
if (empty($name)) $errors[] = "Food name is required.";
if ($price <= 0) $errors[] = "Price must be greater than 0.";
if ($restaurantId <= 0) $errors[] = "Select a restaurant.";
if ($categoryId <= 0) $errors[] = "Select a category.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $uploadDir = __DIR__ . '/../../uploads/foods/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePath = 'assets/images/default_food.jpg';
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['food_image']['tmp_name'];
        $fileName = $_FILES['food_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
            $newFileName = 'food_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = 'uploads/foods/' . $newFileName;
            }
        }
    }

    $db->execute(
        "INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$restaurantId, $categoryId, $name, $description, $price, $imagePath, $isPopular, $isLatest, $isAvailable]
    );

    $foodId = (int)$db->lastInsertId();
    $food = $db->queryRow(
        "SELECT f.*, r.`name` AS `restaurant_name`, c.`name` AS `category_name` FROM `foods` f JOIN `restaurants` r ON f.`restaurant_id` = r.`id` JOIN `categories` c ON f.`category_id` = c.`id` WHERE f.`id` = ?",
        [$foodId]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Food item added successfully.',
        'food' => $food
    ]);
} catch (Throwable $e) {
    error_log('Food creation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to create food item.']);
}
