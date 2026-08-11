<?php
/**
 * Food Detail Page - Food Delivery System
 * Displays food item details, dynamic price multiplier widget, and add-to-cart operations.
 */


// Load minimal dependencies required for POST handling before rendering header
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/database/Database.php';
require_once __DIR__ . '/includes/functions/helpers.php';
require_once __DIR__ . '/includes/functions/auth.php';
require_once __DIR__ . '/includes/functions/cart_functions.php';

$pageTitle = "Food Details";

$db = Database::getInstance();

$foodId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($foodId <= 0) {
    set_flash('danger', 'Invalid Food Selection.');
    redirect(BASE_URL . 'index.php');
}

// Fetch Food Item details
$sql = "SELECT f.*, r.`name` AS `restaurant_name`, r.`id` AS `restaurant_id`, c.`name` AS `category_name`
        FROM `foods` f
        JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
        JOIN `categories` c ON f.`category_id` = c.`id`
        WHERE f.`id` = ? AND f.`is_available` = 1";
$food = $db->queryRow($sql, [$foodId]);

if (!$food) {
    set_flash('danger', 'Food item not found or is currently unavailable.');
    redirect(BASE_URL . 'index.php');
}

// Handle Add to Cart operations BEFORE output to allow header redirects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        // Fail silently and redirect back with no sensitive output
        set_flash('danger', 'Security error. Please try again.');
        redirect(BASE_URL . 'food-detail.php?id=' . $foodId);
    }

    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;

    // Require login for adding to cart
    if (!is_logged_in()) {
        set_flash('warning', 'Please login before adding items to your cart.');
        // Redirect to login with return URL
        $next = 'food-detail.php?id=' . $foodId;
        redirect(BASE_URL . 'login.php?next=' . urlencode($next));
    }

    try {
        add_to_cart($foodId, $qty);
        // Redirect back to the same page with a query flag so client JS shows a SweetAlert
        redirect(BASE_URL . 'food-detail.php?id=' . $foodId . '&added=1');
    } catch (Exception $e) {
        // Don't expose raw errors
        set_flash('danger', 'Something went wrong. Please try again.');
        redirect(BASE_URL . 'food-detail.php?id=' . $foodId);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <!-- Navigation Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-danger text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="restaurant-menu.php?id=<?php echo $food['restaurant_id']; ?>" class="text-danger text-decoration-none"><?php echo e($food['restaurant_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo e($food['name']); ?></li>
        </ol>
    </nav>
    
    <!-- Product Row -->
    <div class="row g-5">
        
        <!-- Food Product Image Column -->
        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden shadow-sm" style="max-height: 450px;">
                <?php 
                $img = image_url($food['image_path'] ?? '', 'assets/images/default_food.jpg');
                ?>
                <img src="<?php echo $img; ?>" alt="<?php echo e($food['name']); ?>" class="w-100 h-100" style="object-fit: cover; max-height: 450px;">
            </div>
        </div>
        
        <!-- Food Options & Order Form Column -->
        <div class="col-lg-6">
            <span class="badge bg-danger rounded-pill px-3 py-2 mb-3"><?php echo e($food['category_name']); ?></span>
            <h1 class="fw-bold text-dark mb-1"><?php echo e($food['name']); ?></h1>
            <p class="text-muted mb-4"><i class="bi bi-shop text-danger me-1"></i>Prepared by: <a href="restaurant-menu.php?id=<?php echo $food['restaurant_id']; ?>" class="text-danger fw-semibold text-decoration-none"><?php echo e($food['restaurant_name']); ?></a></p>
            
            <h3 class="food-price mb-4" id="food-unit-price" data-price="<?php echo $food['price']; ?>">
                <?php echo format_currency($food['price']); ?>
            </h3>
            
            <h5 class="fw-bold mb-2">Description</h5>
            <p class="text-muted mb-4"><?php echo e($food['description']); ?></p>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-light text-dark rounded-pill px-3 py-2"><i class="bi bi-star-fill text-warning me-1"></i><?php echo get_food_rating($food); ?>/5.0</span>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2"><i class="bi bi-clock-history text-danger me-1"></i>Ready in <?php echo get_food_delivery_time($food); ?></span>
            </div>
            
            <!-- Order config Form -->
            <form action="food-detail.php?id=<?php echo $foodId; ?>" method="POST" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="food_id" value="<?php echo $foodId; ?>">
                
                <div class="row align-items-center mb-4">
                    <div class="col-md-5">
                        <label for="food-qty" class="form-label fw-semibold">Quantity</label>
                        <input type="number" name="quantity" id="food-qty" class="form-control text-center py-2" value="1" min="1" max="50" required>
                    </div>
                    <div class="col-md-7 mt-3 mt-md-0 text-md-end">
                        <span class="small text-muted d-block">Estimated Subtotal</span>
                        <span class="fs-3 fw-bold text-dark" id="food-total-price"><?php echo format_currency($food['price']); ?></span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-brand w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-cart-plus-fill fs-5"></i>
                    <span class="fw-bold">Add to Order Basket</span>
                </button>
            </form>
            
            <!-- Quality Seals -->
            <div class="row g-3 mt-4 text-center">
                <div class="col-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <i class="bi bi-clock-history text-danger fs-4 d-block mb-1"></i>
                        <span class="small text-muted d-block" style="font-size: 0.75rem;">Ready in <?php echo get_food_delivery_time($food); ?></span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <i class="bi bi-box-seam text-success fs-4 d-block mb-1"></i>
                        <span class="small text-muted d-block" style="font-size: 0.75rem;">Freshly prepared</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 border rounded-3 bg-light">
                        <i class="bi bi-award-fill text-warning fs-4 d-block mb-1"></i>
                        <span class="small text-muted d-block" style="font-size: 0.75rem;">Rated <?php echo get_food_rating($food); ?>/5</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
