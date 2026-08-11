<?php
/**
 * Shopping Cart Page - QuickBite System
 * Lists selected foods, item counts, totals, and controls for modifications.
 */

$pageTitle = "My Cart";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/functions/cart_functions.php';

require_customer();

// Handle Quantity Updates (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $food_id = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($food_id > 0 && $qty > 0) {
        update_cart_qty($food_id, $qty);
        set_flash('success', __('cart_qty_updated'));
    }
    redirect(BASE_URL . 'pages/customer/cart.php');
}

// Handle Item Deletions (GET)
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $food_id = (int)$_GET['id'];
    if ($food_id > 0) {
        remove_from_cart($food_id);
        set_flash('info', __('cart_item_removed'));
    }
    redirect(BASE_URL . 'pages/customer/cart.php');
}

// Fetch all items currently in cart
$cartItems = get_cart_items();
$cartTotal = get_cart_total();
$deliveryFee = 0.0;
$discount = 0.0;
$tax = 0.0;
$grandTotal = $cartTotal + $deliveryFee - $discount + $tax;

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <h2 class="fw-bold mb-4"><i class="bi bi-cart3 text-danger me-2"></i><?php echo __('cart_title'); ?></h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="mb-4">
                <i class="bi bi-basket text-muted" style="font-size: 5rem;"></i>
            </div>
            <h4 class="fw-bold text-dark"><?php echo __('cart_empty_title'); ?></h4>
            <p class="text-muted mb-4"><?php echo __('cart_empty_desc'); ?></p>
            <div>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-brand px-5 py-3"><?php echo __('cart_explore_menu'); ?></a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Left Column: Basket Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col" style="border: none;"><?php echo __('cart_item_details'); ?></th>
                                    <th scope="col" style="border: none;" class="text-center"><?php echo __('cart_quantity'); ?></th>
                                    <th scope="col" style="border: none;" class="text-end"><?php echo __('cart_subtotal'); ?></th>
                                    <th scope="col" style="border: none;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <!-- Item Info -->
                                        <td class="py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 overflow-hidden" style="width: 70px; height: 70px;">
                                                    <?php 
                                                    $foodImg = image_url($item['image_path'] ?? '', 'assets/images/default_food.jpg');
                                                    ?>
                                                    <img src="<?php echo $foodImg; ?>" alt="<?php echo e($item['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark"><?php echo e($item['name']); ?></h6>
                                                    <span class="text-muted small d-block"><?php echo e($item['restaurant_name']); ?></span>
                                                    <span class="text-danger fw-semibold small"><?php echo format_currency($item['price']); ?> <?php echo __('cart_each'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Quantity Form Modifier -->
                                        <td class="py-3 text-center" style="max-width: 180px;">
                                            <form action="cart.php" method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="action" value="update_quantity">
                                                <input type="hidden" name="food_id" value="<?php echo $item['food_id']; ?>">
                                                
                                                <button type="button" class="btn btn-sm btn-light border-0 shadow-sm qty-btn" data-target="qty-<?php echo $item['food_id']; ?>" data-action="minus">-</button>
                                                <input type="number" id="qty-<?php echo $item['food_id']; ?>" name="quantity" class="form-control form-control-sm text-center bg-light border-0" value="<?php echo $item['quantity']; ?>" min="1" max="50" style="width: 60px;" required>
                                                <button type="button" class="btn btn-sm btn-light border-0 shadow-sm qty-btn" data-target="qty-<?php echo $item['food_id']; ?>" data-action="plus">+</button>
                                                <button type="submit" class="btn btn-sm btn-light border-0 shadow-sm"><i class="bi bi-arrow-repeat text-success"></i></button>
                                            </form>
                                        </td>
                                        
                                        <!-- Item subtotal -->
                                        <td class="py-3 text-end fw-bold text-dark">
                                            <?php echo format_currency($item['subtotal']); ?>
                                        </td>
                                        
                                        <!-- Removal Link -->
                                        <td class="py-3 text-end">
                                            <a href="cart.php?action=remove&id=<?php echo $item['food_id']; ?>" class="btn btn-link text-danger p-0" onclick="return confirm('<?php echo __('cart_remove_confirm'); ?>');">
                                                <i class="bi bi-trash3 fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Cart Summaries -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-4 text-dark"><?php echo __('cart_order_tally'); ?></h5>
                    
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span><?php echo __('cart_items_subtotal'); ?></span>
                        <span><?php echo format_currency($cartTotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span><?php echo __('cart_delivery_fee'); ?></span>
                        <span class="text-success"><?php echo __('cart_free'); ?></span>
                    </div>
                    <?php if (!empty($cartItems)) : $firstItem = $cartItems[0]; ?>
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="bi bi-clock-history me-1"></i>Estimated arrival for <?php echo e($firstItem['name']); ?>: <?php echo get_food_delivery_time($firstItem); ?>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span><?php echo __('cart_discount'); ?></span>
                        <span><?php echo format_currency($discount); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span><?php echo __('cart_tax'); ?></span>
                        <span><?php echo format_currency($tax); ?></span>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold"><?php echo __('cart_estimated_total'); ?></span>
                        <span class="fw-bold text-danger fs-4"><?php echo format_currency($grandTotal); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-secondary w-100 py-2 mb-2"><?php echo __('cart_continue_shopping'); ?></a>
                    
                    <?php if (is_logged_in()): ?>
                        <?php if (is_customer()): ?>
                            <a href="<?php echo BASE_URL; ?>pages/customer/checkout.php" class="btn btn-brand w-100 py-3 fw-bold shadow"><?php echo __('cart_proceed_checkout'); ?></a>
                        <?php else: ?>
                            <div class="alert alert-warning small text-center mb-0"><?php echo __('cart_admin_warning'); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center">
                            <p class="small text-muted mb-3"><?php echo __('cart_login_required'); ?></p>
                            <div class="d-grid gap-2">
                                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-dark rounded-pill py-2"><?php echo __('cart_sign_in_to_order'); ?></a>
                                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-brand rounded-pill py-2"><?php echo __('cart_create_account'); ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>