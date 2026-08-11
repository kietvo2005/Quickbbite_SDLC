<?php
/**
 * Food Menu Item CRUD Manager - QuickBite Admin Panel
 * Adds, edits, lists, and deletes food menu options across restaurants.
 */

$pageTitle = "Manage Foods";
require_once __DIR__ . '/../../includes/header.php';

// Guards
require_admin();

$db = Database::getInstance();

// Setup upload folder dynamically
$uploadDir = __DIR__ . '/../../uploads/foods/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Track edit states
$editMode = false;
$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editData = $db->queryRow("SELECT * FROM `foods` WHERE `id` = ?", [$editId]);
    if ($editData) {
        $editMode = true;
    }
}

// 1. Handle CRUD - Create & Update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $action = $_POST['action'];
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0.0);
    $restaurantId = (int)($_POST['restaurant_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    
    // Flags
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isLatest = isset($_POST['is_latest']) ? 1 : 0;
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    
    $errors = [];
    if (empty($name)) $errors[] = "Food name is required.";
    if ($price <= 0) $errors[] = "Please provide a valid price (greater than 0).";
    if ($restaurantId <= 0) $errors[] = "Please select a valid restaurant partner.";
    if ($categoryId <= 0) $errors[] = "Please select a food category.";
    
    // Cover Image file upload logic
    $imagePath = '';
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['food_image']['tmp_name'];
        $fileName = $_FILES['food_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'food_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = 'uploads/foods/' . $newFileName;
            }
        } else {
            $errors[] = "Only JPG, JPEG, and PNG formats are allowed for food pictures.";
        }
    }
    
    if (empty($errors)) {
        if ($action === 'add') {
            $sql = "INSERT INTO `foods` (`restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->execute($sql, [$restaurantId, $categoryId, $name, $description, $price, $imagePath ?: 'assets/images/default_food.jpg', $isPopular, $isLatest, $isAvailable]);
            set_flash('success', 'New food item added to menu.');
        } elseif ($action === 'edit' && isset($_POST['food_id'])) {
            $foodId = (int)$_POST['food_id'];
            
            // Retain path if no new file uploaded
            if (empty($imagePath)) {
                $existing = $db->queryRow("SELECT `image_path` FROM `foods` WHERE `id` = ?", [$foodId]);
                $imagePath = $existing['image_path'] ?? 'assets/images/default_food.jpg';
            } else {
                // Delete old image file
                $old = $db->queryRow("SELECT `image_path` FROM `foods` WHERE `id` = ?", [$foodId]);
                if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
                    unlink(__DIR__ . '/../../' . $old['image_path']);
                }
            }
            
            $sql = "UPDATE `foods` SET `restaurant_id` = ?, `category_id` = ?, `name` = ?, `description` = ?, `price` = ?, `image_path` = ?, `is_popular` = ?, `is_latest` = ?, `is_available` = ? WHERE `id` = ?";
            $db->execute($sql, [$restaurantId, $categoryId, $name, $description, $price, $imagePath, $isPopular, $isLatest, $isAvailable, $foodId]);
            set_flash('success', 'Food item details updated successfully.');
        }
        redirect(BASE_URL . 'pages/admin/foods.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// 2. Handle CRUD - Delete (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    
    // Delete file first
    $old = $db->queryRow("SELECT `image_path` FROM `foods` WHERE `id` = ?", [$targetId]);
    if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
        unlink(__DIR__ . '/../../' . $old['image_path']);
    }
    
    $db->execute("DELETE FROM `foods` WHERE `id` = ?", [$targetId]);
    set_flash('info', 'Food item removed from menu.');
    redirect(BASE_URL . 'pages/admin/foods.php');
}

// Fetch helper list: restaurants and categories for selector dropdowns
$restaurantsDropdown = $db->queryAll("SELECT `id`, `name` FROM `restaurants` WHERE `status` = 'active'");
$categoriesDropdown = $db->queryAll("SELECT `id`, `name` FROM `categories` ORDER BY `name` ASC");

