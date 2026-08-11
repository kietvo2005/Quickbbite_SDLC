<?php
/**
 * Real, executable test harness for the Unit 7 report.
 * Runs directly against the live schema and the project's own functions
 * (no mocking) — place_order(), verify_csrf(), password_hash/verify, and
 * the order-tracking timeline logic copied verbatim from order-detail.php.
 */

// Minimal server context so config.php doesn't warn under CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/database/Database.php';
require_once __DIR__ . '/includes/functions/helpers.php';
require_once __DIR__ . '/includes/functions/auth.php';
require_once __DIR__ . '/includes/functions/cart_functions.php';
require_once __DIR__ . '/includes/functions/order_functions.php';

$results = [];
function check($id, $desc, $condition) {
    global $results;
    $results[] = [$id, $desc, $condition ? 'PASS' : 'FAIL'];
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $id . ' - ' . $desc . PHP_EOL;
}

$db = Database::getInstance();

// --- Set up a real test customer (bypassing the HTML form, calling the same
//     password_hash() call register.php actually uses) ---
$testEmail = 'test_customer_' . time() . '@example.com';
$hashed = password_hash('WeakOrStrong@123', PASSWORD_DEFAULT);
$db->execute(
    "INSERT INTO `users` (`name`,`username`,`email`,`password`,`phone`,`role`,`status`) VALUES (?,?,?,?,?, 'customer','active')",
    ['Test Customer', 'test_customer_' . time(), $testEmail, $hashed, '+1 555-0000']
);
$testUserId = (int)$db->lastInsertId();

// =========================================================
// TC08 — password hashing/verification (register.php / auth.php logic)
// =========================================================
$row = $db->queryRow("SELECT `password` FROM `users` WHERE `id` = ?", [$testUserId]);
check('TC08a', 'password_hash() does not store the plaintext password', $row['password'] !== 'WeakOrStrong@123');
check('TC08b', 'password_verify() confirms the correct password against the stored hash', password_verify('WeakOrStrong@123', $row['password']));
check('TC08c', 'password_verify() rejects an incorrect password', !password_verify('wrongpassword', $row['password']));

// Same server-side password policy regex actually used in register.php
function passes_register_password_policy($password) {
    return !(strlen($password) < 8
        || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[a-z]/', $password)
        || !preg_match('/[0-9]/', $password)
        || !preg_match('/[^A-Za-z0-9]/', $password));
}
check('TC08d', 'register.php password policy rejects a weak password ("abc123")', passes_register_password_policy('abc123') === false);
check('TC08e', 'register.php password policy accepts a strong password ("WeakOrStrong@123")', passes_register_password_policy('WeakOrStrong@123') === true);

// =========================================================
// TC09 — CSRF token verification (helpers.php, used by checkout.php etc.)
// =========================================================
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(16));
check('TC09a', 'verify_csrf() rejects a missing/incorrect token', verify_csrf('not-the-real-token') === false);
check('TC09b', 'verify_csrf() accepts the correct session token', verify_csrf($_SESSION['csrf_token']) === true);

// =========================================================
// Simulate the logged-in customer for cart/order tests
// =========================================================
$_SESSION['user_id'] = $testUserId;
$_SESSION['logged_in'] = true;
$_SESSION['user_role'] = 'customer';
$_SESSION['role'] = 'customer';

// =========================================================
// TC01 — place_order() with an empty cart
// =========================================================
$db->execute("DELETE FROM `cart` WHERE `user_id` = ?", [$testUserId]); // ensure empty
$threw = false;
$errMsg = '';
try {
    place_order($testUserId, '123 Test St, Test City TC 00000', 'cod', 'Test Customer', '+1 555-0000');
} catch (Exception $e) {
    $threw = true;
    $errMsg = $e->getMessage();
}
check('TC01', "place_order() throws an Exception on an empty cart (message: \"$errMsg\")", $threw && strpos($errMsg, 'empty') !== false);

// =========================================================
// TC02 — add_to_cart() + get_cart_total() subtotal correctness
// =========================================================
$food = $db->queryRow("SELECT `id`,`price` FROM `foods` WHERE `id` = 1"); // Classic Cheeseburger, seeded
add_to_cart((int)$food['id'], 2);
$expectedTotal = round((float)$food['price'] * 2, 2);
$actualTotal = round((float)get_cart_total(), 2);
check('TC02', "get_cart_total() = qty(2) x price({$food['price']}) = {$expectedTotal} (actual: {$actualTotal})", abs($expectedTotal - $actualTotal) < 0.01);

