<?php
/**
 * Checkout Page - Food Delivery System
 * Collects delivery addresses, billing choices, and commits the transaction to the database.
 */

$pageTitle = "Checkout";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/cart_functions.php';
require_once __DIR__ . '/../../includes/functions/order_functions.php';

require_customer();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$user = get_logged_in_user();
$customerName = $user['name'] ?? $user['username'] ?? '';
$customerPhone = $user['phone'] ?? '';

// Check cart items
$cartItems = get_cart_items();
$cartTotal = get_cart_total();
if (empty($cartItems)) {
    set_flash('warning', __('checkout_error_empty_cart'));
    redirect(BASE_URL . 'pages/customer/cart.php');
}

// Fetch saved addresses
$addresses = $db->queryAll("SELECT * FROM `addresses` WHERE `user_id` = ? ORDER BY `is_default` DESC", [$userId]);

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $paymentMethod = sanitize_input($_POST['payment_method'] ?? 'cod');
    $addressOption = sanitize_input($_POST['address_option'] ?? 'saved');
    $customerName = sanitize_input($_POST['customer_name'] ?? '');
    $customerPhone = sanitize_input($_POST['customer_phone'] ?? '');
    $orderNotes = sanitize_input($_POST['order_notes'] ?? '');
    $deliveryAddress = '';
    
    $errors = [];
    
    if (empty($customerName)) {
        $errors[] = __('checkout_error_name');
    }
    
    if (!preg_match('/^[0-9+\-\s()]{7,15}$/', $customerPhone)) {
        $errors[] = __('checkout_error_phone');
    }
    
    // Address selection validation
    if ($addressOption === 'saved') {
        $addressId = isset($_POST['saved_address_id']) ? (int)$_POST['saved_address_id'] : 0;
        if ($addressId <= 0) {
            $errors[] = __('checkout_error_select_address');
        } else {
            $addr = $db->queryRow("SELECT * FROM `addresses` WHERE `id` = ? AND `user_id` = ?", [$addressId, $userId]);
            if ($addr) {
                $deliveryAddress = $addr['address_line1'];
                if (!empty($addr['address_line2'])) $deliveryAddress .= ', ' . $addr['address_line2'];
                $deliveryAddress .= ', ' . $addr['city'] . ', ' . $addr['state'] . ' ' . $addr['postal_code'];
            } else {
                $errors[] = __('checkout_error_invalid_address');
            }
        }
    } else {
        // Custom Address Input
        $line1 = sanitize_input($_POST['address_line1'] ?? '');
        $line2 = sanitize_input($_POST['address_line2'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');
        $state = sanitize_input($_POST['state'] ?? '');
        $zip = sanitize_input($_POST['postal_code'] ?? '');
        
        if (empty($line1) || empty($city) || empty($state) || empty($zip)) {
            $errors[] = __('checkout_error_complete_address');
        } else {
            $deliveryAddress = $line1;
            if (!empty($line2)) $deliveryAddress .= ', ' . $line2;
            $deliveryAddress .= ', ' . $city . ', ' . $state . ' ' . $zip;
            
            // Optionally save this address to profile if checked
            if (isset($_POST['save_new_address'])) {
                $db->execute("INSERT INTO `addresses` (`user_id`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `is_default`) VALUES (?, ?, ?, ?, ?, ?, 0)", [
                    $userId, $line1, $line2, $city, $state, $zip
                ]);
            }
        }
    }
    
    if (empty($errors)) {
        try {
            require_once __DIR__ . '/../../includes/functions/order_functions.php';
            $orderId = place_order($userId, $deliveryAddress, $paymentMethod, $customerName, $customerPhone, $orderNotes);
            
            if ($paymentMethod === 'bank_transfer') {
                set_flash('info', __('checkout_success_bank_transfer'));
                redirect(BASE_URL . 'pages/customer/bank-payment.php?id=' . $orderId);
            }

            set_flash('success', __('checkout_success'));
            redirect(BASE_URL . 'pages/customer/order-success.php?id=' . $orderId);
        } catch (Exception $e) {
            set_flash('danger', __('checkout_error_failed') . $e->getMessage());
        }
    } else {
        foreach ($errors as $err) {
            set_flash('danger', $err);
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h2 class="fw-bold mb-0"><i class="bi bi-wallet2 text-danger me-2"></i><?php echo __('checkout_title'); ?></h2>
        <a href="cart.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i><?php echo __('checkout_back_to_cart'); ?>
        </a>
    </div>
    
    <form action="checkout.php" method="POST" class="row g-4" data-order-form>
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="place_order">
        
        <!-- Left: Address & Billing info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-person-lines-fill text-danger me-2"></i><?php echo __('checkout_customer_info'); ?></h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label fw-semibold small"><?php echo __('checkout_full_name'); ?></label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control form-control-sm" value="<?php echo e($customerName); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label fw-semibold small"><?php echo __('checkout_phone'); ?></label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control form-control-sm" value="<?php echo e($customerPhone); ?>" placeholder="e.g. 0123456789" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="order_notes" class="form-label fw-semibold small"><?php echo __('checkout_order_notes'); ?></label>
                    <textarea name="order_notes" id="order_notes" rows="3" class="form-control form-control-sm" placeholder="<?php echo __('checkout_order_notes_placeholder'); ?>"></textarea>
                </div>
            </div>
            
            <!-- 1. Address Section -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-geo-alt text-danger me-2"></i><?php echo __('checkout_delivery_address'); ?></h5>
                
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input type="radio" name="address_option" id="addr_saved" class="form-check-input" value="saved" checked <?php echo empty($addresses) ? 'disabled' : ''; ?>>
                        <label for="addr_saved" class="form-check-label fw-semibold"><?php echo __('checkout_saved_address'); ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="address_option" id="addr_new" class="form-check-input" value="new" <?php echo empty($addresses) ? 'checked' : ''; ?>>
                        <label for="addr_new" class="form-check-label fw-semibold"><?php echo __('checkout_new_address'); ?></label>
                    </div>
                </div>
                
                <!-- Saved Address Select Grid -->
                <div id="saved_address_section" class="<?php echo empty($addresses) ? 'd-none' : ''; ?>">
                    <div class="row g-3">
                        <?php foreach ($addresses as $index => $addr): ?>
                            <div class="col-md-6">
                                <label class="border rounded-4 p-3 bg-light w-100 d-block cursor-pointer position-relative">
                                    <div class="form-check mb-2">
                                        <input type="radio" name="saved_address_id" class="form-check-input" value="<?php echo $addr['id']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                        <span class="fw-bold small text-dark"><?php echo __('checkout_address_details'); ?></span>
                                    </div>
                                    <p class="small text-muted mb-0"><?php echo e($addr['address_line1']); ?></p>
                                    <p class="small text-muted mb-0"><?php echo e($addr['city']); ?>, <?php echo e($addr['state']); ?> <?php echo e($addr['postal_code']); ?></p>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Custom Address inputs -->
                <div id="new_address_section" class="<?php echo !empty($addresses) ? 'd-none' : ''; ?> p-3 border rounded-4 bg-light">
                    <h6 class="fw-bold mb-3"><?php echo __('checkout_add_coordinates'); ?></h6>
                    <div class="mb-3">
                        <label for="address_line1" class="form-label fw-semibold small"><?php echo __('checkout_address_line1'); ?></label>
                        <input type="text" name="address_line1" id="address_line1" class="form-control form-control-sm" placeholder="<?php echo __('checkout_address_line1_placeholder'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address_line2" class="form-label fw-semibold small"><?php echo __('checkout_address_line2'); ?></label>
                        <input type="text" name="address_line2" id="address_line2" class="form-control form-control-sm" placeholder="<?php echo __('checkout_address_line2_placeholder'); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label fw-semibold small"><?php echo __('checkout_city'); ?></label>
                            <input type="text" name="city" id="city" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label fw-semibold small"><?php echo __('checkout_state'); ?></label>
                            <input type="text" name="state" id="state" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label fw-semibold small"><?php echo __('checkout_postal_code'); ?></label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="save_new_address" id="save_new_address" class="form-check-input" value="1">
                        <label for="save_new_address" class="form-check-label text-muted small"><?php echo __('checkout_save_address'); ?></label>
                    </div>
                </div>
            </div>
            
            <!-- 2. Payment Section -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4"><i class="bi bi-credit-card text-danger me-2"></i><?php echo __('checkout_payment_selection'); ?></h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="border rounded-4 p-3 bg-light w-100 d-block cursor-pointer">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="radio" name="payment_method" class="form-check-input" value="cod" checked>
                                <span class="fw-bold"><i class="bi bi-cash-stack text-success fs-5 me-1"></i><?php echo __('checkout_cod'); ?></span>
                            </div>
                            <span class="small text-muted d-block mt-2"><?php echo __('checkout_cod_desc'); ?></span>
                        </label>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="border rounded-4 p-3 bg-light w-100 d-block cursor-pointer">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="radio" name="payment_method" class="form-check-input" value="bank_transfer">
                                <span class="fw-bold"><i class="bi bi-bank text-info fs-5 me-1"></i><?php echo __('checkout_bank_transfer'); ?></span>
                            </div>
                            <span class="small text-muted d-block mt-2"><?php echo __('checkout_bank_transfer_desc'); ?></span>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="border rounded-4 p-3 bg-light w-100 d-block cursor-pointer">
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="radio" name="payment_method" class="form-check-input" value="card">
                                <span class="fw-bold"><i class="bi bi-credit-card text-primary fs-5 me-1"></i><?php echo __('checkout_card'); ?></span>
                            </div>
                            <span class="small text-muted d-block mt-2"><?php echo __('checkout_card_desc'); ?></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right: Basket summaries -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white position-sticky" style="top: 100px;">
                <h5 class="fw-bold mb-3"><?php echo __('checkout_invoice_details'); ?></h5>
                
                <!-- Display Cart items checklist -->
                <div class="mb-4" style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div class="small">
                                <span class="fw-bold text-dark d-block"><?php echo e($item['name']); ?></span>
                                <span class="text-muted text-xs"><?php echo $item['quantity']; ?>x &bull; <?php echo e($item['restaurant_name']); ?></span>
                            </div>
                            <span class="small fw-semibold text-dark"><?php echo format_currency($item['subtotal']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span><?php echo __('checkout_subtotal'); ?></span>
                    <span><?php echo format_currency($cartTotal); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span><?php echo __('cart_delivery_fee'); ?></span>
                    <span class="text-success fw-semibold"><?php echo __('cart_free'); ?></span>
                </div>
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="small text-muted"><?php echo __('checkout_estimated_delivery'); ?></div>
                    <div class="fw-semibold text-dark">35–45 min</div>
                </div>
                
                <hr class="my-3">
                
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold"><?php echo __('checkout_grand_total'); ?></span>
                    <span class="fw-bold text-danger fs-4"><?php echo format_currency($cartTotal); ?></span>
                </div>
                
                <button type="submit" class="btn btn-brand w-100 py-3 fw-bold shadow"><?php echo __('checkout_submit_order'); ?></button>
                <div class="small text-muted mt-3 text-center"><?php echo __('checkout_secure_note'); ?></div>
            </div>
        </div>
    </form>
</div>

<script>
    // Handle switching between saved addresses and new address input views
    document.addEventListener("DOMContentLoaded", () => {
        const optionSaved = document.getElementById("addr_saved");
        const optionNew = document.getElementById("addr_new");
        const sectionSaved = document.getElementById("saved_address_section");
        const sectionNew = document.getElementById("new_address_section");
        
        const inputs = sectionNew.querySelectorAll("input");

        function toggleSections() {
            if (optionSaved && optionSaved.checked) {
                sectionSaved.classList.remove("d-none");
                sectionNew.classList.add("d-none");
                inputs.forEach(el => el.removeAttribute("required"));
            } else if (optionNew && optionNew.checked) {
                sectionSaved.classList.add("d-none");
                sectionNew.classList.remove("d-none");
                inputs.forEach(el => {
                    if (el.id !== "address_line2" && el.id !== "save_new_address") {
                        el.setAttribute("required", "");
                    }
                });
            }
        }

        if (optionSaved) optionSaved.addEventListener("change", toggleSections);
        if (optionNew) optionNew.addEventListener("change", toggleSections);
        
        toggleSections(); // initial trigger
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>