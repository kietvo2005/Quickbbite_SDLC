-- Migration: add admin-required user metadata and default admin record
USE `food_delivery`;

ALTER TABLE `users`
  ADD COLUMN `name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`,
  ADD COLUMN `address` VARCHAR(255) DEFAULT NULL AFTER `phone`,
  ADD COLUMN `last_login` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;


