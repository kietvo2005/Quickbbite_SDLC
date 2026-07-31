<?php
/**
 * Global Header Template
 * Injects CSS frameworks and displays the sticky-top navigation header.
 */

// Load necessary files relative to this template directory
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/functions/helpers.php';
require_once __DIR__ . '/functions/auth.php';
require_once __DIR__ . '/functions/cart_functions.php';

// Prepare active user
$currentUser = is_logged_in() ? get_logged_in_user() : null;
$cartCount = get_cart_count();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="<?php echo (($_SESSION['language'] ?? 'en') === 'vi') ? 'vi' : 'en'; ?>" class="<?php echo (($_SESSION['theme'] ?? 'light') === 'dark') ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">

    <!-- Dark Mode Overrides -->
    <style>
        html.dark-mode {
            color-scheme: dark;
            --bs-body-bg: #0a0b0f;
            --bs-body-color: #e6e8eb;
            --bs-border-color: rgba(255, 255, 255, 0.08);
            --bs-secondary-color: #8a8f98;
            --bs-tertiary-bg: #14161b;
            --bs-heading-color: #f2f3f5;
            --bs-link-color: #ff5464;
            --bs-link-hover-color: #ff7a86;
        }

        html.dark-mode body {
            background: #0a0b0f !important;
            color: #e6e8eb;
        }

        /* Glassy navbar with subtle blur */
        html.dark-mode .navbar-custom {
            background-color: rgba(15, 17, 22, 0.85) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        html.dark-mode .navbar-custom .nav-link,
        html.dark-mode .navbar-brand span {
            color: #d5d8dd !important;
        }
        html.dark-mode .navbar-custom .nav-link:hover {
            color: #ffffff !important;
        }
        html.dark-mode .navbar-custom .nav-link.active-nav {
            color: #ff5464 !important;
        }
        html.dark-mode .brand-badge {
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08);
        }

        /* Elevated surfaces: cards, panels */
        html.dark-mode .bg-white,
        html.dark-mode .card,
        html.dark-mode .auth-card {
            background-color: #14161b !important;
            color: #e6e8eb !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4), 0 8px 24px rgba(0, 0, 0, 0.28) !important;
        }
        html.dark-mode .card:hover,
        html.dark-mode .food-card:hover {
            border-color: rgba(255, 84, 100, 0.35) !important;
        }
        html.dark-mode .bg-light {
            background-color: #191c22 !important;
        }
        html.dark-mode .food-card,
        html.dark-mode .category-card,
        html.dark-mode .list-group-item {
            background-color: #14161b !important;
            color: #e6e8eb !important;
            border-color: rgba(255, 255, 255, 0.07) !important;
        }
        html.dark-mode .category-card.border-danger {
            border-color: #ff5464 !important;
            box-shadow: 0 0 0 1px rgba(255, 84, 100, 0.15);
        }

        /* Typography */
        html.dark-mode .text-dark {
            color: #e6e8eb !important;
        }
        html.dark-mode .text-muted {
            color: #8a8f98 !important;
        }
        html.dark-mode h1, html.dark-mode h2, html.dark-mode h3,
        html.dark-mode h4, html.dark-mode h5, html.dark-mode h6,
        html.dark-mode .fw-bold {
            color: #f2f3f5 !important;
            opacity: 1 !important;
        }

        /* Borders & dividers */
        html.dark-mode .border,
        html.dark-mode .border-bottom,
        html.dark-mode hr {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Form controls */
        html.dark-mode .form-control,
        html.dark-mode .form-select,
        html.dark-mode .input-group-text {
            background-color: #191c22 !important;
            color: #e6e8eb !important;
            border-color: rgba(255, 255, 255, 0.09) !important;
        }
        html.dark-mode .form-control:focus,
        html.dark-mode .form-select:focus {
            background-color: #1c1f26 !important;
            color: #ffffff !important;
            border-color: #ff5464 !important;
            box-shadow: 0 0 0 3px rgba(255, 84, 100, 0.15) !important;
        }
        html.dark-mode .form-control::placeholder {
            color: #5b616c !important;
        }
        html.dark-mode .form-check-input {
            background-color: #191c22;
            border-color: rgba(255, 255, 255, 0.15);
        }
        html.dark-mode .form-check-input:checked {
            background-color: #ff5464;
            border-color: #ff5464;
        }

        /* Dropdowns */
        html.dark-mode .dropdown-menu {
            background-color: #14161b !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark-mode .dropdown-item {
            color: #d5d8dd !important;
        }
        html.dark-mode .dropdown-item:hover {
            background-color: #1c1f26 !important;
            color: #ffffff !important;
        }
        html.dark-mode .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Buttons */
        html.dark-mode .btn-light {
            background-color: #191c22 !important;
            color: #e6e8eb !important;
            border: 1px solid rgba(255, 255, 255, 0.09) !important;
        }
        html.dark-mode .btn-light:hover {
            background-color: #22262e !important;
        }
        html.dark-mode .btn-outline-dark {
            color: #e6e8eb !important;
            border-color: rgba(255, 255, 255, 0.18) !important;
        }
        html.dark-mode .btn-outline-dark:hover {
            background-color: #191c22 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
        }
        html.dark-mode .btn-outline-secondary,
        html.dark-mode .btn-outline-danger {
            color: #c3c8d1 !important;
            border-color: rgba(255, 255, 255, 0.18) !important;
        }
        html.dark-mode .btn-brand {
            box-shadow: 0 4px 14px rgba(255, 84, 100, 0.35);
        }
        html.dark-mode .btn-brand:hover {
            box-shadow: 0 6px 18px rgba(255, 84, 100, 0.45);
        }

        /* Tables */
        html.dark-mode table,
        html.dark-mode .table {
            color: #e6e8eb !important;
        }
        html.dark-mode .table > :not(caption) > * > * {
            background-color: #14161b !important;
            color: #e6e8eb !important;
            border-color: rgba(255, 255, 255, 0.07) !important;
        }
        html.dark-mode .table-hover > tbody > tr:hover > * {
            background-color: #191c22 !important;
        }

        /* Alerts (flash messages) */
        html.dark-mode .alert-success {
            background-color: rgba(46, 213, 115, 0.12);
            color: #6fe19b;
            border-color: rgba(46, 213, 115, 0.25);
        }
        html.dark-mode .alert-danger {
            background-color: rgba(255, 71, 87, 0.12);
            color: #ff8792;
            border-color: rgba(255, 71, 87, 0.25);
        }
        html.dark-mode .alert-warning {
            background-color: rgba(255, 165, 2, 0.12);
            color: #ffc266;
            border-color: rgba(255, 165, 2, 0.25);
        }
        html.dark-mode .alert-info {
            background-color: rgba(112, 161, 255, 0.12);
            color: #9cbbff;
            border-color: rgba(112, 161, 255, 0.25);
        }

        /* Badges & chips */
        html.dark-mode .rating-chip,
        html.dark-mode .delivery-chip {
            background-color: #191c22 !important;
            color: #d5d8dd !important;
        }
        html.dark-mode .badge.bg-light {
            background-color: #191c22 !important;
            color: #c3c8d1 !important;
        }

        /* Footer stays intentionally darker than cards for hierarchy */
        html.dark-mode .footer-custom {
            background-color: #08090c !important;
        }

        /* Smooth transitions between light/dark */
        html body,
        html .card,
        html .navbar-custom,
        html .form-control,
        html .form-select,
        html .btn,
        html .food-card {
            transition: background-color 0.2s ease, color 0.2s ease,
                        border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* Scrollbar polish for dark mode */
        html.dark-mode ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        html.dark-mode ::-webkit-scrollbar-track {
            background: #0a0b0f;
        }
        html.dark-mode ::-webkit-scrollbar-thumb {
            background: #2a2d34;
            border-radius: 6px;
        }
        html.dark-mode ::-webkit-scrollbar-thumb:hover {
            background: #383c45;
        }
    </style>
</head>
    </style>
</head>
<body>
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>

    <!-- Responsive Glassmorphism Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom py-2 sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo is_admin() ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'index.php'; ?>">
                <span class="brand-badge me-2"><i class="bi bi-bicycle"></i></span>
                <span><?php echo e(SITE_NAME); ?></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo $currentPage === 'index.php' ? 'active-nav' : ''; ?>" href="<?php echo BASE_URL; ?>index.php"><?php echo __('nav_home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo in_array($currentPage, ['restaurant.php', 'restaurant-menu.php']) ? 'active-nav' : ''; ?>" href="<?php echo BASE_URL; ?>restaurant.php"><?php echo __('nav_restaurants'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo $currentPage === 'about.php' ? 'active-nav' : ''; ?>" href="<?php echo BASE_URL; ?>about.php"><?php echo __('nav_about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo $currentPage === 'contact.php' ? 'active-nav' : ''; ?>" href="<?php echo BASE_URL; ?>contact.php"><?php echo __('nav_contact'); ?></a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Quick Dark Mode Toggle -->
                    <a href="<?php echo BASE_URL; ?>toggle-theme.php" class="btn btn-light rounded-circle p-2 shadow-sm" aria-label="Toggle dark mode" title="Toggle dark mode">
                        <?php if (($_SESSION['theme'] ?? 'light') === 'dark'): ?>
                            <i class="bi bi-sun fs-5"></i>
                        <?php else: ?>
                            <i class="bi bi-moon-stars fs-5"></i>
                        <?php endif; ?>
                    </a>

                    <!-- Shopping Cart Icon -->
                    <?php if (!is_admin()): ?>
                        <a href="<?php echo BASE_URL; ?>pages/customer/cart.php" class="btn btn-light position-relative rounded-circle p-2 shadow-sm">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <?php if (is_admin() && basename($_SERVER['SCRIPT_NAME']) === 'index.php'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-danger rounded-pill px-3 py-2">
                            <i class="bi bi-speedometer2 me-2"></i><?php echo __('nav_admin_dashboard'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-dark dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-2 shadow-sm" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php 
                                $avatarPath = image_url(!empty($_SESSION['avatar']) ? 'uploads/avatars/' . $_SESSION['avatar'] : 'assets/images/default_avatar.jpg');
                                ?>
                                <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="rounded-circle" style="width: 25px; height: 25px; object-fit: cover;">
                                <span><?php echo e($_SESSION['user_name'] ?? $_SESSION['username']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2 rounded-3" aria-labelledby="userMenu">
                                <?php if (is_admin()): ?>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2 fw-semibold text-danger" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                                            <i class="bi bi-speedometer2 me-2"></i><?php echo __('nav_admin_dashboard'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>index.php">
                                            <i class="bi bi-globe me-2 text-primary"></i>View Website
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>admin/profile.php">
                                            <i class="bi bi-person me-2 text-success"></i><?php echo __('nav_my_profile'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>admin/settings.php">
                                            <i class="bi bi-gear me-2 text-secondary"></i><?php echo __('nav_settings'); ?>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/profile.php">
                                            <i class="bi bi-person me-2 text-primary"></i><?php echo __('nav_my_profile'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/order-history.php">
                                            <i class="bi bi-bag-check me-2 text-success"></i><?php echo __('nav_my_orders'); ?>
                                        </a>
                                    </li>
                                    <?php if (!is_admin()): ?>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/cart.php">
                                            <i class="bi bi-cart3 me-2 text-warning"></i><?php echo __('nav_my_cart'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/profile.php#addresses">
                                            <i class="bi bi-geo-alt me-2 text-info"></i><?php echo __('nav_delivery_address'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/wishlist.php">
                                            <i class="bi bi-heart me-2 text-danger"></i><?php echo __('nav_wishlist'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-2 py-2" href="<?php echo BASE_URL; ?>pages/customer/settings.php">
                                            <i class="bi bi-gear me-2 text-secondary"></i><?php echo __('nav_settings'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item rounded-2 py-2 text-muted" href="<?php echo BASE_URL; ?>logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i><?php echo __('nav_logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-dark rounded-pill px-4"><?php echo __('nav_login'); ?></a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-brand px-4"><?php echo __('nav_register'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages Container -->
    <div class="container mt-3">
        <?php display_flash(); ?>
    </div>