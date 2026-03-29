-- Med-Quick - Secure Pharmacy POS
-- Database Schema
--
-- Author: Gemini CLI
-- Version: 1.0.0
--
-- Constraints:
-- Backend: Pure PHP 8.2
-- Database: MariaDB
--
-- Notes:
-- - `DECIMAL(10, 2)` is used for financial fields.
-- - Fields intended for encryption are `VARCHAR(512)` or `TEXT`.
-- - Timestamps are used for tracking record creation and updates.

--
-- Database: `med_quick_pos`
--
CREATE DATABASE IF NOT EXISTS `med_quick_pos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `med_quick_pos`;

-- =================================================================
-- Table structure for `users`
-- Role-Based Access Control (RBAC) included.
-- =g================================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name_encrypted` VARCHAR(512) NOT NULL,
  `email_encrypted` VARCHAR(512) NOT NULL UNIQUE,
  `role` ENUM('admin', 'pharmacist', 'cashier') NOT NULL DEFAULT 'cashier',
  `is_active` BOOLEAN NOT NULL DEFAULT true,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `generic_salts`
-- =================================================================
CREATE TABLE `generic_salts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `categories`
-- =================================================================
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `parent_id` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `manufacturers`
-- =================================================================
CREATE TABLE `manufacturers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL UNIQUE,
  `contact_person_encrypted` VARCHAR(512),
  `phone_encrypted` VARCHAR(512),
  `email_encrypted` VARCHAR(512),
  `address_encrypted` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `medicines`
-- =================================================================
CREATE TABLE `medicines` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `barcode` VARCHAR(100) UNIQUE,
  `strength` VARCHAR(50), -- e.g., "500mg"
  `category_id` INT UNSIGNED,
  `manufacturer_id` INT UNSIGNED,
  `generic_salt_id` INT UNSIGNED,
  `mrp` DECIMAL(10, 2) NOT NULL,
  `tax_rate` DECIMAL(5, 2) NOT NULL DEFAULT 5.00, -- Percentage
  `requires_prescription` BOOLEAN NOT NULL DEFAULT false,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`generic_salt_id`) REFERENCES `generic_salts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `suppliers`
-- =================================================================
CREATE TABLE `suppliers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL UNIQUE,
  `contact_person_encrypted` VARCHAR(512),
  `phone_encrypted` VARCHAR(512),
  `email_encrypted` VARCHAR(512),
  `address_encrypted` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `purchases`
-- =================================================================
CREATE TABLE `purchases` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `supplier_id` INT UNSIGNED,
  `purchase_date` DATE NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `note_encrypted` TEXT,
  `user_id` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;


-- =================================================================
-- Table structure for `stock_batches`
-- Tracks medicine batches, expiry, and costs.
-- =================================================================
CREATE TABLE `stock_batches` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `medicine_id` INT UNSIGNED NOT NULL,
  `purchase_id` INT UNSIGNED,
  `batch_number` VARCHAR(100) NOT NULL,
  `expiry_date` DATE NOT NULL,
  `quantity` INT NOT NULL,
  `cost_price` DECIMAL(10, 2) NOT NULL,
  `selling_price` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `medicine_batch` (`medicine_id`, `batch_number`),
  FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `sales`
-- =================================================================
CREATE TABLE `sales` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_token` VARCHAR(64) NOT NULL UNIQUE, -- Idempotency Key
  `customer_name_encrypted` VARCHAR(512),
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `total_tax` DECIMAL(10, 2) NOT NULL,
  `grand_total` DECIMAL(10, 2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'online') NOT NULL,
  `user_id` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `sale_items`
-- =================================================================
CREATE TABLE `sale_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT UNSIGNED NOT NULL,
  `stock_batch_id` INT UNSIGNED NOT NULL,
  `medicine_name` VARCHAR(150) NOT NULL, -- Denormalized for reporting
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `tax` DECIMAL(10, 2) NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`stock_batch_id`) REFERENCES `stock_batches`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `security_logs`
-- =================================================================
CREATE TABLE `security_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `ip_address` VARCHAR(45) NOT NULL,
  `event_type` VARCHAR(100) NOT NULL, -- e.g., 'LOGIN_SUCCESS', 'LOGIN_FAIL', 'SENSITIVE_DATA_ACCESS'
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =================================================================
-- Table structure for `bank_transactions`
-- =================================================================
CREATE TABLE `bank_transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_date` DATETIME NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `debit` DECIMAL(10, 2) DEFAULT 0.00,
  `credit` DECIMAL(10, 2) DEFAULT 0.00,
  `balance_after` DECIMAL(10, 2) NOT NULL,
  `related_sale_id` INT UNSIGNED,
  `related_purchase_id` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`related_sale_id`) REFERENCES `sales`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`related_purchase_id`) REFERENCES `purchases`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---
-- END OF SCHEMA
-- ---
