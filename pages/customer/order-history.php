<?php
/**
 * Customer Order History - QuickBite
 * Lists customer orders with status indicators.
 */

$pageTitle = "Order History";
require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';
require_once __DIR__ . '/../../includes/database/Database.php';

require_customer();
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Retrieve orders list
$ordersList = $db->queryAll("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `created_at` DESC", [$userId]);
?>

<div class="container my-5 animate-fade-in-up">
    <h2 class="fw-bold mb-4"><i class="bi bi-clock-history text-danger me-2"></i><?php echo __('orders_title'); ?></h2>
    
    <?php if (empty($ordersList)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="mb-4">
                <i class="bi bi-journal-x text-muted" style="font-size: 5rem;"></i>
            </div>
            <h4 class="fw-bold text-dark"><?php echo __('orders_none_title'); ?></h4>
            <p class="text-muted mb-4"><?php echo __('orders_none_desc'); ?></p>
            <div>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-brand px-5 py-3"><?php echo __('orders_first_meal'); ?></a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th scope="col" style="border: none;"><?php echo __('orders_order_id'); ?></th>
                            <th scope="col" style="border: none;"><?php echo __('orders_date_placed'); ?></th>
                            <th scope="col" style="border: none;"><?php echo __('orders_payment_info'); ?></th>
                            <th scope="col" style="border: none;" class="text-center"><?php echo __('orders_delivery_status'); ?></th>
                            <th scope="col" style="border: none;" class="text-end"><?php echo __('orders_total_paid'); ?></th>
                            <th scope="col" style="border: none;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordersList as $order): ?>
                            <tr>
                                <td class="py-3 fw-bold">
                                    #<?php echo $order['id']; ?>
                                </td>
                                <td class="py-3 text-muted small">
                                    <?php echo date('M d, Y &bull; h:i A', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="py-3">
                                    <span class="small d-block text-dark fw-semibold text-uppercase">
                                        <?php echo $order['payment_method'] === 'cod' ? __('orders_cod') : __('orders_online_card'); ?>
                                    </span>
                                    <span class="badge rounded-pill <?php echo $order['payment_status'] === 'paid' ? 'bg-light text-success' : 'bg-light text-warning'; ?>" style="font-size: 0.7rem;">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <?php 
                                    $status = $order['status'];
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                                    elseif ($status === 'preparing') $badgeClass = 'bg-info text-dark';
                                    elseif ($status === 'out_for_delivery') $badgeClass = 'bg-primary';
                                    elseif ($status === 'delivered') $badgeClass = 'bg-success';
                                    elseif ($status === 'cancelled') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge rounded-pill <?php echo $badgeClass; ?> px-3 py-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">
                                        <?php echo str_replace('_', ' ', $status); ?>
                                    </span>
                                </td>
                                <td class="py-3 text-end fw-bold text-danger">
                                    <?php echo format_currency($order['total_amount']); ?>
                                </td>
                                <td class="py-3 text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-brand-outline"><?php echo __('orders_view_details'); ?></a>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $order['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo __('orders_cancel_confirm'); ?>');">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="action" value="cancel_order">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><?php echo __('orders_cancel'); ?></button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $order['id']; ?>" method="POST" class="d-inline">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="reorder_items">
                                            <button type="submit" class="btn btn-sm btn-brand"><?php echo __('orders_reorder'); ?></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>