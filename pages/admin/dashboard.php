<?php
/**
 * Professional Admin Dashboard - QuickBite
 * Incorporates KPI statistics cards, Chart.js integrations, Newest Customers, and Bestselling Food.
 */

require_once __DIR__ . '/../../includes/config/config.php';
require_once __DIR__ . '/../../includes/database/Database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';
require_once __DIR__ . '/../../includes/functions/auth.php';

// Safe check: Deny access if not admin
require_admin();

$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../../includes/header.php';

$db = Database::getInstance();

// 1. Calculate KPI Metric Card Summaries
$totalUsersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `users`")['cnt'];
$totalCustomersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `users` WHERE `role` = 'customer'")['cnt'];
$totalRestaurantsCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `restaurants`")['cnt'];
$totalFoodsCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `foods`")['cnt'];
$totalOrdersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `orders`")['cnt'];
$pendingOrdersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `orders` WHERE `status` = 'pending'")['cnt'];
$completedOrdersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `orders` WHERE `status` = 'delivered'")['cnt'];
$cancelledOrdersCount = (int)$db->queryRow("SELECT COUNT(*) AS cnt FROM `orders` WHERE `status` = 'cancelled'")['cnt'];
$todayRevenue = (float)$db->queryRow("SELECT COALESCE(SUM(`total_amount`), 0) AS revenue FROM `orders` WHERE `payment_status` = 'paid' AND `status` != 'cancelled' AND DATE(`created_at`) = CURRENT_DATE()")['revenue'];
$monthlyRevenue = (float)$db->queryRow("SELECT COALESCE(SUM(`total_amount`), 0) AS revenue FROM `orders` WHERE `payment_status` = 'paid' AND `status` != 'cancelled' AND MONTH(`created_at`) = MONTH(CURRENT_DATE()) AND YEAR(`created_at`) = YEAR(CURRENT_DATE())")['revenue'];
$yearlyRevenue = (float)$db->queryRow("SELECT COALESCE(SUM(`total_amount`), 0) AS revenue FROM `orders` WHERE `payment_status` = 'paid' AND `status` != 'cancelled' AND YEAR(`created_at`) = YEAR(CURRENT_DATE())")['revenue'];

