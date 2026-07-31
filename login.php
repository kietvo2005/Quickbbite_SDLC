<?php
/**
 * Unified Login Page - Food Delivery System
 * Authenticates customers and administrators securely.
 */

$pageTitle = "Login";
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/functions/helpers.php';
require_once __DIR__ . '/includes/functions/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect(BASE_URL . 'admin/dashboard.php');
    } else {
        redirect(BASE_URL . 'index.php');
    }
}

// Handle login submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Guard check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }

    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $errors = [];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('login_error_email');
    }
    if (empty($password)) {
        $errors[] = __('login_error_password');
    }

    if (empty($errors)) {
        if (login($email, $password, $remember)) {
            // Sync cart items stored as guest session
           // sync_cart_on_login($_SESSION['user_id']);

            $displayName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'there';
            set_flash('success', str_replace(':name', $displayName, __('login_welcome_message')));

            $userRole = strtolower($_SESSION['role'] ?? $_SESSION['user_role'] ?? 'customer');
            if ($userRole === 'admin') {
                redirect(BASE_URL . 'admin/dashboard.php');
            } else {
                redirect(BASE_URL . 'index.php');
            }
        }
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 py-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark"><?php echo __('login_welcome_back'); ?></h3>
                    <p class="text-muted"><?php echo __('login_subtitle'); ?></p>
                </div>
                
                <form id="loginForm" action="login.php" method="POST" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold"><?php echo __('login_email'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control border-start-0 bg-transparent" placeholder="name@domain.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold"><?php echo __('login_password'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 bg-transparent" placeholder="Enter password" required>
                            <button type="button" class="btn btn-light border-start-0" id="togglePassword"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label text-muted small"><?php echo __('login_remember_me'); ?></label>
                        </div>
                        <a href="forgot-password.php" class="text-danger small text-decoration-none fw-semibold"><?php echo __('login_forgot_password'); ?></a>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2 mb-3"><?php echo __('login_sign_in'); ?></button>
                    
                    <p class="text-center text-muted small mb-0"><?php echo __('login_no_account'); ?> <a href="register.php" class="text-danger fw-semibold text-decoration-none"><?php echo __('login_sign_up'); ?></a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>