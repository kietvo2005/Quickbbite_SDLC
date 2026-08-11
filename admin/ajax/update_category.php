<?php
/**
 * AJAX endpoint for updating a category
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
$name = sanitize_input($_POST['name'] ?? '');
$description = sanitize_input($_POST['description'] ?? '');
$icon = sanitize_input($_POST['icon_class'] ?? 'bi-egg-fried');

$errors = [];
if ($categoryId <= 0) $errors[] = "Invalid category ID.";
if (empty($name)) $errors[] = "Category name is required.";

$nameCheck = $db->queryRow("SELECT `id` FROM `categories` WHERE `name` = ? AND `id` != ?", [$name, $categoryId]);
if ($nameCheck) {
    $errors[] = "A category named '" . $name . "' already exists.";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $uploadDir = __DIR__ . '/../../uploads/categories/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $existing = $db->queryRow("SELECT `image_path` FROM `categories` WHERE `id` = ?", [$categoryId]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
        exit;
    }

    $imagePath = $existing['image_path'];

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['category_image']['tmp_name'];
        $fileName = $_FILES['category_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
            $newFileName = 'cat_' . time() . '_' . rand(10, 99) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                if (!empty($existing['image_path']) && strpos($existing['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $existing['image_path'])) {
                    unlink(__DIR__ . '/../../' . $existing['image_path']);
                }
                $imagePath = 'uploads/categories/' . $newFileName;
            }
        }
    }

    $db->execute(
        "UPDATE `categories` SET `name` = ?, `description` = ?, `icon_class` = ?, `image_path` = ? WHERE `id` = ?",
        [$name, $description, $icon, $imagePath, $categoryId]
    );

    $category = $db->queryRow("SELECT * FROM `categories` WHERE `id` = ?", [$categoryId]);

    echo json_encode([
        'success' => true,
        'message' => 'Category updated successfully.',
        'category' => $category
    ]);
} catch (Throwable $e) {
    error_log('Category update failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update category.']);
}
