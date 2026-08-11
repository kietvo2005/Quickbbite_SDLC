<?php
/**
 * Detailed Order Overview & Receipt Page - QuickBite
 * Displays full order information, status workflow, invoice actions, and admin controls.
 */

$pageTitle = "Order Details";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/cart_functions.php';

require_customer();

$db = Database::getInstance();
$userId = (int)($_SESSION['user_id'] ?? 0);
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    require_once __DIR__ . '/../../404.php';
    exit;
}

$order = $db->queryRow(
    "SELECT o.*, u.`name`, u.`username`, u.`email`, u.`phone`
     FROM `orders` o
     JOIN `users` u ON o.`user_id` = u.`id`
     WHERE o.`id` = ?",
    [$orderId]
);

if (!$order) {
    require_once __DIR__ . '/../../404.php';
    exit;
}

if (!is_admin() && (int)$order['user_id'] !== $userId) {
    require_once __DIR__ . '/../../403.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error: CSRF verification failed.');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
        if ($order['status'] === 'pending') {
            $db->execute("UPDATE `orders` SET `status` = 'cancelled' WHERE `id` = ?", [$orderId]);
            set_flash('info', 'Your order has been cancelled.');
            redirect(BASE_URL . 'pages/customer/order-detail.php?id=' . $orderId);
        }
        set_flash('danger', 'Only pending orders can be cancelled.');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reorder_items') {
        $reorderItems = $db->queryAll("SELECT `food_id`, `quantity` FROM `order_items` WHERE `order_id` = ?", [$orderId]);
        foreach ($reorderItems as $item) {
            add_to_cart((int)$item['food_id'], (int)$item['quantity']);
        }
        set_flash('success', 'Your previous order has been added to the cart.');
        redirect(BASE_URL . 'pages/customer/cart.php');
    } elseif (is_admin() && isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $newStatus = sanitize_input($_POST['order_status'] ?? 'pending');
        $allowedStatuses = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
        if (in_array($newStatus, $allowedStatuses, true)) {
            $db->execute("UPDATE `orders` SET `status` = ? WHERE `id` = ?", [$newStatus, $orderId]);
            set_flash('success', 'Order status updated successfully.');
            redirect(BASE_URL . 'pages/customer/order-detail.php?id=' . $orderId);
        }
    } elseif (is_admin() && isset($_POST['action']) && $_POST['action'] === 'save_note') {
        $note = sanitize_input($_POST['internal_note'] ?? '');
        $_SESSION['internal_notes'][$orderId] = $note;
        set_flash('success', 'Internal note saved.');
        redirect(BASE_URL . 'pages/customer/order-detail.php?id=' . $orderId);
    }
}

$items = $db->queryAll(
    "SELECT oi.`quantity`, oi.`price`, f.`name`, f.`image_path`, r.`name` AS `restaurant_name`
     FROM `order_items` oi
     JOIN `foods` f ON oi.`food_id` = f.`id`
     JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
     WHERE oi.`order_id` = ?",
    [$orderId]
);

$restaurantNames = $db->queryRow(
    "SELECT GROUP_CONCAT(DISTINCT r.`name` ORDER BY r.`name` SEPARATOR ', ') AS `restaurant_names`
     FROM `order_items` oi
     JOIN `foods` f ON oi.`food_id` = f.`id`
     JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
     WHERE oi.`order_id` = ?",
    [$orderId]
);

$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$deliveryFee = 0.0;
$discount = 0.0;
$coupon = '—';
$tax = 0.0;
$grandTotal = (float)$order['total_amount'];

$status = $order['status'];
$timelineSteps = [
    ['key' => 'pending', 'label' => 'Pending', 'icon' => 'receipt'],
    ['key' => 'preparing', 'label' => 'Confirmed', 'icon' => 'egg-fried'],
    ['key' => 'out_for_delivery', 'label' => 'Preparing', 'icon' => 'basket2'],
    ['key' => 'delivered', 'label' => 'Out for Delivery', 'icon' => 'bicycle'],
    ['key' => 'completed', 'label' => 'Delivered', 'icon' => 'house-check']
];

