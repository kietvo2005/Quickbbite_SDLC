ALTER TABLE `orders` 
MODIFY COLUMN `payment_method` ENUM('cod','card','bank_transfer') NOT NULL DEFAULT 'cod';
SELECT id, payment_method, order_code FROM orders WHERE id IN (25,26,27,30);
SELECT * FROM sepay_webhook_logs ORDER BY id DESC LIMIT 5;
CREATE TABLE `sepay_webhook_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sepay_transaction_id` INT NOT NULL,
  `order_id` INT NULL,
  `payload` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_sepay_transaction` (`sepay_transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;