// Fetch all foods list
$foods = $db->queryAll("SELECT f.*, r.`name` AS `restaurant_name`, c.`name` AS `category_name`
                        FROM `foods` f
                        JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
                        JOIN `categories` c ON f.`category_id` = c.`id`
                        ORDER BY f.`id` DESC");
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Foods Manager area -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-egg-fried text-danger me-2"></i>Manage Menu Foods</h3>
                <?php if ($editMode): ?>
                    <a href="foods.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add New</a>
                <?php endif; ?>
            </div>
            
            <!-- Add / Edit Form Box -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h5 class="fw-bold mb-3"><?php echo $editMode ? 'Edit Menu Dish details' : 'Add New Dish to Restaurant Menus'; ?></h5>
                
                <form action="foods.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="food_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Dish Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $editMode ? e($editData['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label fw-semibold small">Unit Price ($ USD) *</label>
                            <input type="number" step="0.01" min="0.05" name="price" id="price" class="form-control" value="<?php echo $editMode ? e($editData['price']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="restaurant_id" class="form-label fw-semibold small">Restaurant Provider *</label>
                            <select name="restaurant_id" id="restaurant_id" class="form-select" required>
                                <option value="" disabled selected>Select restaurant...</option>
                                <?php foreach ($restaurantsDropdown as $rest): ?>
                                    <option value="<?php echo $rest['id']; ?>" <?php echo ($editMode && (int)$editData['restaurant_id'] === (int)$rest['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($rest['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-semibold small">Food Category *</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="" disabled selected>Select category...</option>
                                <?php foreach ($categoriesDropdown as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($editMode && (int)$editData['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small">Ingredients and Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control"><?php echo $editMode ? e($editData['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="food_image" class="form-label fw-semibold small">Dish Cover Image</label>
                        <input type="file" name="food_image" id="food_image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        <?php if ($editMode && !empty($editData['image_path'])): ?>
                            <div class="form-text mt-1 text-muted">Current file: <code><?php echo e(basename($editData['image_path'])); ?></code></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Flags Grid -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_popular" id="is_popular" class="form-check-input" value="1" <?php echo ($editMode && $editData['is_popular']) ? 'checked' : ''; ?>>
                                <label for="is_popular" class="form-check-label fw-semibold small">Set Popular Item</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_latest" id="is_latest" class="form-check-input" value="1" <?php echo (!$editMode || $editData['is_latest']) ? 'checked' : ''; ?>>
                                <label for="is_latest" class="form-check-label fw-semibold small">Set New Item Tag</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_available" id="is_available" class="form-check-input" value="1" <?php echo (!$editMode || $editData['is_available']) ? 'checked' : ''; ?>>
                                <label for="is_available" class="form-check-label fw-semibold small">Available (Toggle In-Stock)</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand px-5 py-2"><?php echo $editMode ? 'Save Details' : 'Add Dish to Menus'; ?></button>
                </form>
            </div>
            
            <!-- List Menu items Table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Dishes Registry</h5>
                
                <?php if (empty($foods)): ?>
                    <p class="text-muted small">No food menu items have been added yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">Image</th>
                                    <th scope="col">Dish</th>
                                    <th scope="col">Restaurant / Cat</th>
                                    <th scope="col" class="text-end">Price</th>
                                    <th scope="col">Tags</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foods as $f): ?>
                                    <tr>
                                        <td>
                                            <div class="rounded overflow-hidden" style="width: 50px; height: 50px;">
                                                <?php 
                                                $path = image_url($f['image_path'] ?? '', 'assets/images/default_food.jpg');
                                                ?>
                                                <img src="<?php echo $path; ?>" alt="Dish" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark d-block small text-truncate" style="max-width: 140px;"><?php echo e($f['name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $f['id']; ?></span>
                                        </td>
                                        <td class="small">
                                            <span class="d-block text-dark fw-semibold"><?php echo e($f['restaurant_name']); ?></span>
                                            <span class="badge bg-light text-dark text-xs"><?php echo e($f['category_name']); ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-danger small"><?php echo format_currency($f['price']); ?></td>
                                        <td>
                                            <?php if ($f['is_popular']): ?>
                                                <span class="badge bg-warning text-dark text-xxs px-1">Popular</span>
                                            <?php endif; ?>
                                            <?php if ($f['is_latest']): ?>
                                                <span class="badge bg-success text-xxs px-1">New</span>
                                            <?php endif; ?>
                                            <?php if (!$f['is_available']): ?>
                                                <span class="badge bg-secondary text-xxs px-1">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="foods.php?edit_id=<?php echo $f['id']; ?>" class="btn btn-sm btn-light"><i class="bi bi-pencil-square"></i></a>
                                            <a href="foods.php?action=delete&id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Remove food item from all menu tables?');">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
