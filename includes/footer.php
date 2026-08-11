<?php
/**
 * Global Footer Template
 * Contains footer layout, copyright info, and scripts loading.
 */
?>
    <!-- Main Footer layout -->
    <footer class="footer-custom mt-auto">
        <div class="container">
            <div class="row g-4">
                <!-- Col 1: About the System -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white fw-bold mb-3">
                        <i class="bi bi-bicycle me-2 text-danger"></i><?php echo e(SITE_NAME); ?>
                    </h5>
                    <p class="text-muted small">
                        <?php echo __('footer_about_text'); ?>
                    </p>
                    <div class="d-flex gap-3 fs-5 mt-3">
                        <a href="#" class="text-muted"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                
                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="text-white fw-semibold mb-3"><?php echo __('footer_quick_links'); ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>index.php" class="footer-link"><?php echo __('nav_home'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>restaurant.php" class="footer-link"><?php echo __('nav_restaurants'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>about.php" class="footer-link"><?php echo __('nav_about'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>contact.php" class="footer-link"><?php echo __('nav_contact'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Col 3: Accounts & Cart -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="text-white fw-semibold mb-3"><?php echo __('footer_customer_area'); ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>pages/customer/profile.php" class="footer-link"><?php echo __('nav_my_profile'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/customer/cart.php" class="footer-link"><?php echo __('nav_my_cart'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/customer/order-history.php" class="footer-link"><?php echo __('footer_order_history'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>forgot-password.php" class="footer-link"><?php echo __('footer_forgot_password'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Col 4: Contact/Support Details -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white fw-semibold mb-3"><?php echo __('footer_contact_support'); ?></h5>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-2 text-danger"></i>123 University Blvd, Tech City, TC 10101</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-2 text-danger"></i>+1 (555) 019-9000</p>
                    <p class="text-muted small mb-3"><i class="bi bi-envelope me-2 text-danger"></i>support@fooddelivery.com</p>
                    <form class="newsletter-form d-flex gap-2">
                        <input type="email" class="form-control form-control-sm rounded-pill" placeholder="<?php echo __('footer_email_placeholder'); ?>" aria-label="Email">
                        <button type="button" class="btn btn-sm btn-brand rounded-pill px-3"><?php echo __('footer_join'); ?></button>
                    </form>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted small">&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. <?php echo __('footer_rights'); ?></p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <ul class="list-inline mb-0 small">
                        <li class="list-inline-item"><a href="#" class="text-muted text-decoration-none me-3"><?php echo __('footer_privacy'); ?></a></li>
                        <li class="list-inline-item"><a href="#" class="text-muted text-decoration-none me-3"><?php echo __('footer_terms'); ?></a></li>
                        <li class="list-inline-item"><a href="<?php echo BASE_URL; ?>install.php" class="text-danger text-decoration-none fw-semibold"><?php echo __('footer_reset_db'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <button id="backToTop" class="btn btn-danger rounded-circle shadow-lg" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 for richer interactions -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom validation rules script -->
    <script src="<?php echo BASE_URL; ?>assets/js/validation.js"></script>
    <!-- Custom interactions helper scripts -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <!-- Admin CRUD utilities (toast notifications, confirmations, AJAX handlers) -->
    <script src="<?php echo BASE_URL; ?>assets/js/admin-crud.js"></script>
</body>
</html>