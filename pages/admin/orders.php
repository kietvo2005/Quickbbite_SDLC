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
$csrfToken = $_SESSION['csrf_token'] ?? '';

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
                                                <!-- Payment Status Switch -->
                                                <div class="status-control">
                                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                                    <select
                                                        class="status-select form-select form-select-sm border-0 bg-light text-xxs p-1 fw-bold <?php echo $order['payment_status'] === 'paid' ? 'text-success' : 'text-warning'; ?>"
                                                        data-endpoint="<?php echo BASE_URL; ?>admin/ajax/update_payment_status.php"
                                                        data-order-id="<?php echo $order['id']; ?>"
                                                        data-field="payment_status"
                                                        data-label="Payment status"
                                                        data-error-message="Unable to update payment status."
                                                    >
                                                        <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                        <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                    </select>
                                                    <div class="status-feedback small text-muted mt-1" aria-live="polite"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Delivery Status Switch -->
                                            <div class="status-control">
                                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                                <select
                                                    class="status-select form-select form-select-sm border-0 bg-light fw-bold text-xxs"
                                                    data-endpoint="<?php echo BASE_URL; ?>admin/ajax/update_order_status.php"
                                                    data-order-id="<?php echo $order['id']; ?>"
                                                    data-field="status"
                                                    data-label="Order status"
                                                    data-error-message="Unable to update order status."
                                                >
                                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="out_for_delivery" <?php echo $order['status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <div class="status-feedback small text-muted mt-1" aria-live="polite"></div>
                                            </div>
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

<div id="admin-toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>

<style>
    .status-control {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .status-select:disabled {
        opacity: 0.7;
        cursor: wait;
    }

    .status-feedback {
        min-height: 1rem;
    }

    .admin-toast {
        min-width: 240px;
        max-width: 320px;
        padding: 0.75rem 0.9rem;
        border-radius: 0.75rem;
        color: #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.2s ease, transform 0.2s ease;
        pointer-events: none;
    }

    .admin-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .admin-toast.success {
        background: linear-gradient(135deg, #198754, #20c997);
    }

    .admin-toast.error {
        background: linear-gradient(135deg, #dc3545, #e35d6a);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastContainer = document.getElementById('admin-toast-container');

        function showToast(message, type) {
            if (!toastContainer) {
                return;
            }

            const toast = document.createElement('div');
            toast.className = `admin-toast ${type} show`;
            toast.textContent = message;
            toastContainer.appendChild(toast);

            window.setTimeout(function () {
                toast.classList.remove('show');
                window.setTimeout(function () {
                    toast.remove();
                }, 220);
            }, 2200);
        }

        function setFeedback(select, message, isLoading) {
            const feedback = select.closest('.status-control').querySelector('.status-feedback');
            if (feedback) {
                feedback.textContent = isLoading ? 'Saving…' : message;
            }
        }

        function updateSelectAppearance(select, value) {
            select.classList.remove('text-success', 'text-warning', 'text-danger', 'text-info');

            if (select.dataset.field === 'payment_status') {
                if (value === 'paid') {
                    select.classList.add('text-success'); // Blue/Green for Paid
                } else if (value === 'failed') {
                    select.classList.add('text-danger'); // Red for Failed
                } else {
                    select.classList.add('text-warning'); // Yellow for Pending
                }
            }
        }

        document.querySelectorAll('.status-select').forEach(function (select) {
            const initialValue = select.value;
            select.dataset.previousValue = initialValue;
            updateSelectAppearance(select, initialValue);

            select.addEventListener('change', function () {
                const previousValue = select.dataset.previousValue || select.value;
                const newValue = select.value;
                const endpoint = select.dataset.endpoint;
                const orderId = select.dataset.orderId;
                const field = select.dataset.field;
                const label = select.dataset.label;
                const errorMessage = select.dataset.errorMessage;

                if (!endpoint || !orderId) {
                    return;
                }

                select.disabled = true;
                setFeedback(select, '', true);

                const formData = new URLSearchParams();
                formData.append('order_id', orderId);
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                formData.append(field, newValue);

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: formData.toString()
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { response, payload };
                        });
                    })
                    .then(function (result) {
                        if (result.payload && result.payload.success) {
                            select.dataset.previousValue = newValue;
                            updateSelectAppearance(select, newValue);
                            setFeedback(select, '');
                            showToast(label + ' updated.', 'success');
                        } else {
                            select.value = previousValue;
                            updateSelectAppearance(select, previousValue);
                            setFeedback(select, '');
                            showToast(result.payload?.message || errorMessage, 'error');
                        }
                    })
                    .catch(function () {
                        select.value = previousValue;
                        updateSelectAppearance(select, previousValue);
                        setFeedback(select, '');
                        showToast(errorMessage, 'error');
                    })
                    .finally(function () {
                        select.disabled = false;
                    });
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
