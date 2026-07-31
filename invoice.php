<?php
require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/database/Database.php';
require_once __DIR__ . '/includes/functions/helpers.php';
require_once __DIR__ . '/includes/functions/auth.php';

$db = Database::getInstance();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    http_response_code(404);
    echo 'Invoice not found.';
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
    http_response_code(404);
    echo 'Invoice not found.';
    exit;
}

$items = $db->queryAll(
    "SELECT oi.`quantity`, oi.`price`, f.`name`
     FROM `order_items` oi
     JOIN `foods` f ON oi.`food_id` = f.`id`
     WHERE oi.`order_id` = ?",
    [$orderId]
);

$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$download = isset($_GET['download']) && $_GET['download'] == '1';
if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice-' . $orderId . '.pdf"');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo $orderId; ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 24px; }
        h1 { margin: 0 0 10px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px 0; text-align: left; }
        .right { text-align: right; }
        .summary { margin-top: 20px; float: right; width: 280px; }
        .summary div { display: flex; justify-content: space-between; margin-bottom: 6px; }
    </style>
</head>
<body>
    <h1>Invoice #<?php echo $orderId; ?></h1>
    <div class="meta">
        <div><strong>Customer:</strong> <?php echo e($order['name'] ?: $order['username']); ?></div>
        <div><strong>Email:</strong> <?php echo e($order['email']); ?></div>
        <div><strong>Address:</strong> <?php echo e($order['delivery_address']); ?></div>
        <div><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo e($item['name']); ?></td>
                    <td class="right"><?php echo (int)$item['quantity']; ?></td>
                    <td class="right"><?php echo format_currency($item['price']); ?></td>
                    <td class="right"><?php echo format_currency((float)$item['price'] * (int)$item['quantity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        <div><span>Subtotal</span><strong><?php echo format_currency($subtotal); ?></strong></div>
        <div><span>Delivery Fee</span><strong><?php echo format_currency(0); ?></strong></div>
        <div><span>Grand Total</span><strong><?php echo format_currency($order['total_amount']); ?></strong></div>
    </div>
</body>
</html>
