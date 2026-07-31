-- SePay bank-transfer integration (run once on existing databases)

ALTER TABLE `orders`
  MODIFY COLUMN `payment_method` ENUM('cod', 'card', 'bank_transfer') NOT NULL DEFAULT 'cod',
  ADD COLUMN `order_code` VARCHAR(50) DEFAULT NULL AFTER `id`,
  ADD UNIQUE KEY `uk_order_code` (`order_code`);

CREATE TABLE IF NOT EXISTS `sepay_webhook_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sepay_transaction_id` INT NOT NULL,
  `order_id` INT DEFAULT NULL,
  `payload` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_sepay_transaction_id` (`sepay_transaction_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
