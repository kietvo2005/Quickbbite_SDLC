<?php
/**
 * Contact Us Page - Food Delivery System
 * Captures user enquiries, validates them, and registers them inside database.
 */

$pageTitle = "Contact Us";
require_once __DIR__ . '/includes/header.php';

// Handle support query submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Guard check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    $errors = [];
    
    // Server-side input validations
    if (empty($name)) {
        $errors[] = __('contact_error_name');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('contact_error_email');
    }
    if (empty($subject)) {
        $errors[] = __('contact_error_subject');
    }
    if (empty($message)) {
        $errors[] = __('contact_error_message');
    }
    
    if (empty($errors)) {
        // Save database query
        $db = Database::getInstance();
        $sql = "INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `status`) VALUES (?, ?, ?, ?, 'unread')";
        $db->execute($sql, [$name, $email, $subject, $message]);
        
        set_flash('success', __('contact_success'));
        redirect(BASE_URL . 'contact.php');
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark"><?php echo __('contact_hero_title'); ?></h2>
            <p class="text-muted"><?php echo __('contact_hero_subtitle'); ?></p>
        </div>
    </div>
    
    <div class="row g-5">
        <!-- Contact Form Column -->
        <div class="col-lg-6">
            <div class="card auth-card p-4">
                <h4 class="fw-bold mb-4 text-primary"><?php echo __('contact_send_message'); ?></h4>
                
                <form id="contactForm" action="contact.php" method="POST" class="needs-validation" novalidate>
                    <?php echo csrf_input(); ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold"><?php echo __('contact_your_name'); ?></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="<?php echo __('contact_name_placeholder'); ?>" required>
                        <div class="invalid-feedback"><?php echo __('contact_name_required'); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold"><?php echo __('contact_email'); ?></label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="<?php echo __('contact_email_placeholder'); ?>" required>
                        <div class="invalid-feedback"><?php echo __('contact_email_required'); ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label fw-semibold"><?php echo __('contact_subject'); ?></label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="<?php echo __('contact_subject_placeholder'); ?>" required>
                        <div class="invalid-feedback"><?php echo __('contact_subject_required'); ?></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="message" class="form-label fw-semibold"><?php echo __('contact_message'); ?></label>
                        <textarea name="message" id="message" rows="5" class="form-control" placeholder="<?php echo __('contact_message_placeholder'); ?>" required></textarea>
                        <div class="invalid-feedback"><?php echo __('contact_message_required'); ?></div>
                    </div>
                    
                    <button type="submit" class="btn btn-brand w-100 py-2"><?php echo __('contact_submit'); ?></button>
                </form>
            </div>
        </div>
        
        <!-- Info Details Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-between">
                <div>
                    <h4 class="fw-bold mb-4 text-dark"><?php echo __('contact_business_info'); ?></h4>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="bg-light p-3 rounded-circle text-danger">
                            <i class="bi bi-geo-alt-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?php echo __('contact_corporate_hq'); ?></h6>
                            <p class="text-muted small mb-0">123 University Blvd, Tech City, TC 10101</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="bg-light p-3 rounded-circle text-danger">
                            <i class="bi bi-telephone-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?php echo __('contact_call_center'); ?></h6>
                            <p class="text-muted small mb-0"><?php echo __('contact_call_center_hours'); ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="bg-light p-3 rounded-circle text-danger">
                            <i class="bi bi-envelope-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?php echo __('contact_online_support'); ?></h6>
                            <p class="text-muted small mb-0">support@fooddelivery.com</p>
                        </div>
                    </div>
                </div>
                
                <!-- Styled Google Map Placeholder -->
                <div class="rounded-4 overflow-hidden shadow-sm" style="height: 250px; background-color: #e9ecef; position: relative;">
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i class="bi bi-map-fill text-muted fs-1 mb-2"></i>
                        <h6 class="fw-bold text-dark"><?php echo __('contact_map_placeholder_title'); ?></h6>
                        <p class="text-muted small mb-0"><?php echo __('contact_map_placeholder_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>