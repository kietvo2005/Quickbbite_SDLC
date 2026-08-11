<?php
/**
 * User Account Manager - QuickBite Admin Panel
 * Lists accounts, adjusts role levels, toggles active status, and manages suspensions.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';

// Guards
require_admin();

$pageTitle = "Manage Users";
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();
$adminId = $_SESSION['user_id'];

// 1. Handle Status Toggles & Deletions (GET Action Handles)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Prevent actions on oneself
    if ($targetId === $adminId) {
        set_flash('danger', 'You cannot suspend or delete your own admin session.');
        redirect(BASE_URL . 'pages/admin/users.php');
    }
    
    if ($action === 'toggle_status' && isset($_GET['status'])) {
        $status = sanitize_input($_GET['status']);
        if (in_array($status, ['active', 'inactive', 'suspended'])) {
            $db->execute("UPDATE `users` SET `status` = ? WHERE `id` = ?", [$status, $targetId]);
            set_flash('success', 'User status updated to ' . $status);
        }
    } elseif ($action === 'delete') {
        $db->execute("DELETE FROM `users` WHERE `id` = ?", [$targetId]);
        set_flash('info', 'User account permanently deleted.');
    }
    redirect(BASE_URL . 'pages/admin/users.php');
}

// 2. Handle Role Upgrades (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $targetId = (int)$_POST['user_id'];
    $role = sanitize_input($_POST['role']);
    
    if ($targetId === $adminId) {
        set_flash('danger', 'You cannot modify your own administrative rank.');
    } elseif (in_array($role, ['customer', 'admin'])) {
        $db->execute("UPDATE `users` SET `role` = ? WHERE `id` = ?", [$role, $targetId]);
        
        // If changed to admin, ensure there is a record in the admins table
        if ($role === 'admin') {
            $adminExists = $db->queryRow("SELECT `id` FROM `admins` WHERE `user_id` = ?", [$targetId]);
            if (!$adminExists) {
                $db->execute("INSERT INTO `admins` (`user_id`, `full_name`, `role`) VALUES (?, ?, 'Administrator')", [
                    $targetId, 'Staff Member'
                ]);
            }
        } else {
            // Remove admin record if demoted
            $db->execute("DELETE FROM `admins` WHERE `user_id` = ?", [$targetId]);
        }
        
        set_flash('success', 'User authorization role updated successfully.');
    }
    redirect(BASE_URL . 'pages/admin/users.php');
}

// Fetch all registered users
$users = $db->queryAll("SELECT * FROM `users` ORDER BY `id` DESC");
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- User management Panel -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-people text-danger me-2"></i>Manage System Users</h3>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th scope="col">User</th>
                                <th scope="col">Email / Phone</th>
                                <th scope="col">Role</th>
                                <th scope="col">Account Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php 
                                            $avatar = image_url(!empty($u['avatar']) ? 'uploads/avatars/' . $u['avatar'] : 'assets/images/default_avatar.jpg');
                                            ?>
                                            <img src="<?php echo $avatar; ?>" alt="Avatar" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                                            <div>
                                                <span class="fw-bold text-dark d-block small"><?php echo e($u['username']); ?></span>
                                                <span class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $u['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <span class="d-block text-dark"><?php echo e($u['email']); ?></span>
                                        <span class="text-muted"><?php echo e($u['phone'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <!-- Role Upgrade/Demote Select -->
                                        <form action="users.php" method="POST" class="d-inline-flex gap-1 align-items-center">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            
                                            <select name="role" class="form-select form-select-sm bg-light border-0" onchange="this.form.submit()" <?php echo $u['id'] === $adminId ? 'disabled' : ''; ?>>
                                                <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $u['status'];
                                        $badge = 'bg-success';
                                        if ($status === 'inactive') $badge = 'bg-secondary';
                                        elseif ($status === 'suspended') $badge = 'bg-danger';
                                        ?>
                                        <span class="badge rounded-pill <?php echo $badge; ?> text-uppercase small" style="font-size: 0.7rem;"><?php echo $status; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($u['id'] !== $adminId): ?>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu shadow border-0 p-1">
                                                    <li><a class="dropdown-item rounded small" href="users.php?action=toggle_status&id=<?php echo $u['id']; ?>&status=active">Activate</a></li>
                                                    <li><a class="dropdown-item rounded small" href="users.php?action=toggle_status&id=<?php echo $u['id']; ?>&status=suspended">Suspend</a></li>
                                                </ul>
                                            </div>
                                            <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete user account permanently?');">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">You (Self)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
