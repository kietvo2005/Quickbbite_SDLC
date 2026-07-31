<?php
/**
 * Homepage - Food Delivery System
 * Displays hero search, categories, popular dishes, latest additions, and active restaurants.
 */

$pageTitle = "Home";
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance();
$wishlistIds = [];
if (is_logged_in()) {
    $rows = $db->queryAll('SELECT food_id FROM `wishlist` WHERE user_id = ?', [(int) $_SESSION['user_id']]);
    $wishlistIds = array_column($rows, 'food_id');
}

// Parse filters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

$isFiltered = ($categoryId > 0 || !empty($searchQuery));
$searchResults = [];

if ($isFiltered) {
    // Build filter queries
    $sql = "SELECT f.*, r.`name` AS `restaurant_name` 
            FROM `foods` f
            JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
            WHERE f.`is_available` = 1";
    $params = [];
    
    if ($categoryId > 0) {
        $sql .= " AND f.`category_id` = ?";
        $params[] = $categoryId;
    }
    
    if (!empty($searchQuery)) {
        $sql .= " AND (f.`name` LIKE ? OR f.`description` LIKE ? OR r.`name` LIKE ?)";
        $searchParam = "%{$searchQuery}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $searchResults = $db->queryAll($sql, $params);
}

// Fetch all categories for filter buttons
$categories = $db->queryAll("SELECT * FROM `categories` ORDER BY `id` ASC");

// Fetch popular foods
$popularFoods = $db->queryAll("SELECT f.*, r.`name` AS `restaurant_name` 
                               FROM `foods` f 
                               JOIN `restaurants` r ON f.`restaurant_id` = r.`id` 
                               WHERE f.`is_popular` = 1 AND f.`is_available` = 1 
                               LIMIT 4");

// Fetch latest foods
$latestFoods = $db->queryAll("SELECT f.*, r.`name` AS `restaurant_name` 
                              FROM `foods` f 
                              JOIN `restaurants` r ON f.`restaurant_id` = r.`id` 
                              WHERE f.`is_latest` = 1 AND f.`is_available` = 1 
                              ORDER BY f.`id` DESC 
                              LIMIT 4");

// Fetch active restaurants
$restaurants = $db->queryAll("SELECT * FROM `restaurants` WHERE `status` = 'active' LIMIT 4");
?>

<!-- Hero Banner & Search Interface -->
<section class="hero-banner text-center d-flex align-items-center">
    <div class="container animate-fade-in-up">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (is_admin()): ?>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white text-dark mb-3 shadow-sm">
                        <i class="bi bi-shield-check text-danger"></i>
                        <span class="fw-semibold small"><?php echo __('home_admin_mode'); ?></span>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-brand px-4"><?php echo __('home_return_dashboard'); ?></a>
                    </div>
                <?php endif; ?>
                <h1 class="display-4 fw-extrabold mb-3"><?php echo __('home_hero_title'); ?></h1>
                <p class="lead mb-4 text-white-50"><?php echo __('home_hero_subtitle'); ?></p>
                
                <!-- Search bar form -->
                <form action="index.php" method="GET" class="p-2 bg-white rounded-pill shadow-lg d-flex align-items-center gap-2">
                    <i class="bi bi-search text-muted fs-4 ms-3"></i>
                    <input type="text" name="search" class="form-control border-0 bg-transparent py-2 shadow-none" placeholder="<?php echo __('home_search_placeholder'); ?>" value="<?php echo e($searchQuery); ?>">
                    <?php if ($categoryId > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-brand px-5 py-3"><?php echo __('home_search_btn'); ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Directory Content -->
<div class="container my-5">
    
    <!-- Category Icons Filter List -->
    <div class="mb-5">
        <h3 class="fw-bold mb-4"><?php echo __('home_browse_categories'); ?></h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
            <!-- All Filter -->
            <div class="col">
                <a href="index.php" class="category-card <?php echo ($categoryId === 0 && empty($searchQuery)) ? 'border border-danger border-2' : ''; ?>">
                    <i class="bi bi-grid-fill category-icon text-muted"></i>
                    <h6 class="fw-semibold mb-0"><?php echo __('home_all_foods'); ?></h6>
                </a>
            </div>
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="index.php?category=<?php echo $cat['id']; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" 
                       class="category-card <?php echo ($categoryId === (int)$cat['id']) ? 'border border-danger border-2' : ''; ?>">
                        <i class="bi bi-egg-fried category-icon"></i>
                        <h6 class="fw-semibold mb-0"><?php echo e($cat['name']); ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Conditional Filter Results OR Default Sections -->
    <?php if ($isFiltered): ?>
        <section class="mb-5 animate-fade-in-up">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><?php echo __('home_search_results'); ?></h3>
                <a href="index.php" class="btn btn-sm btn-outline-secondary"><?php echo __('home_clear_filters'); ?></a>
            </div>
            
            <?php if (empty($searchResults)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted"><?php echo __('home_no_items'); ?></h5>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($searchResults as $food): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="food-card food-card-clickable" data-detail-url="food-detail.php?id=<?php echo (int)$food['id']; ?>" tabindex="0" role="button" aria-label="View details for <?php echo e($food['name']); ?>">
                                <div class="food-img-container">
                                    <?php 
                                    $img = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg');
                                    ?>
                                    <img src="<?php echo $img; ?>" alt="<?php echo e($food['name']); ?>" class="food-img">
                                </div>
                                <div class="p-3">
                                    <p class="text-muted small mb-1"><i class="bi bi-shop me-1 text-danger"></i><?php echo e($food['restaurant_name']); ?></p>
                                    <h5 class="fw-bold mb-2"><?php echo e($food['name']); ?></h5>
                                    <p class="text-muted small text-truncate"><?php echo e($food['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="food-price"><?php echo format_currency($food['price']); ?></span>
                                        <a href="food-detail.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-brand"><?php echo __('home_order_now'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
    <?php else: ?>
        <!-- Section: Popular Foods -->
        <section class="mb-5">
            <h3 class="fw-bold mb-4"><?php echo __('home_popular_dishes'); ?></h3>
            <div class="row g-4">
                <?php foreach ($popularFoods as $food): ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="food-card food-card-clickable" data-detail-url="food-detail.php?id=<?php echo (int)$food['id']; ?>" tabindex="0" role="button" aria-label="View details for <?php echo e($food['name']); ?>">
                            <span class="food-badge">Popular</span>
                            <button type="button" class="wishlist-btn" aria-label="Add to wishlist" data-food-id="<?php echo (int) $food['id']; ?>">
                                <i class="bi <?php echo in_array($food['id'], $wishlistIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                            </button>
                            <div class="food-img-container">
                                <?php 
                                $img = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg');
                                ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo e($food['name']); ?>" class="food-img">
                            </div>
                            <div class="p-3">
                                <p class="text-muted small mb-1"><i class="bi bi-shop me-1 text-danger"></i><?php echo e($food['restaurant_name']); ?></p>
                                <h5 class="fw-bold mb-2 text-truncate"><?php echo e($food['name']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="rating-chip"><i class="bi bi-star-fill"></i> 4.8</span>
                                    <span class="delivery-chip"><i class="bi bi-clock-history"></i> 25 min</span>
                                </div>
                                <p class="text-muted small text-truncate"><?php echo e($food['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="food-price"><?php echo format_currency($food['price']); ?></span>
                                    <a href="food-detail.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-brand"><?php echo __('home_order_now'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section: Latest Additions -->
        <section class="mb-5">
            <h3 class="fw-bold mb-4"><?php echo __('home_latest_additions'); ?></h3>
            <div class="row g-4">
                <?php foreach ($latestFoods as $food): ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="food-card food-card-clickable" data-detail-url="food-detail.php?id=<?php echo (int)$food['id']; ?>" tabindex="0" role="button" aria-label="View details for <?php echo e($food['name']); ?>">
                            <span class="food-badge bg-success">New</span>
                            <button type="button" class="wishlist-btn" aria-label="Add to wishlist" data-food-id="<?php echo (int) $food['id']; ?>">
                                <i class="bi <?php echo in_array($food['id'], $wishlistIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                            </button>
                            <div class="food-img-container">
                                <?php 
                                $img = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg');
                                ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo e($food['name']); ?>" class="food-img">
                            </div>
                            <div class="p-3">
                                <p class="text-muted small mb-1"><i class="bi bi-shop me-1 text-danger"></i><?php echo e($food['restaurant_name']); ?></p>
                                <h5 class="fw-bold mb-2 text-truncate"><?php echo e($food['name']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="rating-chip"><i class="bi bi-star-fill"></i> 4.7</span>
                                    <span class="delivery-chip"><i class="bi bi-clock-history"></i> 18 min</span>
                                </div>
                                <p class="text-muted small text-truncate"><?php echo e($food['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="food-price"><?php echo format_currency($food['price']); ?></span>
                                    <a href="food-detail.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-brand"><?php echo __('home_order_now'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section: Featured Restaurants -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><?php echo __('home_featured_restaurants'); ?></h3>
                <a href="restaurant.php" class="btn btn-brand-outline"><?php echo __('home_view_all'); ?></a>
            </div>
            <div class="row g-4">
                <?php foreach ($restaurants as $rest): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div style="height: 180px; overflow: hidden;">
                                <?php 
                                $restImg = image_url($rest['image_path'] ?? '', 'assets/images/default_restaurant.jpg');
                                ?>
                                <img src="<?php echo $restImg; ?>" alt="<?php echo e($rest['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                            </div>
                            <div class="card-body p-3">
                                <h5 class="fw-bold mb-1"><?php echo e($rest['name']); ?></h5>
                                <span class="badge bg-light text-dark mb-2"><?php echo e($rest['cuisine_type']); ?></span>
                                <p class="card-text text-muted small text-truncate"><?php echo e($rest['description']); ?></p>
                                <div class="d-flex align-items-center gap-1 text-muted small mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger"></i>
                                    <span class="text-truncate"><?php echo e($rest['address']); ?></span>
                                </div>
                                <a href="restaurant-menu.php?id=<?php echo $rest['id']; ?>" class="btn btn-sm btn-brand w-100"><?php echo __('home_explore_menu'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const foodId = btn.dataset.foodId;
            if (!foodId) return;

            try {
                const res = await fetch(window.BASE_URL + 'pages/customer/wishlist-toggle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ food_id: foodId })
                });
                const data = await res.json();

                if (!data.success) {
                    if (res.status === 401) window.location.href = window.BASE_URL + 'login.php';
                    return;
                }

                const icon = btn.querySelector('i');
                if (data.action === 'added') {
                    icon.classList.replace('bi-heart', 'bi-heart-fill');
                    icon.classList.add('text-danger');
                } else {
                    icon.classList.replace('bi-heart-fill', 'bi-heart');
                    icon.classList.remove('text-danger');
                }
            } catch (err) {
                // Silent fail — network hiccup, user can retry.
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>