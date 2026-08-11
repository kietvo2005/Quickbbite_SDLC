<?php
/**
 * Food Menu Item CRUD Manager - QuickBite Admin Panel (AJAX-Powered)
 * Adds, edits, lists, and deletes food menu options across restaurants.
 * No page reloads - all operations handled via AJAX with toast notifications.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';

// Guards
require_admin();

$pageTitle = "Manage Foods";
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// Track edit states (display mode only - no form processing)
$editMode = false;
$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editData = $db->queryRow("SELECT * FROM `foods` WHERE `id` = ?", [$editId]);
    if ($editData) {
        $editMode = true;
    }
}

// Fetch helper lists: restaurants and categories for selector dropdowns
$restaurantsDropdown = $db->queryAll("SELECT `id`, `name` FROM `restaurants` WHERE `status` = 'active'");
$categoriesDropdown = $db->queryAll("SELECT `id`, `name` FROM `categories` ORDER BY `name` ASC");

// Fetch all foods list
$foods = $db->queryAll("SELECT f.*, r.`name` AS `restaurant_name`, c.`name` AS `category_name`
                        FROM `foods` f
                        JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
                        JOIN `categories` c ON f.`category_id` = c.`id`
                        ORDER BY f.`id` DESC");

$csrfToken = $_SESSION['csrf_token'] ?? '';
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
                
                <!-- AJAX Form for Food CRUD -->
                <form id="foodForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="food_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Dish Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $editMode ? e($editData['name']) : ''; ?>" required>
                            <div class="invalid-feedback small">Dish name is required.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label fw-semibold small">Unit Price ($ USD) *</label>
                            <input type="number" step="0.01" min="0.05" name="price" id="price" class="form-control" value="<?php echo $editMode ? e($editData['price']) : ''; ?>" required>
                            <div class="invalid-feedback small">Price must be greater than 0.</div>
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
                            <div class="invalid-feedback small">Select a restaurant.</div>
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
                            <div class="invalid-feedback small">Select a category.</div>
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
                    
                    <button type="submit" class="btn btn-brand px-5 py-2">
                        <span id="submitBtnText"><?php echo $editMode ? 'Save Details' : 'Add Dish to Menus'; ?></span>
                    </button>
                </form>
            </div>
            
            <!-- List Menu items Table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Dishes Registry</h5>
                
                <?php if (empty($foods)): ?>
                    <p class="text-muted small">No food menu items have been added yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle" id="foodsTable">
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
                            <tbody id="foodsTableBody">
                                <?php foreach ($foods as $f): ?>
                                    <tr data-food-id="<?php echo $f['id']; ?>">
                                        <td>
                                            <div class="rounded overflow-hidden" style="width: 50px; height: 50px;">
                                                <?php 
                                                $path = image_url($f['image_path'] ?? '', 'assets/images/default_food.jpg');
                                                ?>
                                                <img src="<?php echo $path; ?>" alt="Dish" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark d-block small text-truncate food-name" style="max-width: 140px;"><?php echo e($f['name']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $f['id']; ?></span>
                                        </td>
                                        <td class="small">
                                            <span class="d-block text-dark fw-semibold restaurant-name"><?php echo e($f['restaurant_name']); ?></span>
                                            <span class="badge bg-light text-dark text-xs category-badge"><?php echo e($f['category_name']); ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-danger small food-price"><?php echo format_currency($f['price']); ?></td>
                                        <td>
                                            <div class="food-tags">
                                                <?php if ($f['is_popular']): ?>
                                                    <span class="badge bg-warning text-dark text-xxs px-1">Popular</span>
                                                <?php endif; ?>
                                                <?php if ($f['is_latest']): ?>
                                                    <span class="badge bg-success text-xxs px-1">New</span>
                                                <?php endif; ?>
                                                <?php if (!$f['is_available']): ?>
                                                    <span class="badge bg-secondary text-xxs px-1">Out of Stock</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light edit-food-btn" data-food-id="<?php echo $f['id']; ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1 delete-food-btn" data-food-id="<?php echo $f['id']; ?>">
                                                <i class="bi bi-trash3"></i>
                                            </button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const foodForm = document.getElementById('foodForm');
        const foodsTableBody = document.getElementById('foodsTableBody');
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        // ============================================
        // FORM SUBMISSION (Create/Update)
        // ============================================
        foodForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!foodForm.checkValidity()) {
                foodForm.classList.add('was-validated');
                return;
            }

            const formData = new FormData(foodForm);
            const foodId = formData.get('food_id');
            const isEdit = foodId && foodId !== '';
            const endpoint = isEdit
                ? '<?php echo BASE_URL; ?>admin/ajax/update_food.php'
                : '<?php echo BASE_URL; ?>admin/ajax/create_food.php';

            const submitBtn = foodForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (isEdit) {
                        // Update row in table
                        updateFoodRow(data.food);
                        window.location.href = '<?php echo BASE_URL; ?>pages/admin/foods.php';
                    } else {
                        // Clear form and add new row
                        foodForm.reset();
                        foodForm.classList.remove('was-validated');
                        // Optionally refresh the table or add new row dynamically
                        window.location.reload();
                    }
                    showAdminToast(data.message, 'success');
                } else {
                    showAdminToast(data.message || 'Unable to save food.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAdminToast('An error occurred. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // ============================================
        // EDIT BUTTON
        // ============================================
        document.querySelectorAll('.edit-food-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const foodId = this.dataset.foodId;
                window.location.href = `foods.php?edit_id=${foodId}`;
            });
        });

        // ============================================
        // DELETE BUTTON
        // ============================================
        document.querySelectorAll('.delete-food-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const foodId = this.dataset.foodId;
                const row = document.querySelector(`tr[data-food-id="${foodId}"]`);
                const foodName = row.querySelector('.food-name').textContent;

                const confirmed = await showAdminConfirm(
                    'Delete Food Item',
                    `Remove "${foodName}" from all menu tables? This action cannot be undone.`,
                    'Delete',
                    'Cancel'
                );

                if (!confirmed) return;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>admin/ajax/delete_food.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: new URLSearchParams({
                            food_id: foodId,
                            csrf_token: csrfToken
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        row.style.opacity = '0.5';
                        row.style.textDecoration = 'line-through';
                        setTimeout(() => row.remove(), 300);
                        showAdminToast('Food item deleted.', 'success');
                    } else {
                        showAdminToast(data.message || 'Unable to delete food.', 'error');
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-trash3"></i>';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAdminToast('An error occurred.', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-trash3"></i>';
                }
            });
        });

        // Helper function to update food row
        function updateFoodRow(food) {
            const row = document.querySelector(`tr[data-food-id="${food.id}"]`);
            if (row) {
                row.querySelector('.food-name').textContent = food.name;
                row.querySelector('.restaurant-name').textContent = food.restaurant_name;
                row.querySelector('.category-badge').textContent = food.category_name;
                row.querySelector('.food-price').textContent = '$' + parseFloat(food.price).toFixed(2);
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
