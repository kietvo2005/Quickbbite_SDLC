<?php
/**
 * Admin Profile Page
 */
$pageTitle = 'Admin Profile';
require_once __DIR__ . '/../includes/header.php';
require_admin();

$db = Database::getInstance();
$userId = (int)($_SESSION['user_id'] ?? 0);

$uploadDir = __DIR__ . '/../uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error: CSRF verification failed.');
    }

    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $errors = [];

    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email format.';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    }

    $emailCheck = $db->queryRow("SELECT `id` FROM `users` WHERE `email` = ? AND `id` != ?", [$email, $userId]);
    if ($emailCheck) {
        $errors[] = 'This email is already in use by another user.';
    }

    $avatarName = $_SESSION['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = (int)$_FILES['avatar']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png'];
        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = 'Only JPG, JPEG, and PNG formats are allowed.';
        }
        if ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Image size exceeds maximum limit of 2MB.';
        }

        if (empty($errors)) {
            $newAvatarName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newAvatarName)) {
                if (!empty($_SESSION['avatar']) && file_exists($uploadDir . $_SESSION['avatar'])) {
                    unlink($uploadDir . $_SESSION['avatar']);
                }
                $avatarName = $newAvatarName;
            } else {
                $errors[] = 'Failed to upload avatar image.';
            }
        }
    }

    if (empty($errors)) {
        $db->execute(
            "UPDATE `users` SET `name` = ?, `username` = ?, `email` = ?, `phone` = ?, `avatar` = ? WHERE `id` = ?",
            [$username, $username, $email, $phone, $avatarName, $userId]
        );

        $_SESSION['user_name'] = $username;
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['email'] = $email;
        $_SESSION['avatar'] = $avatarName;

        set_flash('success', 'Profile details updated successfully.');
        redirect(BASE_URL . 'admin/profile.php');
    }

    foreach ($errors as $err) {
        set_flash('danger', $err);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error: CSRF verification failed.');
    }

    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    $errors = [];

    $user = $db->queryRow("SELECT `password` FROM `users` WHERE `id` = ?", [$userId]);
    if (!password_verify($oldPass, $user['password'])) {
        $errors[] = 'Incorrect current password.';
    }
    if (strlen($newPass) < 8 || !preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass) || !preg_match('/[^A-Za-z0-9]/', $newPass)) {
        $errors[] = 'New password must be at least 8 characters, containing uppercase, numeric, and special characters.';
    }
    if ($newPass !== $confirmPass) {
        $errors[] = 'Confirm password does not match.';
    }

    if (empty($errors)) {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $db->execute("UPDATE `users` SET `password` = ? WHERE `id` = ?", [$hashed, $userId]);
        set_flash('success', 'Password updated successfully.');
        redirect(BASE_URL . 'admin/profile.php');
    }

    foreach ($errors as $err) {
        set_flash('danger', $err);
    }
}

$profile = $db->queryRow("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
$avatarUrl = image_url(!empty($profile['avatar']) ? 'uploads/avatars/' . $profile['avatar'] : 'assets/images/default_avatar.jpg');
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <img src="<?php echo $avatarUrl; ?>" alt="Profile avatar" class="rounded-circle border border-3 border-danger shadow-sm" style="width: 130px; height: 130px; object-fit: cover;">
                </div>
                <h4 class="fw-bold mb-1"><?php echo e($profile['username']); ?></h4>
                <p class="text-muted small mb-3"><?php echo e($profile['email']); ?></p>
                <span class="badge bg-danger rounded-pill px-3 py-2">Administrator Account</span>

                <hr class="my-4">

                <div class="text-start small">
                    <p class="mb-2"><i class="bi bi-calendar-event me-2 text-danger"></i>Joined: <?php echo date('M d, Y', strtotime($profile['created_at'])); ?></p>
                    <p class="mb-2"><i class="bi bi-phone me-2 text-danger"></i>Phone: <?php echo e($profile['phone'] ?? 'Not added'); ?></p>
                    <p class="mb-0"><i class="bi bi-clock-history me-2 text-danger"></i>Last Login: <?php echo !empty($profile['last_login']) ? date('M d, Y h:i A', strtotime($profile['last_login'])) : 'Not available yet'; ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <ul class="nav nav-pills mb-4 gap-2" id="adminProfileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="details-tab" data-bs-toggle="pill" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">
                            <i class="bi bi-person-fill me-2"></i>Profile Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                            <i class="bi bi-shield-lock-fill me-2"></i>Security
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="adminProfileTabsContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <h5 class="fw-bold mb-4">Edit Admin Profile</h5>
                        <form action="profile.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label fw-semibold">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?php echo e($profile['username']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control" value="<?php echo e($profile['phone']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?php echo e($profile['email']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="avatar-upload" class="form-label fw-semibold">Upload Avatar Profile</label>
                                <input type="file" name="avatar" id="avatar-upload" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text text-muted">Supports JPG, JPEG, and PNG formats. Max file size: 2MB.</div>
                            </div>

                            <button type="submit" class="btn btn-brand px-4 py-2">Save Profile Updates</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <h5 class="fw-bold mb-4">Change Password</h5>
                        <form action="profile.php" method="POST" class="needs-validation" novalidate>
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label for="old_password" class="form-label fw-semibold">Current Password</label>
                                <input type="password" name="old_password" id="old_password" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label fw-semibold">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                </div>
                            </div>

                            <div class="alert alert-light border small text-muted">
                                Password must include at least 8 characters, one uppercase letter, one number, and one special character.
                            </div>

                            <button type="submit" class="btn btn-outline-danger px-4 py-2">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
