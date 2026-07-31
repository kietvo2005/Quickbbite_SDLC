<?php
/**
 * Order Placement and Management engine
 * Uses PDO database transactions for consistency.
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/cart_functions.php';
require_once __DIR__ . '/sepay_functions.php';

/**
 * Executes a customer checkout process.
 * @param int $user_id
 * @param string $address
 * @param string $payment_method ('cod', 'card', 'bank_transfer')
 * @return int Order ID
 * @throws Exception
 */
function place_order($user_id, $address, $payment_method, $customer_name = '', $customer_phone = '', $order_notes = '') {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    try {
        // Begin Transaction
        $conn->beginTransaction();
        
        $cart_items = get_cart_items();
        if (empty($cart_items)) {
            throw new Exception("Your shopping cart is currently empty.");
        }
        
        $total = get_cart_total();
        
        $customer_name = trim((string)$customer_name);
        $customer_phone = trim((string)$customer_phone);
        $order_notes = trim((string)$order_notes);
        
        $allowedMethods = ['cod', 'card', 'bank_transfer'];
        if (!in_array($payment_method, $allowedMethods, true)) {
            throw new Exception('Invalid payment method selected.');
        }

        $orderCode = null;
        if ($payment_method === 'bank_transfer') {
            $orderCode = generate_sepay_order_code();
        }
        
        if ($customer_name !== '' || $customer_phone !== '') {
            $conn->prepare("UPDATE `users` SET `name` = ?, `phone` = ?, `address` = ? WHERE `id` = ?")
                ->execute([$customer_name !== '' ? $customer_name : null, $customer_phone !== '' ? $customer_phone : null, $address, $user_id]);
        }
        
        // 1. Create order record
        $stmtOrder = $conn->prepare("INSERT INTO `orders` (`order_code`, `user_id`, `total_amount`, `status`, `delivery_address`, `payment_method`, `payment_status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtOrder->execute([$orderCode, $user_id, $total, 'pending', $address, $payment_method, 'pending']);
        $order_id = $conn->lastInsertId();
        
        // 2. Insert items
        $stmtItem = $conn->prepare("INSERT INTO `order_items` (`order_id`, `food_id`, `quantity`, `price`) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmtItem->execute([$order_id, $item['food_id'], $item['quantity'], $item['price']]);
        }
        
        // 3. Setup payment simulation record
        $pay_status = ($payment_method === 'card') ? 'paid' : 'pending';
        $transaction_id = ($payment_method === 'card') ? 'TXN-' . strtoupper(bin2hex(random_bytes(8))) : null;
        
        $stmtPay = $conn->prepare("INSERT INTO `payments` (`order_id`, `payment_method`, `payment_status`, `transaction_id`, `amount`) VALUES (?, ?, ?, ?, ?)");
        $stmtPay->execute([$order_id, $payment_method, $pay_status, $transaction_id, $total]);
        
        // Update order status if payment made
        if ($pay_status === 'paid') {
            $conn->prepare("UPDATE `orders` SET `payment_status` = 'paid' WHERE `id` = ?")->execute([$order_id]);
        }
        
        if ($order_notes !== '') {
            $_SESSION['order_notes'][$order_id] = $order_notes;
        }
        
        $_SESSION['last_order_meta'][$order_id] = [
            'name' => $customer_name,
            'phone' => $customer_phone,
            'notes' => $order_notes
        ];
        
        // 4. Wipe cart
        $conn->prepare("DELETE FROM `cart` WHERE `user_id` = ?")->execute([$user_id]);
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Get active user orders history.
 * @param int $user_id
 * @return array
 */
function get_user_orders($user_id) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `created_at` DESC";
    return $db->queryAll($sql, [$user_id]);
}

/**
 * Fetch detailed order view info.
 * @param int $order_id
 * @return array|false
 */
function get_order_by_id($order_id) {
    $db = Database::getInstance();
    $sql = "SELECT o.*, u.`username`, u.`email`, u.`phone` 
            FROM `orders` o
            JOIN `users` u ON o.`user_id` = u.`id`
            WHERE o.`id` = ?";
    return $db->queryRow($sql, [$order_id]);
}

/**
 * Fetch all items contained within an order.
 * @param int $order_id
 * @return array
 */
function get_order_items($order_id) {
    $db = Database::getInstance();
    $sql = "SELECT oi.*, f.`name`, f.`image_path`, r.`name` AS `restaurant_name`
            FROM `order_items` oi
            JOIN `foods` f ON oi.`food_id` = f.`id`
            JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
            WHERE oi.`order_id` = ?";
    return $db->queryAll($sql, [$order_id]);
}
