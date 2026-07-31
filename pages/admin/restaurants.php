<?php
/**
 * Restaurant Outlet CRUD Manager - Food Delivery System Admin Panel
 * Adds, edits, lists, and removes restaurant partner accounts.
 */

$pageTitle = "Manage Restaurants";
require_once __DIR__ . '/../../includes/header.php';

// Guards
require_admin();

$db = Database::getInstance();

// Setup upload folder dynamically
$uploadDir = __DIR__ . '/../../uploads/restaurants/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Track if we are editing a restaurant
$editMode = false;
$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editData = $db->queryRow("SELECT * FROM `restaurants` WHERE `id` = ?", [$editId]);
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
    $cuisine = sanitize_input($_POST['cuisine_type'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');
    
    $errors = [];
    if (empty($name)) $errors[] = "Restaurant name is required.";
    if (empty($address)) $errors[] = "Restaurant address is required.";
    
    // File upload logic
    $imagePath = '';
    if (isset($_FILES['restaurant_image']) && $_FILES['restaurant_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['restaurant_image']['tmp_name'];
        $fileName = $_FILES['restaurant_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'rest_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = 'uploads/restaurants/' . $newFileName;
            }
        } else {
            $errors[] = "Only JPG, JPEG, and PNG formats are allowed for restaurant cover images.";
        }
    }
    
    if (empty($errors)) {
        if ($action === 'add') {
            // Insert
            $sql = "INSERT INTO `restaurants` (`name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $db->execute($sql, [$name, $description, $cuisine, $address, $phone, $imagePath ?: 'assets/images/default_restaurant.jpg', $status]);
            set_flash('success', 'New restaurant added successfully.');
        } elseif ($action === 'edit' && isset($_POST['restaurant_id'])) {
            // Update
            $restaurantId = (int)$_POST['restaurant_id'];
            
            // Retain previous image path if no new file is uploaded
            if (empty($imagePath)) {
                $existing = $db->queryRow("SELECT `image_path` FROM `restaurants` WHERE `id` = ?", [$restaurantId]);
                $imagePath = $existing['image_path'] ?? 'assets/images/default_restaurant.jpg';
            } else {
                // Delete old image file if possible
                $old = $db->queryRow("SELECT `image_path` FROM `restaurants` WHERE `id` = ?", [$restaurantId]);
                if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
                    unlink(__DIR__ . '/../../' . $old['image_path']);
                }
            }
            
            $sql = "UPDATE `restaurants` SET `name` = ?, `description` = ?, `cuisine_type` = ?, `address` = ?, `phone` = ?, `image_path` = ?, `status` = ? WHERE `id` = ?";
            $db->execute($sql, [$name, $description, $cuisine, $address, $phone, $imagePath, $status, $restaurantId]);
            set_flash('success', 'Restaurant details updated successfully.');
        }
        redirect(BASE_URL . 'pages/admin/restaurants.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// 2. Handle CRUD - Delete (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    
    // Delete image file first
    $old = $db->queryRow("SELECT `image_path` FROM `restaurants` WHERE `id` = ?", [$targetId]);
    if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
        unlink(__DIR__ . '/../../' . $old['image_path']);
    }
    
    $db->execute("DELETE FROM `restaurants` WHERE `id` = ?", [$targetId]);
    set_flash('info', 'Restaurant partner deleted.');
    redirect(BASE_URL . 'pages/admin/restaurants.php');
}

// Fetch all restaurants list
$restaurants = $db->queryAll("SELECT * FROM `restaurants` ORDER BY `id` DESC");
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- CRUD Content area -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-shop text-danger me-2"></i>Manage Restaurant Outlets</h3>
                <?php if ($editMode): ?>
                    <a href="restaurants.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add New</a>
                <?php endif; ?>
            </div>
            
            <!-- Add / Edit Form Box -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h5 class="fw-bold mb-3"><?php echo $editMode ? 'Edit Restaurant Partner' : 'Register New Restaurant Partner'; ?></h5>
                
                <form action="restaurants.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="restaurant_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Restaurant Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $editMode ? e($editData['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cuisine_type" class="form-label fw-semibold small">Cuisine Type (e.g. Italian, Sushi, American)</label>
                            <input type="text" name="cuisine_type" id="cuisine_type" class="form-control" value="<?php echo $editMode ? e($editData['cuisine_type']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $editMode ? e($editData['phone']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold small">Operating Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" <?php echo ($editMode && $editData['status'] === 'active') ? 'selected' : ''; ?>>Active (Ordering open)</option>
                                <option value="inactive" <?php echo ($editMode && $editData['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive (Ordering closed)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold small">Corporate Address *</label>
                        <input type="text" name="address" id="address" class="form-control" value="<?php echo $editMode ? e($editData['address']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control"><?php echo $editMode ? e($editData['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="restaurant_image" class="form-label fw-semibold small">Cover Image File</label>
                        <input type="file" name="restaurant_image" id="restaurant_image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        <?php if ($editMode && !empty($editData['image_path'])): ?>
                            <div class="form-text mt-1 text-muted">Current file: <code><?php echo e(basename($editData['image_path'])); ?></code></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-brand px-5 py-2"><?php echo $editMode ? 'Save Details' : 'Register Restaurant'; ?></button>
                </form>
            </div>
            
            <!-- List Restaurants Grid Table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Registered Restaurants Directory</h5>
                
                <?php if (empty($restaurants)): ?>
                    <p class="text-muted small">No restaurants listed in the system.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">Cover</th>
                                    <th scope="col">Restaurant</th>
                                    <th scope="col">Cuisine</th>
                                    <th scope="col">Address / Contact</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($restaurants as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="rounded overflow-hidden" style="width: 60px; height: 45px;">
                                                <?php 
                                                $path = image_url($r['image_path'] ?? '', 'assets/images/default_restaurant.jpg');
                                                ?>
                                                <img src="<?php echo $path; ?>" alt="Cover" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark d-block small"><?php echo e($r['name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $r['id']; ?></span>
                                        </td>
                                        <td class="small"><?php echo e($r['cuisine_type']); ?></td>
                                        <td class="small">
                                            <span class="d-block text-dark text-truncate" style="max-width: 180px;"><?php echo e($r['address']); ?></span>
                                            <span class="text-muted"><?php echo e($r['phone']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $r['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?> text-uppercase" style="font-size: 0.75rem;">
                                                <?php echo $r['status']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="restaurants.php?edit_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-light"><i class="bi bi-pencil-square"></i></a>
                                            <a href="restaurants.php?action=delete&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete restaurant and all associated menu items?');">
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
