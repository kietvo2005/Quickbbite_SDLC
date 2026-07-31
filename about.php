<?php
/**
 * About Us Page - Academic Portfolio Design
 * Showcases the educational website's design patterns, security measures, and database normalization.
 */

$pageTitle = "About Us";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Header Section -->
<div class="bg-dark text-white py-5 mb-5 text-center" style="background: linear-gradient(135deg, #1e272e 0%, #2f3542 100%);">
    <div class="container py-3">
        <h1 class="display-5 fw-bold animate-fade-in-up"><?php echo __('about_hero_title'); ?></h1>
        <p class="lead text-muted"><?php echo __('about_hero_subtitle'); ?></p>
    </div>
</div>

<div class="container my-5">
    <!-- Grid Layout: Core Project Features -->
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-3 text-primary"><?php echo __('about_engineering_title'); ?></h2>
            <p><?php echo __('about_engineering_p1'); ?></p>
            <p><?php echo __('about_engineering_p2'); ?></p>
            
            <div class="mt-4 d-flex flex-wrap gap-2">
                <span class="badge bg-danger p-2 fs-6 rounded-pill"><i class="bi bi-shield-lock me-1"></i><?php echo __('about_badge_auth'); ?></span>
                <span class="badge bg-success p-2 fs-6 rounded-pill"><i class="bi bi-server me-1"></i><?php echo __('about_badge_pdo'); ?></span>
                <span class="badge bg-warning text-dark p-2 fs-6 rounded-pill"><i class="bi bi-layout-wtf me-1"></i><?php echo __('about_badge_bootstrap'); ?></span>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-3"><i class="bi bi-gear-fill me-2 text-danger"></i><?php echo __('about_architecture_title'); ?></h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent">
                        <span><?php echo __('about_folder_org'); ?></span>
                        <span class="badge bg-primary rounded-pill">MVC-like</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent">
                        <span><?php echo __('about_db_driver'); ?></span>
                        <span class="badge bg-primary rounded-pill">PDO (MySQL)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent">
                        <span><?php echo __('about_password_hashing'); ?></span>
                        <span class="badge bg-primary rounded-pill">PASSWORD_DEFAULT (Bcrypt)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-transparent">
                        <span><?php echo __('about_security_middleware'); ?></span>
                        <span class="badge bg-primary rounded-pill">CSRF, XSS & SQLi Protected</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Architectural Deep Dive Cards -->
    <div class="row g-4 mb-5">
        <h3 class="fw-bold text-center mb-4"><?php echo __('about_under_hood'); ?></h3>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="icon-container mb-3 d-inline-flex bg-light p-3 rounded-circle text-danger mx-auto">
                    <i class="bi bi-database-fill-gear fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2"><?php echo __('about_db_title'); ?></h5>
                <p class="text-muted small mb-0"><?php echo __('about_db_desc'); ?></p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="icon-container mb-3 d-inline-flex bg-light p-3 rounded-circle text-success mx-auto">
                    <i class="bi bi-shield-check fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2"><?php echo __('about_security_title'); ?></h5>
                <p class="text-muted small mb-0"><?php echo __('about_security_desc'); ?></p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="icon-container mb-3 d-inline-flex bg-light p-3 rounded-circle text-warning mx-auto">
                    <i class="bi bi-cart-check-fill fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2"><?php echo __('about_cart_title'); ?></h5>
                <p class="text-muted small mb-0"><?php echo __('about_cart_desc'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>