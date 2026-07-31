<?php
/**
 * Shopping Cart Management functions
 * Supports database sync for logged-in users and session fallback for guests.
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/auth.php';

/**
 * Add food item to the shopping cart.
 * @param int $food_id
 * @param int $quantity
 * @return bool
 */
function add_to_cart($food_id, $quantity = 1) {
    $quantity = max((int)$quantity, 1);
    
    if (is_logged_in()) {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        
        // Check if product is already in database cart
        $item = $db->queryRow("SELECT `id`, `quantity` FROM `cart` WHERE `user_id` = ? AND `food_id` = ?", [$user_id, $food_id]);
        if ($item) {
            $new_quantity = $item['quantity'] + $quantity;
            $db->execute("UPDATE `cart` SET `quantity` = ? WHERE `id` = ?", [$new_quantity, $item['id']]);
        } else {
            $db->execute("INSERT INTO `cart` (`user_id`, `food_id`, `quantity`) VALUES (?, ?, ?)", [$user_id, $food_id, $quantity]);
        }
        return true;
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$food_id])) {
            $_SESSION['cart'][$food_id] += $quantity;
        } else {
            $_SESSION['cart'][$food_id] = $quantity;
        }
        return true;
    }
}

/**
 * Update cart item quantity.
 * @param int $food_id
 * @param int $quantity
 * @return bool
 */
function update_cart_qty($food_id, $quantity) {
    $quantity = max((int)$quantity, 1);
    
    if (is_logged_in()) {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        
        $db->execute("UPDATE `cart` SET `quantity` = ? WHERE `user_id` = ? AND `food_id` = ?", [$quantity, $user_id, $food_id]);
        return true;
    } else {
        if (isset($_SESSION['cart'][$food_id])) {
            $_SESSION['cart'][$food_id] = $quantity;
            return true;
        }
    }
    return false;
}

/**
 * Remove an item from the cart.
 * @param int $food_id
 * @return bool
 */
function remove_from_cart($food_id) {
    if (is_logged_in()) {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        $db->execute("DELETE FROM `cart` WHERE `user_id` = ? AND `food_id` = ?", [$user_id, $food_id]);
        return true;
    } else {
        if (isset($_SESSION['cart'][$food_id])) {
            unset($_SESSION['cart'][$food_id]);
            return true;
        }
    }
    return false;
}

/**
 * Retrieve items details currently in the cart.
 * @return array
 */
function get_cart_items() {
    $db = Database::getInstance();
    $items = [];
    
    if (is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT c.`food_id`, c.`quantity`, f.`name`, f.`price`, f.`image_path`, r.`name` AS `restaurant_name` 
                FROM `cart` c
                JOIN `foods` f ON c.`food_id` = f.`id`
                JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
                WHERE c.`user_id` = ?";
        $items = $db->queryAll($sql, [$user_id]);
    } else {
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $food_id => $quantity) {
                $sql = "SELECT f.`id` AS `food_id`, f.`name`, f.`price`, f.`image_path`, r.`name` AS `restaurant_name`
                        FROM `foods` f
                        JOIN `restaurants` r ON f.`restaurant_id` = r.`id`
                        WHERE f.`id` = ?";
                $food = $db->queryRow($sql, [$food_id]);
                if ($food) {
                    $food['quantity'] = $quantity;
                    $items[] = $food;
                }
            }
        }
    }
    
    // Append subtotal field for easier processing
    foreach ($items as &$item) {
        $item['subtotal'] = (float)$item['price'] * (int)$item['quantity'];
    }
    return $items;
}

/**
 * Total price calculation for all products in the cart.
 * @return float
 */
function get_cart_total() {
    $items = get_cart_items();
    $total = 0.0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

/**
 * Get distinct cart count or items count.
 * @return int
 */
function get_cart_count() {
    if (is_logged_in()) {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        return (int)$db->queryRow("SELECT SUM(`quantity`) FROM `cart` WHERE `user_id` = ?", [$user_id])['SUM(`quantity`)'] ?? 0;
    } else {
        return !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }
}

/**
 * Completely clear user's cart.
 */
function clear_cart() {
    if (is_logged_in()) {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        $db->execute("DELETE FROM `cart` WHERE `user_id` = ?", [$user_id]);
    } else {
        unset($_SESSION['cart']);
    }
}

/**
 * Transfer session items to DB upon logins
 * @param int $user_id
 */
function sync_cart_on_login($user_id) {
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $db = Database::getInstance();
        foreach ($_SESSION['cart'] as $food_id => $qty) {
            $item = $db->queryRow("SELECT `id`, `quantity` FROM `cart` WHERE `user_id` = ? AND `food_id` = ?", [$user_id, $food_id]);
            if ($item) {
                $new_qty = $item['quantity'] + $qty;
                $db->execute("UPDATE `cart` SET `quantity` = ? WHERE `id` = ?", [$new_qty, $item['id']]);
            } else {
                $db->execute("INSERT INTO `cart` (`user_id`, `food_id`, `quantity`) VALUES (?, ?, ?)", [$user_id, $food_id, $qty]);
            }
        }
        unset($_SESSION['cart']);
    }
}
