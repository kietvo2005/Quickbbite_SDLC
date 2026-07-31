-- Migration: add admin-required user metadata and default admin record
USE `food_delivery`;

ALTER TABLE `users`
  ADD COLUMN `name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`,
  ADD COLUMN `address` VARCHAR(255) DEFAULT NULL AFTER `phone`,
  ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

INSERT INTO `users` (`name`, `username`, `email`, `password`, `phone`, `address`, `avatar`, `role`, `status`)
VALUES (
  'Admin Primary',
  'admin_primary',
  'admin@admin.com',
  '$2y$10$s4AN/LGaTqe53cnTaySHmer1.bjQUKwQ5U4aHunuIKJjUVbJKTfKC',
  '+1 555-9003',
  NULL,
  NULL,
  'admin',
  'active'
)
ON DUPLICATE KEY UPDATE
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `status` = VALUES(`status`);