// 2. Fetch Monthly Revenue Aggregates for Chart.js Line Graph
$monthlyRevenueData = $db->queryAll("SELECT DATE_FORMAT(`created_at`, '%b %Y') AS `month_label`, SUM(`total_amount`) AS `revenue` 
                                     FROM `orders` 
                                     WHERE `status` != 'cancelled' AND `payment_status` = 'paid' 
                                     GROUP BY YEAR(`created_at`), MONTH(`created_at`) 
                                     ORDER BY `created_at` ASC");
$monthLabels = [];
$monthValues = [];
foreach ($monthlyRevenueData as $row) {
    $monthLabels[] = $row['month_label'];
    $monthValues[] = (float)$row['revenue'];
}

// 3. Fetch Order Status Statistics for Chart.js Doughnut Graph
$statusStatsData = $db->queryAll("SELECT `status`, COUNT(*) AS `count` FROM `orders` GROUP BY `status`");
$statusLabels = [];
$statusValues = [];
foreach ($statusStatsData as $row) {
    $statusLabels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $statusValues[] = (int)$row['count'];
}

// 4. Fetch Newest Customers List
$newestCustomers = $db->queryAll("SELECT `id`, `username`, `email`, `created_at` 
                                  FROM `users` 
                                  WHERE `role` = 'customer' 
                                  ORDER BY `id` DESC 
                                  LIMIT 5");

// 5. Fetch Bestselling Food Items List
$sqlPopular = "SELECT f.`name`, r.`name` AS `restaurant_name`, SUM(oi.`quantity`) AS `qty_sold`, f.`price`
               FROM `order_items` oi
               JOIN `foods` f ON oi.`food_id` = f.`id`
               JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
               GROUP BY oi.`food_id`
               ORDER BY `qty_sold` DESC
               LIMIT 5";
$popularDishes = $db->queryAll($sqlPopular);

// 6. Fetch Recent Orders List
$recentOrders = $db->queryAll("SELECT o.*, u.`username` 
                               FROM `orders` o 
                               JOIN `users` u ON o.`user_id` = u.`id` 
                               ORDER BY o.`created_at` DESC 
                               LIMIT 5");
?>

<!-- Import Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container my-5 animate-fade-in-up">
    <div class="row g-4">
        <!-- Reusable Admin Sidebar -->
        <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        
        <!-- Main Stats Panel -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-speedometer text-danger me-2"></i>Executive Control Center</h3>
            
            <!-- Statistics KPI Cards -->
            <div class="row g-3 mb-5">
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Users</span>
                            <h3 class="fw-bold text-danger mt-1 mb-0"><?php echo $totalUsersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-danger"><i class="bi bi-people fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Customers</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalCustomersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-primary"><i class="bi bi-person-circle fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Restaurants</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalRestaurantsCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-success"><i class="bi bi-shop fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Foods</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalFoodsCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-warning"><i class="bi bi-egg-fried fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Orders</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalOrdersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-info"><i class="bi bi-cart-check fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Revenue Today</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo format_currency($todayRevenue); ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-danger"><i class="bi bi-cash-stack fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Revenue This Month</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo format_currency($monthlyRevenue); ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-primary"><i class="bi bi-calendar-check fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Revenue This Year</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo format_currency($yearlyRevenue); ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-success"><i class="bi bi-graph-up-arrow fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Pending Orders</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $pendingOrdersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-warning"><i class="bi bi-hourglass-split fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Completed Orders</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $completedOrdersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-success"><i class="bi bi-bag-check fs-4"></i></div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex align-items-center justify-content-between flex-row">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Cancelled Orders</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $cancelledOrdersCount; ?></h3>
                        </div>
                        <div class="bg-light p-3 rounded-circle text-danger"><i class="bi bi-x-circle fs-4"></i></div>
                    </div>
                </div>
            </div>

            <!-- Interactive Visualization Charts -->
            <div class="row g-4 mb-5">
                <!-- Line Chart: Monthly Revenue -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-graph-up text-danger me-2"></i>Monthly Revenue Progress</h5>
                        <?php if (empty($monthLabels)): ?>
                            <div class="text-center py-5 text-muted small">Insufficient sales data to draw graph details.</div>
                        <?php else: ?>
                            <div style="height: 220px;">
                                <canvas id="revenueChart" height="180"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Doughnut Chart: Order Status Breakdown -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-pie-chart text-danger me-2"></i>Status Statistics</h5>
                        <?php if (empty($statusLabels)): ?>
                            <div class="text-center py-5 text-muted small">No order details logged.</div>
                        <?php else: ?>
                            <div class="position-relative" style="max-height: 250px;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Detailed Statistics lists -->
            <div class="row g-4 mb-5">
                <!-- Bestselling Food Items -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold mb-3"><i class="bi bi-fire text-warning me-2"></i>Bestselling Dishes</h5>
                        <?php if (empty($popularDishes)): ?>
                            <p class="text-muted small">No dish orders recorded.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($popularDishes as $dish): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-0 bg-transparent">
                                        <div>
                                            <span class="fw-semibold text-dark small d-block"><?php echo e($dish['name']); ?></span>
                                            <span class="text-muted text-xs"><?php echo e($dish['restaurant_name']); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-danger rounded-pill px-2 py-1 small"><?php echo $dish['qty_sold']; ?> sold</span>
                                            <span class="text-muted d-block text-xxs mt-1"><?php echo format_currency($dish['price'] * $dish['qty_sold']); ?> total</span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Newest Customers -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold mb-3"><i class="bi bi-person-plus text-primary me-2"></i>Newest Customers</h5>
                        <?php if (empty($newestCustomers)): ?>
                            <p class="text-muted small">No customers registered.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($newestCustomers as $cust): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-0 bg-transparent">
                                        <div>
                                            <span class="fw-semibold text-dark small d-block"><?php echo e($cust['username']); ?></span>
                                            <span class="text-muted text-xs"><?php echo e($cust['email']); ?></span>
                                        </div>
                                        <span class="text-muted small" style="font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($cust['created_at'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Log -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-4"><i class="bi bi-journal-text text-danger me-2"></i>Recent Orders Pipeline</h5>
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted small">No orders recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="text-muted small">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Payment Method</th>
                                    <th scope="col">Fulfillment Status</th>
                                    <th scope="col" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $order['id']; ?></td>
                                        <td>
                                            <span class="text-dark small fw-semibold"><?php echo e($order['username']); ?></span>
                                            <span class="text-muted text-xxs d-block"><?php echo date('M d, h:i A', strtotime($order['created_at'])); ?></span>
                                        </td>
                                        <td class="fw-bold text-danger small"><?php echo format_currency($order['total_amount']); ?></td>
                                        <td class="text-uppercase small"><?php echo e($order['payment_method']); ?></td>
                                        <td>
                                            <?php 
                                            $st = $order['status'];
                                            $cl = 'bg-secondary';
                                            if ($st === 'pending') $cl = 'bg-warning text-dark';
                                            elseif ($st === 'preparing') $cl = 'bg-info text-dark';
                                            elseif ($st === 'out_for_delivery') $cl = 'bg-primary';
                                            elseif ($st === 'delivered') $cl = 'bg-success';
                                            elseif ($st === 'cancelled') $cl = 'bg-danger';
                                            ?>
                                            <span class="badge rounded-pill <?php echo $cl; ?> text-uppercase small" style="font-size: 0.7rem;">
                                                <?php echo str_replace('_', ' ', $st); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>pages/customer/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light">Manage</a>
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

<!-- Chart.js Render Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Line Chart: Monthly Revenue
        <?php if (!empty($monthLabels)): ?>
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthLabels); ?>,
                datasets: [{
                    label: 'Sales Revenue ($)',
                    data: <?php echo json_encode($monthValues); ?>,
                    borderColor: '#ff4757',
                    backgroundColor: 'rgba(255, 71, 87, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ff4757',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '$' + value; }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Doughnut Chart: Order Status Breakdown
        <?php if (!empty($statusLabels)): ?>
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($statusValues); ?>,
                    backgroundColor: [
                        '#ffa502', // pending
                        '#70a1ff', // preparing
                        '#2ed573', // delivered
                        '#ff4757', // cancelled
                        '#2f3542'  // out_for_delivery
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