$currentStep = 1;
if ($status === 'preparing') {
    $currentStep = 2;
} elseif ($status === 'out_for_delivery') {
    $currentStep = 4;
} elseif ($status === 'delivered') {
    $currentStep = 5;
} elseif ($status === 'cancelled') {
    $currentStep = 0;
}

$estimatedTime = '35–45 min';
if ($status === 'preparing') {
    $estimatedTime = '25–35 min';
} elseif ($status === 'out_for_delivery') {
    $estimatedTime = '10–15 min';
} elseif ($status === 'delivered') {
    $estimatedTime = 'Delivered';
} elseif ($status === 'cancelled') {
    $estimatedTime = 'Cancelled';
}

$internalNote = $_SESSION['internal_notes'][$orderId] ?? 'No special instructions provided.';
$isAdmin = is_admin();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container my-5 animate-fade-in-up">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <a href="<?php echo BASE_URL; ?>pages/customer/order-history.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>Back to Order History
        </a>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </a>
            <a href="<?php echo BASE_URL; ?>invoice.php?id=<?php echo $orderId; ?>&download=1" target="_blank" class="btn btn-sm btn-brand rounded-pill px-3">
                <i class="bi bi-download me-1"></i>Download Invoice
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <p class="text-muted small mb-1">Order Number</p>
                        <h3 class="fw-bold text-dark mb-1">#<?php echo $order['id']; ?></h3>
                        <p class="text-muted mb-0">Placed on <?php echo date('M d, Y &bull; h:i A', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="text-end">
                        <?php
                        $statusBadge = 'bg-secondary';
                        if ($status === 'pending') { $statusBadge = 'bg-warning text-dark'; }
                        elseif ($status === 'preparing') { $statusBadge = 'bg-info text-dark'; }
                        elseif ($status === 'out_for_delivery') { $statusBadge = 'bg-primary'; }
                        elseif ($status === 'delivered') { $statusBadge = 'bg-success'; }
                        elseif ($status === 'cancelled') { $statusBadge = 'bg-danger'; }
                        ?>
                        <span class="badge rounded-pill <?php echo $statusBadge; ?> px-3 py-2 text-uppercase"><?php echo str_replace('_', ' ', $status); ?></span>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted mb-1">Payment Method</div>
                            <div class="fw-semibold text-dark text-uppercase"><?php echo e($order['payment_method']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted mb-1">Estimated Delivery</div>
                            <div class="fw-semibold text-dark"><?php echo $estimatedTime; ?></div>
                        </div>
                    </div>
                </div>

                <?php if ($status === 'cancelled'): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle-fill me-2"></i>This order was cancelled and can no longer be updated.
                    </div>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($timelineSteps as $index => $step): ?>
                            <div class="col-6 col-md-2-4">
                                <div class="track-step <?php echo $currentStep >= ($index + 1) ? 'active' : ''; ?>">
                                    <div class="icon-container"><i class="bi bi-<?php echo $step['icon']; ?>"></i></div>
                                    <span class="small fw-semibold d-block mt-2"><?php echo e($step['label']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <h5 class="fw-bold mb-0"><i class="bi bi-card-list text-danger me-2"></i>Order Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="window.location.href='<?php echo BASE_URL; ?>pages/customer/cart.php'">
                        <i class="bi bi-cart-plus me-1"></i>Go to Cart
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th scope="col" style="border: none;">Food</th>
                                <th scope="col" style="border: none;" class="text-center">Qty</th>
                                <th scope="col" style="border: none;" class="text-end">Unit Price</th>
                                <th scope="col" style="border: none;" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-3 overflow-hidden" style="width: 60px; height: 60px;">
                                                <?php $img = image_url($item['image_path'] ?? '', 'assets/images/default_food.jpg'); ?>
                                                <img src="<?php echo $img; ?>" alt="<?php echo e($item['name']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1"><?php echo e($item['name']); ?></h6>
                                                <span class="text-muted small"><?php echo e($item['restaurant_name']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center"><?php echo (int)$item['quantity']; ?></td>
                                    <td class="py-3 text-end"><?php echo format_currency($item['price']); ?></td>
                                    <td class="py-3 text-end fw-bold text-dark"><?php echo format_currency((float)$item['price'] * (int)$item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-lines-fill text-danger me-2"></i>Customer Details</h5>
                <div class="small text-muted mb-2"><strong>Name:</strong> <?php echo e($order['name'] ?: $order['username']); ?></div>
                <div class="small text-muted mb-2"><strong>Phone:</strong> <?php echo e($order['phone'] ?? 'Not provided'); ?></div>
                <div class="small text-muted mb-2"><strong>Email:</strong> <?php echo e($order['email']); ?></div>
                <div class="small text-muted mb-2"><strong>Address:</strong> <?php echo e($order['delivery_address']); ?></div>
                <div class="small text-muted"><strong>Payment Status:</strong> <span class="fw-semibold text-<?php echo $order['payment_status'] === 'paid' ? 'success' : ($order['payment_status'] === 'failed' ? 'danger' : 'warning'); ?>"><?php echo e(ucfirst($order['payment_status'])); ?></span></div>
                <?php if (!is_admin() && $order['payment_method'] === 'bank_transfer' && $order['payment_status'] === 'pending'): ?>
                    <a href="<?php echo BASE_URL; ?>pages/customer/bank-payment.php?id=<?php echo $orderId; ?>" class="btn btn-brand btn-sm w-100 mt-3">
                        <i class="bi bi-qr-code-scan me-1"></i>Pay with QR Code
                    </a>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-shop text-danger me-2"></i>Restaurant & Notes</h5>
                <div class="small text-muted mb-2"><strong>Restaurant:</strong> <?php echo e($restaurantNames['restaurant_names'] ?: 'Multiple restaurants'); ?></div>
                <div class="small text-muted"><strong>Notes:</strong> <?php echo e($internalNote); ?></div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-receipt text-danger me-2"></i>Order Summary</h5>
                <div class="d-flex justify-content-between mb-2 small text-muted"><span>Subtotal</span><span><?php echo format_currency($subtotal); ?></span></div>
                <div class="d-flex justify-content-between mb-2 small text-muted"><span>Delivery Fee</span><span><?php echo format_currency($deliveryFee); ?></span></div>
                <div class="d-flex justify-content-between mb-2 small text-muted"><span>Discount</span><span><?php echo format_currency($discount); ?></span></div>
                <div class="d-flex justify-content-between mb-2 small text-muted"><span>Coupon</span><span><?php echo e($coupon); ?></span></div>
                <div class="d-flex justify-content-between mb-2 small text-muted"><span>Tax</span><span><?php echo format_currency($tax); ?></span></div>
                <hr>
                <div class="d-flex justify-content-between align-items-center"><span class="fw-bold">Grand Total</span><span class="fw-bold text-danger fs-4"><?php echo format_currency($grandTotal); ?></span></div>
            </div>

            <?php if (!is_admin() && $order['status'] === 'pending'): ?>
                <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" method="POST" class="mb-3" onsubmit="return confirm('Cancel this order?');">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="action" value="cancel_order">
                    <button type="submit" class="btn btn-outline-danger w-100 py-2">Cancel Order</button>
                </form>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" method="POST" class="mb-3">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="reorder_items">
                <button type="submit" class="btn btn-brand w-100 py-2">Reorder Items</button>
            </form>

            <?php if ($isAdmin): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3"><i class="bi bi-sliders2 text-danger me-2"></i>Admin Controls</h5>
                    <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" method="POST" class="mb-3">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="action" value="update_status">
                        <label for="order_status" class="form-label small fw-semibold">Update Order Status</label>
                        <select name="order_status" id="order_status" class="form-select">
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="preparing" <?php echo $status === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                            <option value="out_for_delivery" <?php echo $status === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                            <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-outline-danger mt-3 w-100">Save Status</button>
                    </form>
                    <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" method="POST">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="action" value="save_note">
                        <label for="internal_note" class="form-label small fw-semibold">Internal Notes</label>
                        <textarea name="internal_note" id="internal_note" rows="3" class="form-control" placeholder="Add internal note for the kitchen or courier team"><?php echo e($internalNote); ?></textarea>
                        <button type="submit" class="btn btn-outline-secondary mt-3 w-100">Save Note</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
