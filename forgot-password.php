<?php
/**
 * Forgot Password - Food Delivery System
 * Simulates password recovery.
 */

$pageTitle = "Forgot Password";
require_once __DIR__ . '/includes/header.php';

$resetCode = null;
$resetEmail = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $email = sanitize_input($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Please enter a valid email address.');
    } else {
        $db = Database::getInstance();
        $user = $db->queryRow("SELECT * FROM `users` WHERE `email` = ? AND `status` = 'active'", [$email]);
        
        if ($user) {
            $resetEmail = $user['email'];
            $resetCode = 'RESET-' . strtoupper(bin2hex(random_bytes(6)));
            set_flash('success', 'Simulation active: Password reset authorization generated below.');
        } else {
            set_flash('danger', 'We could not find an active account registered with that email.');
        }
    }
}
?>

<div class="container my-5 py-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Recover Password</h3>
                    <p class="text-muted">Enter registered email to reset login details</p>
                </div>
                
                <form action="forgot-password.php" method="POST" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2">Generate Reset Request</button>
                </form>

                <?php if ($resetCode && $resetEmail): ?>
                    <div class="alert alert-warning mt-4 mb-0">
                        <h6 class="fw-bold"><i class="bi bi-cpu me-2"></i>[Simulation Token Console]</h6>
                        <p class="small mb-2">Since SMTP mail is not configured on localhost, we have bypassed mailers and outputted details below:</p>
                        <ul class="small mb-0">
                            <li><strong>Recipient Account:</strong> <code><?php echo e($resetEmail); ?></code></li>
                            <li><strong>Recovery Key:</strong> <code><?php echo e($resetCode); ?></code></li>
                            <li><strong>Action:</strong> <a href="<?php echo BASE_URL; ?>login.php" class="alert-link">Return to login and use default passwords</a>.</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
