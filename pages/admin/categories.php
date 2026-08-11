<?php
/**
 * Category CRUD Manager - QuickBite Admin Panel (AJAX-Powered)
 * Adds, edits, lists, and deletes food categories.
 * No page reloads - all operations handled via AJAX with toast notifications.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';

// Guards
require_admin();

$pageTitle = "Manage Categories";
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

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

// Fetch all categories
$categories = $db->queryAll("SELECT * FROM `categories` ORDER BY `id` DESC");

$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-tags text-danger me-2"></i>Manage Food Categories</h3>
                <?php if ($editMode): ?>
                    <a href="categories.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add New</a>
                <?php endif; ?>
            </div>
            
            <!-- Add / Edit Form -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h5 class="fw-bold mb-3"><?php echo $editMode ? 'Edit Category Details' : 'Add New Food Category'; ?></h5>
                
                <form id="categoryForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="category_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold small">Category Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $editMode ? e($editData['name']) : ''; ?>" required>
                            <div class="invalid-feedback small">Category name is required.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon_class" class="form-label fw-semibold small">Bootstrap Icon Class</label>
                            <input type="text" name="icon_class" id="icon_class" class="form-control" value="<?php echo $editMode ? e($editData['icon_class']) : 'bi-egg-fried'; ?>">
                            <div class="form-text small">e.g., bi-egg-fried, bi-pizza</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control"><?php echo $editMode ? e($editData['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_image" class="form-label fw-semibold small">Category Image</label>
                        <input type="file" name="category_image" id="category_image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        <?php if ($editMode && !empty($editData['image_path'])): ?>
                            <div class="form-text mt-1 text-muted">Current: <code><?php echo e(basename($editData['image_path'])); ?></code></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-brand px-5 py-2">
                        <?php echo $editMode ? 'Save Category' : 'Create Category'; ?>
                    </button>
                </form>
            </div>
            
            <!-- Categories Table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4">Categories Registry</h5>
                
                <?php if (empty($categories)): ?>
                    <p class="text-muted small">No categories found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle" id="categoriesTable">
                            <thead class="text-muted small">
                                <tr>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th>Description</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <?php foreach ($categories as $cat): ?>
                                    <tr data-category-id="<?php echo $cat['id']; ?>">
                                        <td class="fw-bold category-name"><?php echo e($cat['name']); ?></td>
                                        <td><i class="bi <?php echo e($cat['icon_class']); ?>"></i></td>
                                        <td class="small text-muted category-description"><?php echo e(substr($cat['description'], 0, 50)); ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light edit-category-btn" data-category-id="<?php echo $cat['id']; ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1 delete-category-btn" data-category-id="<?php echo $cat['id']; ?>" data-category-name="<?php echo e($cat['name']); ?>">
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
        const form = document.getElementById('categoryForm');
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        // Form submission
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const formData = new FormData(form);
            const categoryId = formData.get('category_id');
            const isEdit = categoryId && categoryId !== '';
            const endpoint = isEdit
                ? '<?php echo BASE_URL; ?>admin/ajax/update_category.php'
                : '<?php echo BASE_URL; ?>admin/ajax/create_category.php';

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (isEdit) {
                        window.location.href = '<?php echo BASE_URL; ?>pages/admin/categories.php';
                    } else {
                        form.reset();
                        form.classList.remove('was-validated');
                        window.location.reload();
                    }
                    showAdminToast(data.message, 'success');
                } else {
                    showAdminToast(data.message || 'Unable to save.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAdminToast('An error occurred.', 'error');
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Edit button
        document.querySelectorAll('.edit-category-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const categoryId = this.dataset.categoryId;
                window.location.href = `categories.php?edit_id=${categoryId}`;
            });
        });

        // Delete button
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const categoryId = this.dataset.categoryId;
                const categoryName = this.dataset.categoryName;

                const confirmed = await showAdminConfirm(
                    'Delete Category',
                    `Remove "${categoryName}"? This action cannot be undone.`,
                    'Delete',
                    'Cancel'
                );

                if (!confirmed) return;

                this.disabled = true;

                try {
                    const response = await fetch('<?php echo BASE_URL; ?>admin/ajax/delete_category.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: new URLSearchParams({
                            category_id: categoryId,
                            csrf_token: csrfToken
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                        if (row) {
                            row.remove();
                        }
                        showAdminToast('Category deleted.', 'success');
                    } else {
                        showAdminToast(data.message || 'Unable to delete.', 'error');
                        this.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAdminToast('An error occurred.', 'error');
                    this.disabled = false;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
