<?php
/**
 * Restaurant Directory Page - Food Delivery System
 * Displays a list of all active restaurants, searchable and filterable.
 */

$pageTitle = "Restaurants";
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance();

// Retrieve filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cuisine = isset($_GET['cuisine']) ? trim($_GET['cuisine']) : '';

// Build Query
$sql = "SELECT * FROM `restaurants` WHERE `status` = 'active'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (`name` LIKE ? OR `description` LIKE ? OR `cuisine_type` LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($cuisine)) {
    $sql .= " AND `cuisine_type` = ?";
    $params[] = $cuisine;
}

$sql .= " ORDER BY `name` ASC";
$restaurantsList = $db->queryAll($sql, $params);

// Fetch unique cuisines for filters
$cuisines = $db->queryAll("SELECT DISTINCT `cuisine_type` FROM `restaurants` WHERE `status` = 'active'");
?>

<div class="container my-5 animate-fade-in-up">
    <!-- Header row -->
    <div class="row align-items-center mb-5 g-3">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1 text-dark"><?php echo __('restaurant_title'); ?></h2>
            <p class="text-muted mb-0"><?php echo __('restaurant_subtitle'); ?></p>
        </div>
        <div class="col-md-6">
            <!-- Search + Filter triggers -->
            <form action="restaurant.php" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control rounded-pill px-3" placeholder="<?php echo __('restaurant_search_placeholder'); ?>" value="<?php echo e($search); ?>">
                <?php if (!empty($cuisine)): ?>
                    <input type="hidden" name="cuisine" value="<?php echo e($cuisine); ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-brand px-4"><?php echo __('restaurant_search_btn'); ?></button>
            </form>
        </div>
    </div>

    <!-- Cuisines quick filters -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="restaurant.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
           class="btn btn-sm <?php echo empty($cuisine) ? 'btn-dark' : 'btn-outline-dark'; ?> rounded-pill px-3">
            <?php echo __('restaurant_all_cuisines'); ?>
        </a>
        <?php foreach ($cuisines as $c): ?>
            <?php if (!empty($c['cuisine_type'])): ?>
                <a href="restaurant.php?cuisine=<?php echo urlencode($c['cuisine_type']); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   class="btn btn-sm <?php echo ($cuisine === $c['cuisine_type']) ? 'btn-danger' : 'btn-outline-dark'; ?> rounded-pill px-3">
                    <?php echo e($c['cuisine_type']); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Restaurants List -->
    <?php if (empty($restaurantsList)): ?>
        <div class="text-center py-5">
            <i class="bi bi-shop-window text-muted" style="font-size: 4rem;"></i>
            <h4 class="fw-bold text-muted mt-3"><?php echo __('restaurant_none_found'); ?></h4>
            <p class="text-muted"><?php echo __('restaurant_none_found_desc'); ?></p>
            <a href="restaurant.php" class="btn btn-brand-outline mt-2"><?php echo __('restaurant_clear_filters'); ?></a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($restaurantsList as $restaurant): ?>
                <div class="col-md-6 col-lg-3 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden restaurant-card">
                        <div style="height: 180px; overflow: hidden; position: relative;">
                            <?php 
                            $restImg = image_url($restaurant['image_path'] ?? '', 'assets/images/default_restaurant.jpg');
                            ?>
                            <img src="<?php echo $restImg; ?>" alt="<?php echo e($restaurant['name']); ?>" class="w-100 h-100 food-img" style="object-fit: cover;">
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-1 text-dark"><?php echo e($restaurant['name']); ?></h5>
                                <span class="status-chip"><?php echo __('restaurant_open'); ?></span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-light text-dark"><?php echo e($restaurant['cuisine_type']); ?></span>
                                <span class="badge bg-danger-subtle text-danger"><?php echo __('restaurant_free_delivery'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-3">
                                <span><i class="bi bi-star-fill text-warning me-1"></i><?php echo get_restaurant_rating($restaurant); ?></span>
                                <span><i class="bi bi-clock-history me-1"></i><?php echo get_restaurant_delivery($restaurant); ?></span>
                            </div>
                            <p class="card-text text-muted small mb-4 flex-grow-1 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo e($restaurant['description']); ?>
                            </p>
                            <a href="restaurant-menu.php?id=<?php echo $restaurant['id']; ?>" class="btn btn-brand btn-sm w-100 py-2"><?php echo __('restaurant_explore_menu'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>