-- Database Schema for Food Delivery System
-- Created for Distinction-level university assignment
CREATE DATABASE IF NOT EXISTS `food_delivery` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food_delivery`;

-- Disable Foreign Key checks for clean drop
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `sepay_webhook_logs`;
DROP TABLE IF EXISTS `auth_tokens`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `foods`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `restaurants`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `contact_messages`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL DEFAULT '',
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADMINS TABLE
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'Super Admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2a. PERSISTENT LOGIN TOKENS
CREATE TABLE `auth_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `selector` CHAR(36) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_auth_tokens_selector` (`selector`),
  KEY `idx_auth_tokens_user_id` (`user_id`),
  KEY `idx_auth_tokens_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ADDRESSES TABLE
CREATE TABLE `addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `address_line1` VARCHAR(255) NOT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. RESTAURANTS TABLE
CREATE TABLE `restaurants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `cuisine_type` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `icon_class` VARCHAR(50) DEFAULT 'bi-egg-fried',
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. FOODS TABLE
CREATE TABLE `foods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `is_popular` TINYINT(1) DEFAULT 0,
  `is_latest` TINYINT(1) DEFAULT 1,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CART TABLE
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `food_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7a. WISHLIST TABLE
CREATE TABLE `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `food_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_food` (`user_id`, `food_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ORDERS TABLE
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) DEFAULT NULL,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
  `delivery_address` TEXT NOT NULL,
  `payment_method` ENUM('cod', 'card', 'bank_transfer') DEFAULT 'cod',
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_order_code` (`order_code`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDER_ITEMS TABLE
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `food_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. SEPAY WEBHOOK LOGS (idempotency / deduplication)
CREATE TABLE `sepay_webhook_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sepay_transaction_id` INT NOT NULL,
  `order_id` INT DEFAULT NULL,
  `payload` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_sepay_transaction_id` (`sepay_transaction_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. PAYMENTS TABLE
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. REVIEWS TABLE
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `restaurant_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. CONTACT_MESSAGES TABLE
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================
-- DEFAULT SEED DATA
-- ==========================================

-- Default Admin Account
INSERT INTO `users` (`name`, `username`, `email`, `password`, `phone`, `address`, `avatar`, `role`, `status`)
VALUES ('Admin Primary', 'admin_primary', 'admin@admin.com', '$2y$10$s4AN/LGaTqe53cnTaySHmer1.bjQUKwQ5U4aHunuIKJjUVbJKTfKC', '+1 555-9003', NULL, NULL, 'admin', 'active')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `description`, `icon_class`, `image_path`) VALUES
(1, 'Burgers', 'Delicious beef, chicken and veggie burgers.', 'bi-egg-fried', 'assets/images/cat_burgers.jpg'),
(2, 'Pizza', 'Hot and cheesy Italian pizzas.', 'bi-egg-fried', 'assets/images/cat_pizza.jpg'),
(3, 'Sushi & Asian', 'Fresh sushi and Asian delicacies.', 'bi-egg-fried', 'assets/images/cat_asian.jpg'),
(4, 'Desserts', 'Sweet treats and ice cream.', 'bi-egg-fried', 'assets/images/cat_desserts.jpg'),
(5, 'Drinks', 'Refreshing beverages and shakes.', 'bi-egg-fried', 'assets/images/cat_drinks.jpg');

-- Seed Restaurants
INSERT INTO `restaurants` (`id`, `name`, `description`, `cuisine_type`, `address`, `phone`, `image_path`, `status`) VALUES
(1, 'Burger King Deluxe', 'Flame-grilled beef burgers made your way.', 'American', '123 Main St, Metro City', '+1 555-0101', 'assets/images/rest_burgerking.jpg', 'active'),
(2, 'Bella Italia Pizza', 'Authentic stone-baked woodfire pizzas.', 'Italian', '456 Olive Rd, Little Italy', '+1 555-0102', 'assets/images/rest_bellaitalia.jpg', 'active'),
(3, 'Sakura Zen', 'Freshly sliced sashimi, sushi rolls and hot ramen.', 'Japanese', '789 Blossom Ave, Chinatown', '+1 555-0103', 'assets/images/rest_sakurazen.jpg', 'active'),
(4, 'Sweet Tooth Treats', 'Artisanal cakes, ice cream, and fresh waffles.', 'Desserts', '101 Sugar Lane, Sweetville', '+1 555-0104', 'assets/images/rest_sweettooth.jpg', 'active');

-- Seed Foods
INSERT INTO `foods` (`id`, `restaurant_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_popular`, `is_latest`, `is_available`) VALUES
-- Burger King Deluxe
(1, 1, 1, 'Classic Cheeseburger', 'Angus beef patty, cheddar, pickles, mustard, and ketchup on a toasted brioche bun.', 8.99, 'assets/images/food_cheeseburger.jpg', 1, 1, 1),
(2, 1, 1, 'Spicy Zinger Chicken', 'Crispy chicken breast, spicy mayo, lettuce, and jalapeños.', 9.49, 'assets/images/food_zinger.jpg', 1, 1, 1),
(3, 1, 5, 'Chocolate Milkshake', 'Creamy soft-serve vanilla blended with rich Belgian chocolate syrup.', 4.99, 'assets/images/food_shake.jpg', 0, 1, 1),
-- Bella Italia Pizza
(4, 2, 2, 'Margherita Masterpiece', 'San Marzano tomato sauce, fresh mozzarella, fresh basil, and extra virgin olive oil.', 12.99, 'assets/images/food_margherita.jpg', 1, 1, 1),
(5, 2, 2, 'Double Pepperoni Feast', 'Mozzarella, tomato base, loaded with spicy Italian pepperoni slices.', 14.99, 'assets/images/food_pepperoni.jpg', 1, 1, 1),
-- Sakura Zen
(6, 3, 3, 'Signature Salmon Sushi Box', '8 pieces of fresh Atlantic salmon nigiri and spicy salmon rolls.', 16.99, 'assets/images/food_sushi.jpg', 1, 1, 1),
(7, 3, 3, 'Tonkotsu Ramen Bowl', 'Rich pork bone broth, tender chashu pork belly slices, soft-boiled egg, and ramen noodles.', 13.49, 'assets/images/food_ramen.jpg', 0, 1, 1),
-- Sweet Tooth Treats
(8, 4, 4, 'Strawberry Waffle Fantasy', 'Freshly baked Belgian waffle topped with fresh strawberries, whipped cream, and Nutella drizzle.', 7.99, 'assets/images/food_waffle.jpg', 1, 1, 1),
(9, 4, 4, 'Choco Lava Cake', 'Warm chocolate cake with a molten chocolate center, served with vanilla bean gelato.', 6.50, 'assets/images/food_lavacake.jpg', 0, 1, 1);
