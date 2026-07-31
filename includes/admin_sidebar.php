<?php
/**
 * Admin Panel Sidebar Template
 * Unified menu navigation for admin panel sections.
 */

require_once __DIR__ . '/functions/auth.php';

// Safe check: deny if not admin
require_admin();

$activePage = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="col-lg-3 mb-4">
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="text-center py-3">
            <i class="bi bi-speedometer2 text-danger fs-1"></i>
            <h5 class="fw-bold mb-0 mt-2">Control Center</h5>
            <span class="text-muted small">System Operations</span>
        </div>
        <hr class="my-3">
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-link text-dark <?php echo $activePage === 'dashboard.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-graph-up me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/users.php" class="nav-link text-dark <?php echo $activePage === 'users.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-people me-2"></i>Users
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/restaurants.php" class="nav-link text-dark <?php echo $activePage === 'restaurants.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-shop me-2"></i>Restaurants
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/categories.php" class="nav-link text-dark <?php echo $activePage === 'categories.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/foods.php" class="nav-link text-dark <?php echo $activePage === 'foods.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-egg-fried me-2"></i>Foods
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/orders.php" class="nav-link text-dark <?php echo $activePage === 'orders.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-cart-check me-2"></i>Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/payments.php" class="nav-link text-dark <?php echo $activePage === 'payments.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-credit-card me-2"></i>Payments
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/reviews.php" class="nav-link text-dark <?php echo $activePage === 'reviews.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-star me-2"></i>Reviews
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/contacts.php" class="nav-link text-dark <?php echo $activePage === 'contacts.php' || $activePage === 'messages.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-chat-left-dots me-2"></i>Contact Messages
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/reports.php" class="nav-link text-dark <?php echo $activePage === 'reports.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-bar-chart-line me-2"></i>Reports
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>admin/settings.php" class="nav-link text-dark <?php echo $activePage === 'settings.php' ? 'bg-danger text-white' : ''; ?>">
                    <i class="bi bi-gear me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item mt-2">
                <a href="<?php echo BASE_URL; ?>admin/logout.php" class="nav-link text-dark">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>
