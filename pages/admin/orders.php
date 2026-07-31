<?php
/**
 * Order pipeline Manager - Food Delivery System Admin Panel
 * Tracks customer deliveries, filters status categories, and updates fulfillment parameters.
 */

$pageTitle = "Manage Orders";
require_once __DIR__ . '/../../includes/header.php';

// Guards
require_admin();

$db = Database::getInstance();

// Parse status filter if any
$filterStatus = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// 1. Handle Order Parameter Updates (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Security error: CSRF verification failed.");
    }
    
    $action = $_POST['action'];
    $orderId = (int)$_POST['order_id'];
    
    if ($action === 'update_status' && isset($_POST['status'])) {
        $status = sanitize_input($_POST['status']);
        if (in_array($status, ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'])) {
            $db->execute("UPDATE `orders` SET `status` = ? WHERE `id` = ?", [$status, $orderId]);
            set_flash('success', 'Order status updated successfully.');
        }
    } elseif ($action === 'update_payment' && isset($_POST['payment_status'])) {
        $payStatus = sanitize_input($_POST['payment_status']);
        if (in_array($payStatus, ['pending', 'paid', 'failed'])) {
            $db->execute("UPDATE `orders` SET `payment_status` = ? WHERE `id` = ?", [$payStatus, $orderId]);
            
            // Sync status inside payment table
            $db->execute("UPDATE `payments` SET `payment_status` = ? WHERE `order_id` = ?", [$payStatus, $orderId]);
            set_flash('success', 'Payment status updated successfully.');
        }
    }
    
    redirect(BASE_URL . 'pages/admin/orders.php' . (!empty($filterStatus) ? '?status=' . urlencode($filterStatus) : ''));
}

// 2. Build Query
$sql = "SELECT o.*, u.`username`, u.`email` 
        FROM `orders` o 
        JOIN `users` u ON o.`user_id` = u.`id`";
$params = [];

if (!empty($filterStatus)) {
    $sql .= " WHERE o.`status` = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY o.`id` DESC";
$orders = $db->queryAll($sql, $params);
?>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Orders list panel -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-cart-check text-danger me-2"></i>Fulfillment Center</h3>
            
            <!-- Quick filter tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="orders.php" class="btn btn-sm <?php echo empty($filterStatus) ? 'btn-dark' : 'btn-outline-dark'; ?> rounded-pill px-3">All Orders</a>
                <a href="orders.php?status=pending" class="btn btn-sm <?php echo $filterStatus === 'pending' ? 'btn-warning text-dark' : 'btn-outline-dark'; ?> rounded-pill px-3">Pending</a>
                <a href="orders.php?status=preparing" class="btn btn-sm <?php echo $filterStatus === 'preparing' ? 'btn-info text-dark' : 'btn-outline-dark'; ?> rounded-pill px-3">Preparing</a>
                <a href="orders.php?status=out_for_delivery" class="btn btn-sm <?php echo $filterStatus === 'out_for_delivery' ? 'btn-primary' : 'btn-outline-dark'; ?> rounded-pill px-3">Out for Delivery</a>
                <a href="orders.php?status=delivered" class="btn btn-sm <?php echo $filterStatus === 'delivered' ? 'btn-success' : 'btn-outline-dark'; ?> rounded-pill px-3">Delivered</a>
                <a href="orders.php?status=cancelled" class="btn btn-sm <?php echo $filterStatus === 'cancelled' ? 'btn-danger' : 'btn-outline-dark'; ?> rounded-pill px-3">Cancelled</a>
            </div>

            <!-- Orders Table -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <?php if (empty($orders)): ?>
                    <p class="text-muted small">No order data satisfies current filters.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Payment Details</th>
                                    <th scope="col">Fulfillment Stage</th>
                                    <th scope="col" class="text-end">Total Amount</th>
                                    <th scope="col" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $order['id']; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block small"><?php echo e($order['username']); ?></span>
                                            <span class="text-muted small" style="font-size: 0.7rem;"><?php echo date('M d, h:i A', strtotime($order['created_at'])); ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="small text-uppercase fw-semibold text-muted" style="font-size: 0.7rem;">
                                                    <?php echo $order['payment_method'] === 'cod' ? 'Cash' : 'Card'; ?>
                                                </span>
                                                <!-- Payment Status Switch Form -->
                                                <form action="orders.php<?php echo !empty($filterStatus) ? '?status=' . $filterStatus : ''; ?>" method="POST">
                                                    <?php echo csrf_input(); ?>
                                                    <input type="hidden" name="action" value="update_payment">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="payment_status" class="form-select form-select-sm border-0 bg-light text-xxs p-1 fw-bold <?php echo $order['payment_status'] === 'paid' ? 'text-success' : 'text-warning'; ?>" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                        <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Delivery Status Switch Form -->
                                            <form action="orders.php<?php echo !empty($filterStatus) ? '?status=' . $filterStatus : ''; ?>" method="POST">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                
                                                <select name="status" class="form-select form-select-sm border-0 bg-light fw-bold text-xxs" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="out_for_delivery" <?php echo $order['status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold text-danger small"><?php echo format_currency($order['total_amount']); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-brand-outline py-1 px-2 rounded small" style="font-size: 0.75rem;">Invoice</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