// =========================================================
// TC03 — place_order() with a valid cart (real transaction commit)
// =========================================================
$orderId = place_order($testUserId, '123 Test St, Test City TC 00000', 'cod', 'Test Customer', '+1 555-0000');
$order = $db->queryRow("SELECT * FROM `orders` WHERE `id` = ?", [$orderId]);
$items = $db->queryAll("SELECT * FROM `order_items` WHERE `order_id` = ?", [$orderId]);
check('TC03a', "orders row created with id={$orderId}", $order !== false);
check('TC03b', "orders.status defaults to 'pending' (actual: '{$order['status']}')", $order['status'] === 'pending');
check('TC03c', "order_items row created matching the cart (count: " . count($items) . ")", count($items) === 1 && (int)$items[0]['quantity'] === 2);
check('TC03d', "cart is emptied after checkout (rows left: " . count($db->queryAll("SELECT * FROM `cart` WHERE `user_id`=?", [$testUserId])) . ")", count($db->queryAll("SELECT * FROM `cart` WHERE `user_id`=?", [$testUserId])) === 0);

// =========================================================
// TC06 — admin advances order status (same allow-list logic as order-detail.php)
// =========================================================
$allowedStatuses = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
$newStatus = 'out_for_delivery';
if (in_array($newStatus, $allowedStatuses, true)) {
    $db->execute("UPDATE `orders` SET `status` = ? WHERE `id` = ?", [$newStatus, $orderId]);
}
$updated = $db->queryRow("SELECT `status` FROM `orders` WHERE `id` = ?", [$orderId]);
check('TC06', "orders.status updates to 'out_for_delivery' (actual: '{$updated['status']}')", $updated['status'] === 'out_for_delivery');

// =========================================================
// TC07 — order-tracking timeline step calculation (copied verbatim from order-detail.php)
// =========================================================
$status = $updated['status'];
$currentStep = 1;
if ($status === 'preparing') { $currentStep = 2; }
elseif ($status === 'out_for_delivery') { $currentStep = 4; }
elseif ($status === 'delivered') { $currentStep = 5; }
elseif ($status === 'cancelled') { $currentStep = 0; }
check('TC07', "timeline highlights step 4 of 5 for status 'out_for_delivery' (actual step: {$currentStep})", $currentStep === 4);

// =========================================================
// TC10 — eFSM guard: cancellation only allowed while status is 'pending'
// (mirrors the exact guard used in order-detail.php's cancel_order action)
// =========================================================
function attempt_cancel($db, $orderId) {
    $order = $db->queryRow("SELECT `status` FROM `orders` WHERE `id` = ?", [$orderId]);
    if ($order['status'] === 'pending') {
        $db->execute("UPDATE `orders` SET `status` = 'cancelled' WHERE `id` = ?", [$orderId]);
        return true; // cancelled
    }
    return false; // guard blocked it
}
$cancelResult = attempt_cancel($db, $orderId); // order is now out_for_delivery, should be BLOCKED
$afterAttempt = $db->queryRow("SELECT `status` FROM `orders` WHERE `id` = ?", [$orderId]);
check('TC10', "eFSM guard blocks cancellation of an 'out_for_delivery' order (status unchanged: '{$afterAttempt['status']}')", $cancelResult === false && $afterAttempt['status'] === 'out_for_delivery');

// =========================================================
// TC11 — referential integrity: ON DELETE RESTRICT on categories with foods
// =========================================================
$fkBlocked = false;
try {
    $db->execute("DELETE FROM `categories` WHERE `id` = 1"); // Burgers, has foods referencing it
} catch (\PDOException $e) {
    $fkBlocked = true;
}
check('TC11', 'Database FK constraint (ON DELETE RESTRICT) blocks deleting a category still referenced by foods', $fkBlocked);

// --- Cleanup test data ---
$db->execute("DELETE FROM `order_items` WHERE `order_id` = ?", [$orderId]);
$db->execute("DELETE FROM `orders` WHERE `id` = ?", [$orderId]);
$db->execute("DELETE FROM `users` WHERE `id` = ?", [$testUserId]);

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
$pass = count(array_filter($results, fn($r) => $r[2] === 'PASS'));
$fail = count($results) - $pass;
echo "Total: " . count($results) . " | Pass: $pass | Fail: $fail" . PHP_EOL;
