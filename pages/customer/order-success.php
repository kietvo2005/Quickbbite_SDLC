<?php
/**
 * Order Success Page - Food Delivery System
 * Shows a polished confirmation after the customer places an order.
 */

$pageTitle = "Order Confirmed";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

require_customer();

$db = Database::getInstance();
$userId = (int)($_SESSION['user_id'] ?? 0);
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    set_flash('warning', 'No order was found to display.');
    redirect(BASE_URL . 'pages/customer/order-history.php');
}

$order = $db->queryRow(
    "SELECT o.*, u.`name`, u.`username`, u.`email`, u.`phone`
     FROM `orders` o
     JOIN `users` u ON o.`user_id` = u.`id`
     WHERE o.`id` = ? AND o.`user_id` = ?",
    [$orderId, $userId]
);

if (!$order) {
    set_flash('warning', 'The selected order could not be found.');
    redirect(BASE_URL . 'pages/customer/order-history.php');
}

require_once __DIR__ . '/../../includes/header.php';

$estimatedTime = '35-45 min';
if ($order['status'] === 'preparing') {
    $estimatedTime = '25-35 min';
} elseif ($order['status'] === 'out_for_delivery') {
    $estimatedTime = '10-15 min';
} elseif ($order['status'] === 'delivered') {
    $estimatedTime = 'Delivered';
} elseif ($order['status'] === 'cancelled') {
    $estimatedTime = 'Cancelled';
}

$notes = $_SESSION['order_notes'][$orderId] ?? ($_SESSION['last_order_meta'][$orderId]['notes'] ?? '');
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 96px; height: 96px;">
                        <i class="bi bi-check2-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-dark mb-2">Thank You for Your Order!</h2>
                <p class="text-muted mb-4">Your meal is being prepared and our rider will be on the way soon.</p>

                <div class="row g-3 text-start mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted">Order Number</div>
                            <div class="fw-bold text-dark">#<?php echo $order['id']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted">Order Date</div>
                            <div class="fw-bold text-dark"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted">Estimated Delivery</div>
                            <div class="fw-bold text-dark"><?php echo $estimatedTime; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border">
                            <div class="small text-muted">Payment Method</div>
                            <div class="fw-bold text-dark text-uppercase"><?php echo e($order['payment_method']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-4 bg-light border mb-4 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Address</span>
                        <span class="fw-semibold text-dark"><?php echo e($order['delivery_address']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Grand Total</span>
                        <span class="fw-semibold text-danger"><?php echo format_currency($order['total_amount']); ?></span>
                    </div>
                    <?php if (!empty($notes)): ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Notes</span>
                            <span class="fw-semibold text-dark"><?php echo e($notes); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $orderId; ?>" class="btn btn-brand px-4">View Order Details</a>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-secondary px-4">Continue Shopping</a>
                    <a href="<?php echo BASE_URL; ?>pages/customer/order-history.php" class="btn btn-outline-dark px-4">My Orders</a>
                    <a href="<?php echo BASE_URL; ?>invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn btn-outline-danger px-4">Print Invoice</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
