<?php
/**
 * Category CRUD Manager - Food Delivery System Admin Panel
 * Adds, edits, lists, and deletes food categories.
 */

$pageTitle = "Manage Categories";
require_once __DIR__ . '/../../includes/header.php';

// Guards
require_admin();

$db = Database::getInstance();

// Setup upload folder dynamically
$uploadDir = __DIR__ . '/../../uploads/categories/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Track edit states
$editMode = false;
$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editData = $db->queryRow("SELECT * FROM `categories` WHERE `id` = ?", [$editId]);
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
    $icon = sanitize_input($_POST['icon_class'] ?? 'bi-egg-fried');
    
    $errors = [];
    if (empty($name)) $errors[] = "Category name is required.";
    
    // Check name uniqueness (excluding current edit ID)
    if ($editMode) {
        $nameCheck = $db->queryRow("SELECT `id` FROM `categories` WHERE `name` = ? AND `id` != ?", [$name, $_POST['category_id']]);
    } else {
        $nameCheck = $db->queryRow("SELECT `id` FROM `categories` WHERE `name` = ?", [$name]);
    }
    if ($nameCheck) {
        $errors[] = "A category named '" . $name . "' already exists.";
    }
    
    // Optional Category Image File
    $imagePath = '';
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['category_image']['tmp_name'];
        $fileName = $_FILES['category_image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'cat_' . time() . '_' . rand(10, 99) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = 'uploads/categories/' . $newFileName;
            }
        } else {
            $errors[] = "Only JPG, JPEG, and PNG formats are allowed for category images.";
        }
    }
    
    if (empty($errors)) {
        if ($action === 'add') {
            $sql = "INSERT INTO `categories` (`name`, `description`, `icon_class`, `image_path`) VALUES (?, ?, ?, ?)";
            $db->execute($sql, [$name, $description, $icon, $imagePath ?: 'assets/images/default_category.jpg']);
            set_flash('success', 'New category created successfully.');
        } elseif ($action === 'edit' && isset($_POST['category_id'])) {
            $categoryId = (int)$_POST['category_id'];
            
            // Retain path if no new file uploaded
            if (empty($imagePath)) {
                $existing = $db->queryRow("SELECT `image_path` FROM `categories` WHERE `id` = ?", [$categoryId]);
                $imagePath = $existing['image_path'] ?? 'assets/images/default_category.jpg';
            } else {
                // Delete old image
                $old = $db->queryRow("SELECT `image_path` FROM `categories` WHERE `id` = ?", [$categoryId]);
                if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
                    unlink(__DIR__ . '/../../' . $old['image_path']);
                }
            }
            
            $sql = "UPDATE `categories` SET `name` = ?, `description` = ?, `icon_class` = ?, `image_path` = ? WHERE `id` = ?";
            $db->execute($sql, [$name, $description, $icon, $imagePath, $categoryId]);
            set_flash('success', 'Category details updated successfully.');
        }
        redirect(BASE_URL . 'pages/admin/categories.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// 2. Handle CRUD - Delete (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    
    // Check if there are foods under this category (foreign key RESTRICT simulation)
    $foodCheck = (int)$db->queryRow("SELECT COUNT(*) FROM `foods` WHERE `category_id` = ?", [$targetId])['COUNT(*)'];
    if ($foodCheck > 0) {
        set_flash('danger', 'Cannot delete category: ' . $foodCheck . ' food items are currently linked to this category.');
    } else {
        $old = $db->queryRow("SELECT `image_path` FROM `categories` WHERE `id` = ?", [$targetId]);
        if ($old && !empty($old['image_path']) && strpos($old['image_path'], 'uploads/') !== false && file_exists(__DIR__ . '/../../' . $old['image_path'])) {
            unlink(__DIR__ . '/../../' . $old['image_path']);
        }
        
        $db->execute("DELETE FROM `categories` WHERE `id` = ?", [$targetId]);
        set_flash('info', 'Category deleted successfully.');
    }
    redirect(BASE_URL . 'pages/admin/categories.php');
}

// Fetch all categories
$categories = $db->queryAll("SELECT * FROM `categories` ORDER BY `id` DESC");
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Category Manager area -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-tags text-danger me-2"></i>Manage Food Categories</h3>
                <?php if ($editMode): ?>
                    <a href="categories.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add New</a>
                <?php endif; ?>
            </div>
            
            <!-- Category form box -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h5 class="fw-bold mb-3"><?php echo $editMode ? 'Edit Category' : 'Create New Category'; ?></h5>
                
                <form action="categories.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="category_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Category Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $editMode ? e($editData['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon_class" class="form-label fw-semibold small">Bootstrap Icon Class (e.g. <code>bi-egg-fried</code>, <code>bi-cup-hot</code>)</label>
                            <input type="text" name="icon_class" id="icon_class" class="form-control" value="<?php echo $editMode ? e($editData['icon_class']) : 'bi-egg-fried'; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small">Description</label>
                        <textarea name="description" id="description" rows="2" class="form-control"><?php echo $editMode ? e($editData['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="category_image" class="form-label fw-semibold small">Category Image File</label>
                        <input type="file" name="category_image" id="category_image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        <?php if ($editMode && !empty($editData['image_path'])): ?>
                            <div class="form-text mt-1 text-muted">Current file: <code><?php echo e(basename($editData['image_path'])); ?></code></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-brand px-5 py-2"><?php echo $editMode ? 'Save Details' : 'Create Category'; ?></button>
                </form>
            </div>
            
            <!-- Category list grid table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Categories Directory</h5>
                
                <?php if (empty($categories)): ?>
                    <p class="text-muted small">No food categories listed in the system.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">Icon</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Description</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td>
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 50px; height: 50px;">
                                                <i class="bi <?php echo e($cat['icon_class']); ?> text-danger fs-3"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark d-block small"><?php echo e($cat['name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $cat['id']; ?></span>
                                        </td>
                                        <td class="small text-muted" style="max-width: 250px;"><?php echo e($cat['description']); ?></td>
                                        <td class="text-end">
                                            <a href="categories.php?edit_id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-light"><i class="bi bi-pencil-square"></i></a>
                                            <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete this category? Category will only delete if no food items are linked to it.');">
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
