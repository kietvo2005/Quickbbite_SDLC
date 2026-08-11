<?php
/**
 * Register Page - QuickBite
 * Handles customer sign ups with password hashing and session generation.
 */

$pageTitle = "Register";
require_once __DIR__ . '/includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(BASE_URL . 'index.php');
}

// Handle registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Guard check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Server-side strict validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = __('register_error_username');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('register_error_email');
    }
    if (empty($phone) || !preg_match('/^\+?[0-9]{10,15}$/', str_replace([' ', '-'], '', $phone))) {
        $errors[] = __('register_error_phone');
    }
    
    // Password checks
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = __('register_error_password_rules');
    }
    if ($password !== $confirm_password) {
        $errors[] = __('register_error_password_match');
    }
    
    // DB Check for email and username uniqueness
    if (empty($errors)) {
        $db = Database::getInstance();
        
        $emailExists = $db->queryRow("SELECT `id` FROM `users` WHERE `email` = ?", [$email]);
        if ($emailExists) {
            $errors[] = __('register_error_email_exists');
        }
        
        $userExists = $db->queryRow("SELECT `id` FROM `users` WHERE `username` = ?", [$username]);
        if ($userExists) {
            $errors[] = __('register_error_username_exists');
        }
    }
    
    if (empty($errors)) {
        $db = Database::getInstance();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert User
        $sql = "INSERT INTO `users` (`name`, `username`, `email`, `password`, `phone`, `address`, `avatar`, `role`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'customer', 'active')";
        $db->execute($sql, [$username, $username, $email, $hashed_password, $phone, null, null]);
        $user_id = $db->lastInsertId();
        
        // Automatically authenticate user sessions
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['role'] = 'customer';
        $_SESSION['avatar'] = null;
        
        // Sync guest cart contents to DB
        sync_cart_on_login($user_id);
        
        set_flash('success', str_replace(':site', SITE_NAME, __('register_success')));
        redirect(BASE_URL . 'pages/customer/profile.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}
?>

<div class="container my-5 py-3 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card auth-card p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark"><?php echo __('register_create_account'); ?></h3>
                    <p class="text-muted"><?php echo __('register_subtitle'); ?></p>
                </div>
                
                <form id="registerForm" action="register.php" method="POST" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold"><?php echo __('register_username'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="username" id="username" class="form-control border-start-0 bg-transparent" placeholder="e.g. johndoe" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold"><?php echo __('register_email'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control border-start-0 bg-transparent" placeholder="name@domain.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold"><?php echo __('register_phone'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                            <input type="text" name="phone" id="phone" class="form-control border-start-0 bg-transparent" placeholder="e.g. +15551234567" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold"><?php echo __('register_password'); ?></label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Strong password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label fw-semibold"><?php echo __('register_confirm_password'); ?></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Match password" required>
                        </div>
                    </div>
                    
                    <div class="form-text text-muted mb-4 small">
                        <?php echo __('register_password_criteria'); ?>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2 mb-3"><?php echo __('register_sign_up'); ?></button>
                    
                    <p class="text-center text-muted small mb-0"><?php echo __('register_already_registered'); ?> <a href="login.php" class="text-danger fw-semibold text-decoration-none"><?php echo __('register_sign_in'); ?></a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>