<?php
/**
 * One-time sample data seeder for orders and order_items.
 *
 * This script only inserts new orders when there are no existing orders,
 * and does not delete any existing production data.
 */

require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/database/Database.php';

$db = Database::getInstance();
$existingOrders = (int)$db->queryRow('SELECT COUNT(*) AS c FROM `orders`')['c'] ?? 0;
if ($existingOrders > 0) {
    echo "Seed skipped: $existingOrders existing orders present.\n";
    exit(0);
}

$customers = $db->queryAll("SELECT id, username FROM `users` WHERE `role` = 'customer' ORDER BY id ASC");
if (empty($customers)) {
    echo "ERROR: No customer accounts found. Seed cannot proceed.\n";
    exit(1);
}

$foods = $db->queryAll("SELECT id, name, price, restaurant_id FROM `foods` ORDER BY id ASC");
if (empty($foods)) {
    echo "ERROR: No foods found. Seed cannot proceed.\n";
    exit(1);
}

$customerIds = array_column($customers, 'id');
$foodLookup = [];
foreach ($foods as $food) {
    $foodLookup[$food['id']] = $food;
}

// Build a balanced set of sample orders across several months and statuses.
$sampleOrders = [
    [ 'user_id' => $customerIds[0], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-03-05 12:15:00', 'items' => [ [1, 2], [4, 1] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'delivered',      'payment_method' => 'cod',  'payment_status' => 'paid',   'date' => '2026-03-17 18:40:00', 'items' => [ [2, 1], [8, 1] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'preparing',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-03-29 20:10:00', 'items' => [ [5, 2] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-04-04 13:05:00', 'items' => [ [6, 1], [7, 1] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'out_for_delivery','payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-04-15 19:55:00', 'items' => [ [1, 1], [5, 1], [9, 1] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'cancelled',      'payment_method' => 'cod',  'payment_status' => 'failed', 'date' => '2026-04-26 17:20:00', 'items' => [ [3, 1] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-05-08 14:30:00', 'items' => [ [4, 1], [5, 1], [1, 1] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'delivered',      'payment_method' => 'cod',  'payment_status' => 'paid',   'date' => '2026-05-19 12:25:00', 'items' => [ [6, 1], [8, 1] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'preparing',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-05-28 18:05:00', 'items' => [ [2, 2], [3, 1] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-06-03 20:15:00', 'items' => [ [5, 1], [7, 1] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'pending',        'payment_method' => 'cod',  'payment_status' => 'pending','date' => '2026-06-11 11:10:00', 'items' => [ [9, 2] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'out_for_delivery','payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-06-18 16:50:00', 'items' => [ [1, 1], [6, 1] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-06-26 13:35:00', 'items' => [ [4, 1], [2, 1] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'pending',        'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-07-01 10:45:00', 'items' => [ [7, 1], [8, 1] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'preparing',      'payment_method' => 'cod',  'payment_status' => 'paid',   'date' => '2026-07-06 19:05:00', 'items' => [ [5, 2] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-07-12 12:00:00', 'items' => [ [1, 1], [4, 1] ] ],
    [ 'user_id' => $customerIds[0], 'status' => 'cancelled',      'payment_method' => 'cod',  'payment_status' => 'failed', 'date' => '2026-07-18 15:20:00', 'items' => [ [3, 1], [9, 1] ] ],
    [ 'user_id' => $customerIds[1], 'status' => 'out_for_delivery','payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-07-21 20:35:00', 'items' => [ [2, 1], [6, 1], [8, 1] ] ],
    [ 'user_id' => $customerIds[2], 'status' => 'delivered',      'payment_method' => 'card', 'payment_status' => 'paid',   'date' => '2026-07-23 11:10:00', 'items' => [ [4, 1], [5, 1] ] ],
];

$insertOrder = $db->getConnection()->prepare(
    "INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `delivery_address`, `payment_method`, `payment_status`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$insertItem = $db->getConnection()->prepare(
    "INSERT INTO `order_items` (`order_id`, `food_id`, `quantity`, `price`, `created_at`) VALUES (?, ?, ?, ?, ?)"
);

$db->getConnection()->beginTransaction();
try {
    foreach ($sampleOrders as $sample) {
        $total = 0.0;
        foreach ($sample['items'] as $item) {
            [$foodId, $qty] = $item;
            if (!isset($foodLookup[$foodId])) {
                throw new Exception("Food ID $foodId not found.");
            }
            $total += (float)$foodLookup[$foodId]['price'] * (int)$qty;
        }
        $insertOrder->execute([
            $sample['user_id'],
            number_format($total, 2, '.', ''),
            $sample['status'],
            '123 Demo Lane, Test City',
            $sample['payment_method'],
            $sample['payment_status'],
            $sample['date']
        ]);
        $orderId = $db->getConnection()->lastInsertId();
        foreach ($sample['items'] as $item) {
            [$foodId, $qty] = $item;
            $price = $foodLookup[$foodId]['price'];
            $insertItem->execute([$orderId, $foodId, $qty, $price, $sample['date']]);
        }
    }
    $db->getConnection()->commit();
    echo "Seed complete: " . count($sampleOrders) . " orders inserted.\n";
} catch (Exception $e) {
    $db->getConnection()->rollBack();
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
