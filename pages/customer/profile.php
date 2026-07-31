<?php
/**
 * Customer Profile Dashboard - Food Delivery System
 * Handles detail changes, security credentials updates, profile pictures upload, and address CRUD.
 */

$pageTitle = "My Profile";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

// Strict customer route guard
require_customer();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Check and dynamically create uploads folder if missing
$uploadDir = __DIR__ . '/../../uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 1. Handle Details and Avatar Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    $errors = [];
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = __('profile_error_username');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('profile_error_email');
    }
    if (empty($phone)) {
        $errors[] = __('profile_error_phone');
    }
    
    // Check email uniqueness excluding current user
    $emailCheck = $db->queryRow("SELECT `id` FROM `users` WHERE `email` = ? AND `id` != ?", [$email, $userId]);
    if ($emailCheck) {
        $errors[] = __('profile_error_email_taken');
    }
    
    // Check avatar upload
    $avatarName = $_SESSION['avatar']; // Default to current
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = __('profile_error_avatar_format');
        }
        if ($fileSize > 2 * 1024 * 1024) { // 2MB
            $errors[] = __('profile_error_avatar_size');
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $newAvatarName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newAvatarName)) {
                // Delete old avatar file if exists
                if (!empty($_SESSION['avatar']) && file_exists($uploadDir . $_SESSION['avatar'])) {
                    unlink($uploadDir . $_SESSION['avatar']);
                }
                $avatarName = $newAvatarName;
            } else {
                $errors[] = __('profile_error_avatar_upload');
            }
        }
    }
    
    if (empty($errors)) {
        $db->execute("UPDATE `users` SET `name` = ?, `username` = ?, `email` = ?, `phone` = ?, `address` = ?, `avatar` = ? WHERE `id` = ?", [
            $username, $username, $email, $phone, $address, $avatarName, $userId
        ]);
        
        $_SESSION['user_name'] = $username;
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['email'] = $email;
        $_SESSION['avatar'] = $avatarName;
        
        set_flash('success', __('profile_success_update'));
        redirect(BASE_URL . 'pages/customer/profile.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// 2. Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    $user = $db->queryRow("SELECT `password` FROM `users` WHERE `id` = ?", [$userId]);
    if (!password_verify($oldPass, $user['password'])) {
        $errors[] = __('profile_error_wrong_password');
    }
    if (strlen($newPass) < 8 || !preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass) || !preg_match('/[^A-Za-z0-9]/', $newPass)) {
        $errors[] = __('profile_error_password_rules');
    }
    if ($newPass !== $confirmPass) {
        $errors[] = __('profile_error_password_match');
    }
    
    if (empty($errors)) {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $db->execute("UPDATE `users` SET `password` = ? WHERE `id` = ?", [$hashed, $userId]);
        set_flash('success', __('profile_success_password'));
        redirect(BASE_URL . 'pages/customer/profile.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

// 3. Handle Addresses management CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_address') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $line1 = sanitize_input($_POST['address_line1'] ?? '');
    $line2 = sanitize_input($_POST['address_line2'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $zip = sanitize_input($_POST['postal_code'] ?? '');
    
    if (empty($line1) || empty($city) || empty($state) || empty($zip)) {
        set_flash('danger', __('profile_error_address_required'));
    } else {
        // If it is the first address, or selected as default, check defaults
        $addrCount = (int)$db->queryRow("SELECT COUNT(*) FROM `addresses` WHERE `user_id` = ?", [$userId])['COUNT(*)'];
        $is_default = ($addrCount === 0 || isset($_POST['is_default'])) ? 1 : 0;
        
        if ($is_default === 1) {
            // Remove previous default
            $db->execute("UPDATE `addresses` SET `is_default` = 0 WHERE `user_id` = ?", [$userId]);
        }
        
        $db->execute("INSERT INTO `addresses` (`user_id`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `is_default`) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $userId, $line1, $line2, $city, $state, $zip, $is_default
        ]);
        set_flash('success', __('profile_success_address_added'));
        redirect(BASE_URL . 'pages/customer/profile.php#addresses');
    }
}

// Handle set default or delete address requests via GET parameters
if (isset($_GET['address_action']) && isset($_GET['address_id'])) {
    $action = $_GET['address_action'];
    $address_id = (int)$_GET['address_id'];
    
    // Verify address ownership
    $verifyAddr = $db->queryRow("SELECT `id`, `is_default` FROM `addresses` WHERE `id` = ? AND `user_id` = ?", [$address_id, $userId]);
    if ($verifyAddr) {
        if ($action === 'set_default') {
            $db->execute("UPDATE `addresses` SET `is_default` = 0 WHERE `user_id` = ?", [$userId]);
            $db->execute("UPDATE `addresses` SET `is_default` = 1 WHERE `id` = ?", [$address_id]);
            set_flash('success', __('profile_success_default_updated'));
        } elseif ($action === 'delete') {
            $db->execute("DELETE FROM `addresses` WHERE `id` = ?", [$address_id]);
            set_flash('info', __('profile_success_address_removed'));
            // If we deleted the default, set another address as default if any exists
            if ($verifyAddr['is_default']) {
                $another = $db->queryRow("SELECT `id` FROM `addresses` WHERE `user_id` = ? LIMIT 1", [$userId]);
                if ($another) {
                    $db->execute("UPDATE `addresses` SET `is_default` = 1 WHERE `id` = ?", [$another['id']]);
                }
            }
        }
    }
    redirect(BASE_URL . 'pages/customer/profile.php#addresses');
}

// Fetch active user details
$profile = $db->queryRow("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
// Fetch address list
$addresses = $db->queryAll("SELECT * FROM `addresses` WHERE `user_id` = ? ORDER BY `is_default` DESC, `id` DESC", [$userId]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row">
        <!-- Dashboard Sidebar / Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 bg-white">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <?php 
                    $av = image_url(!empty($profile['avatar']) ? 'uploads/avatars/' . $profile['avatar'] : 'assets/images/default_avatar.jpg');
                    ?>
                    <img src="<?php echo $av; ?>" id="avatar-preview" alt="Profile avatar" class="rounded-circle border border-3 border-danger shadow-sm" style="width: 130px; height: 130px; object-fit: cover;">
                </div>
                <h4 class="fw-bold mb-1"><?php echo e($profile['username']); ?></h4>
                <p class="text-muted small mb-3"><?php echo e($profile['email']); ?></p>
                <div class="badge bg-danger rounded-pill px-3 py-2"><?php echo __('profile_customer_account'); ?></div>
                
                <hr class="my-4">
                
                <div class="text-start">
                    <h6 class="fw-bold mb-2"><?php echo __('profile_registration_details'); ?></h6>
                    <p class="text-muted small mb-2"><i class="bi bi-person-badge me-2 text-danger"></i><?php echo __('profile_role'); ?>: <?php echo ucfirst(e($profile['role'] ?? 'customer')); ?></p>
                    <p class="text-muted small mb-2"><i class="bi bi-toggle-on me-2 text-danger"></i><?php echo __('profile_status'); ?>: <?php echo ucfirst(e($profile['status'] ?? 'active')); ?></p>
                    <p class="text-muted small mb-2"><i class="bi bi-calendar-event me-2 text-danger"></i><?php echo __('profile_joined'); ?>: <?php echo date('M d, Y', strtotime($profile['created_at'])); ?></p>
                    <p class="text-muted small mb-2"><i class="bi bi-phone me-2 text-danger"></i><?php echo __('profile_phone'); ?>: <?php echo e($profile['phone'] ?? __('profile_not_added')); ?></p>
                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-2 text-danger"></i><?php echo __('profile_address'); ?>: <?php echo e($profile['address'] ?? __('profile_not_added')); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Profile Customization Area -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-4 d-flex gap-2" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="details-tab" data-bs-toggle="pill" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">
                            <i class="bi bi-person-fill me-2"></i><?php echo __('profile_tab_details'); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="addresses-tab" data-bs-toggle="pill" data-bs-target="#addresses" type="button" role="tab" aria-controls="addresses" aria-selected="false">
                            <i class="bi bi-geo-alt-fill me-2"></i><?php echo __('profile_tab_addresses'); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                            <i class="bi bi-shield-lock-fill me-2"></i><?php echo __('profile_tab_security'); ?>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="profileTabsContent">
                    <!-- Tab 1: Update details and Avatar -->
                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <h5 class="fw-bold mb-4"><?php echo __('profile_edit_details'); ?></h5>
                        <form action="profile.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label fw-semibold"><?php echo __('profile_username'); ?></label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?php echo e($profile['username']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold"><?php echo __('profile_phone_label'); ?></label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo e($profile['phone']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold"><?php echo __('profile_email'); ?></label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo e($profile['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label fw-semibold"><?php echo __('profile_address_label'); ?></label>
                                <textarea name="address" id="address" class="form-control" rows="3" placeholder="<?php echo __('profile_address_placeholder'); ?>"><?php echo e($profile['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="avatar-upload" class="form-label fw-semibold"><?php echo __('profile_upload_avatar'); ?></label>
                                <input type="file" name="avatar" id="avatar-upload" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text text-muted"><?php echo __('profile_upload_hint'); ?></div>
                            </div>
                            
                            <button type="submit" class="btn btn-brand px-4 py-2"><?php echo __('profile_save_updates'); ?></button>
                        </form>
                    </div>
                    
                    <!-- Tab 2: Saved addresses management (CRUD) -->
                    <div class="tab-pane fade" id="addresses" role="tabpanel" aria-labelledby="addresses-tab">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0"><?php echo __('profile_delivery_addresses'); ?></h5>
                            <button type="button" class="btn btn-brand btn-sm px-3" data-bs-toggle="collapse" data-bs-target="#newAddressForm">
                                <i class="bi bi-plus-lg me-1"></i><?php echo __('profile_add_address'); ?>
                            </button>
                        </div>
                        
                        <!-- Add Address Form (Collapsible) -->
                        <div class="collapse mb-4" id="newAddressForm">
                            <div class="p-3 border rounded-4 bg-light">
                                <h6 class="fw-bold mb-3"><?php echo __('profile_new_address'); ?></h6>
                                <form action="profile.php" method="POST">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="action" value="add_address">
                                    
                                    <div class="mb-3">
                                        <label for="address_line1" class="form-label fw-semibold small"><?php echo __('profile_address_line1'); ?></label>
                                        <input type="text" name="address_line1" id="address_line1" class="form-control form-control-sm" placeholder="<?php echo __('profile_address_line1_placeholder'); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address_line2" class="form-label fw-semibold small"><?php echo __('profile_address_line2'); ?></label>
                                        <input type="text" name="address_line2" id="address_line2" class="form-control form-control-sm" placeholder="<?php echo __('profile_address_line2_placeholder'); ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label fw-semibold small"><?php echo __('profile_city'); ?></label>
                                            <input type="text" name="city" id="city" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="state" class="form-label fw-semibold small"><?php echo __('profile_state'); ?></label>
                                            <input type="text" name="state" id="state" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="postal_code" class="form-label fw-semibold small"><?php echo __('profile_postal_code'); ?></label>
                                            <input type="text" name="postal_code" id="postal_code" class="form-control form-control-sm" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_default" id="is_default" class="form-check-input" value="1">
                                        <label for="is_default" class="form-check-label text-muted small"><?php echo __('profile_set_default_checkbox'); ?></label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-brand btn-sm px-4 py-2"><?php echo __('profile_add_address_btn'); ?></button>
                                </form>
                            </div>
                        </div>

                        <!-- Address Entries list -->
                        <?php if (empty($addresses)): ?>
                            <p class="text-muted small"><?php echo __('profile_no_addresses'); ?></p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 bg-light position-relative <?php echo $addr['is_default'] ? 'border-danger' : ''; ?>">
                                            <?php if ($addr['is_default']): ?>
                                                <span class="badge bg-danger rounded-pill mb-2"><?php echo __('profile_default_address'); ?></span>
                                            <?php endif; ?>
                                            <p class="small mb-1 text-dark fw-semibold"><?php echo e($addr['address_line1']); ?></p>
                                            <?php if (!empty($addr['address_line2'])): ?>
                                                <p class="small mb-1 text-muted"><?php echo e($addr['address_line2']); ?></p>
                                            <?php endif; ?>
                                            <p class="small text-muted mb-3"><?php echo e($addr['city']); ?>, <?php echo e($addr['state']); ?> <?php echo e($addr['postal_code']); ?></p>
                                            
                                            <div class="d-flex gap-2">
                                                <?php if (!$addr['is_default']): ?>
                                                    <a href="profile.php?address_action=set_default&address_id=<?php echo $addr['id']; ?>" class="btn btn-xs btn-outline-secondary py-1 px-2 rounded small" style="font-size: 0.75rem;"><?php echo __('profile_set_default'); ?></a>
                                                <?php endif; ?>
                                                <a href="profile.php?address_action=delete&address_id=<?php echo $addr['id']; ?>" class="btn btn-xs btn-outline-danger py-1 px-2 rounded small" style="font-size: 0.75rem;" onclick="return confirm('<?php echo __('profile_delete_confirm'); ?>');"><?php echo __('profile_delete'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tab 3: Security Credentials Update -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <h5 class="fw-bold mb-4"><?php echo __('profile_change_password'); ?></h5>
                        <form action="profile.php" method="POST" class="needs-validation" novalidate>
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="old_password" class="form-label fw-semibold"><?php echo __('profile_current_password'); ?></label>
                                <input type="password" name="old_password" id="old_password" class="form-control" placeholder="<?php echo __('profile_current_password_placeholder'); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label fw-semibold"><?php echo __('profile_new_password'); ?></label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="<?php echo __('profile_new_password_placeholder'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label fw-semibold"><?php echo __('profile_confirm_new_password'); ?></label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="<?php echo __('profile_confirm_new_password_placeholder'); ?>" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-brand px-4 py-2 mt-2"><?php echo __('profile_update_password'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle tab focus based on hash redirects (e.g. redirecting to #addresses)
    document.addEventListener("DOMContentLoaded", () => {
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
            if (triggerEl) {
                bootstrap.Tab.getInstance(triggerEl)?.show() || new bootstrap.Tab(triggerEl).show();
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>