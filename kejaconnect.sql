-- Database schema for KejaConnect Property Management System
-- Generated with high fidelity for MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `kejaconnect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kejaconnect`;

-- Drop existing tables if they exist (clean slate)
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `documents`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `maintenance_requests`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `tenancies`;
DROP TABLE IF EXISTS `units`;
DROP TABLE IF EXISTS `properties`;
DROP TABLE IF EXISTS `users`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `role` ENUM('admin', 'landlord', 'tenant') NOT NULL,
  `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  `profile_photo` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PROPERTIES TABLE
CREATE TABLE IF NOT EXISTS `properties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `landlord_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `county` VARCHAR(100) NOT NULL,
  `property_type` ENUM('apartment', 'bedsitter', 'studio', 'maisonette', 'bungalow') NOT NULL,
  `total_units` INT NOT NULL DEFAULT 1,
  `description` TEXT NULL,
  `amenities` TEXT NULL, -- Stored as comma-separated or text description
  `photos` JSON NULL,    -- JSON array of photo URLs
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_landlord` (`landlord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. UNITS TABLE
CREATE TABLE IF NOT EXISTS `units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NOT NULL,
  `unit_number` VARCHAR(50) NOT NULL,
  `floor` INT NOT NULL DEFAULT 0,
  `bedrooms` INT NOT NULL DEFAULT 0,
  `bathrooms` INT NOT NULL DEFAULT 0,
  `rent_amount` DECIMAL(10, 2) NOT NULL,
  `deposit_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('vacant', 'occupied', 'maintenance') NOT NULL DEFAULT 'vacant',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  INDEX `idx_property` (`property_id`),
  INDEX `idx_unit_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TENANCIES TABLE
CREATE TABLE IF NOT EXISTS `tenancies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `unit_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `landlord_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `rent_amount` DECIMAL(10, 2) NOT NULL,
  `deposit_paid` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `deposit_amount` DECIMAL(10, 2) NOT NULL,
  `lease_document` VARCHAR(255) NULL,
  `status` ENUM('active', 'terminated', 'expired') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_tenant` (`tenant_id`),
  INDEX `idx_landlord_tenancy` (`landlord_id`),
  INDEX `idx_unit_tenancy` (`unit_id`),
  INDEX `idx_tenancy_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. PAYMENTS TABLE
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenancy_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `landlord_id` INT NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` ENUM('mpesa', 'bank', 'cash') NOT NULL,
  `mpesa_code` VARCHAR(50) NULL,
  `receipt_number` VARCHAR(50) NOT NULL UNIQUE,
  `month_paid_for` VARCHAR(20) NOT NULL, -- Format: YYYY-MM
  `status` ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'pending',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_payment_tenancy` (`tenancy_id`),
  INDEX `idx_payment_tenant` (`tenant_id`),
  INDEX `idx_payment_landlord` (`landlord_id`),
  INDEX `idx_payment_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. MAINTENANCE REQUESTS TABLE
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenancy_id` INT NOT NULL,
  `unit_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `landlord_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `category` ENUM('plumbing', 'electrical', 'structural', 'cleaning', 'other') NOT NULL DEFAULT 'other',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
  `landlord_notes` TEXT NULL,
  `photos` JSON NULL, -- JSON array of file paths
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_m_tenancy` (`tenancy_id`),
  INDEX `idx_m_landlord` (`landlord_id`),
  INDEX `idx_m_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. NOTICES TABLE
CREATE TABLE IF NOT EXISTS `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `recipient_id` INT NULL, -- NULL indicates broadcast to all eligible roles or general
  `tenancy_id` INT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('rent_reminder', 'eviction', 'maintenance', 'general') NOT NULL DEFAULT 'general',
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_recipient` (`recipient_id`),
  INDEX `idx_sender` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. DOCUMENTS TABLE
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uploader_id` INT NOT NULL,
  `related_to_id` INT NOT NULL,
  `related_to_type` ENUM('tenancy', 'property', 'unit', 'user') NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(100) NOT NULL,
  `file_size` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_uploader` (`uploader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. AUDIT LOGS TABLE
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL, -- Can be NULL for unauthenticated events (failed login attempts)
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_audit_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. RECOVERY TOKENS (For secure Forgot Password resets)
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  INDEX `idx_reset_email` (`email`),
  INDEX `idx_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- SEED DATA
-- Default passwords are encrypted with bcrypt. Plain text is: "Admin@123", "Landlord@123", "Tenant@123"
-- BCrypt cost = 10
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`, `status`) VALUES
('KejaConnect Admin', 'admin@kejaconnect.co.ke', '$2y$10$wT8Ksc6vO1G1336Z9Z7cO.iZ8K4G6Kz.UAnO88uT9wDsh6t0yS/uK', '+254712345678', 'admin', 'active'),
('Mwenda Joseph', 'mwenda.landlord@gmail.com', '$2y$10$kP/MAsb1hD1fT7B81Q9yFeB4zW2t9h.MvSOnIeYnO9bM47m98zS1C', '+254722112233', 'landlord', 'active'),
('Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '$2y$10$v4T2G6Ie88BSh3f6vU6bN.F9oYpWjH6OsnfP7kU6vYvJ2D7O4mZ/y', '+254733445566', 'tenant', 'active');

-- Properties seed
INSERT INTO `properties` (`landlord_id`, `name`, `address`, `county`, `property_type`, `total_units`, `description`, `amenities`, `photos`) VALUES
(2, 'Greenwood Apartments', 'Ngong Road, near Junction Mall', 'Nairobi', 'apartment', 2, 'Luxury modern apartments with fast fiber internet and spacious parking.', 'Fiber Optic, High-speed Lift, Solar Panels, 24/7 Guards', '["https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80"]'),
(2, 'Sunset Maisonettes', 'Section 9', 'Kiambu', 'maisonette', 1, 'Cozy gated community units ideal for growing families.', 'Borehole, Private Garden, Gated, Gym', '["https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80"]');

-- Units seed
INSERT INTO `units` (`property_id`, `unit_number`, `floor`, `bedrooms`, `bathrooms`, `rent_amount`, `deposit_amount`, `status`) VALUES
(1, 'A101', 0, 2, 2, 45000.00, 45000.00, 'occupied'),
(1, 'A102', 1, 3, 3, 55000.00, 55000.00, 'vacant'),
(2, 'M01', 0, 4, 4, 85000.00, 85000.00, 'vacant');

-- Tenancies seed
INSERT INTO `tenancies` (`unit_id`, `tenant_id`, `landlord_id`, `start_date`, `end_date`, `rent_amount`, `deposit_paid`, `deposit_amount`, `status`) VALUES
(1, 3, 2, '2026-01-01', '2026-12-31', 45000.00, 45000.00, 45000.00, 'active');

-- Payments seed
INSERT INTO `payments` (`tenancy_id`, `tenant_id`, `landlord_id`, `amount`, `payment_date`, `payment_method`, `mpesa_code`, `receipt_number`, `month_paid_for`, `status`, `notes`) VALUES
(1, 3, 2, 45000.00, '2026-05-01', 'mpesa', 'QRE3YHJ9KK', 'REC-2026-0001', '2026-05', 'confirmed', 'Paid on time.'),
(1, 3, 2, 45000.00, '2026-06-02', 'mpesa', 'QRF4YHN8ML', 'REC-2026-0002', '2026-06', 'pending', 'Please approve.');

-- Maintenance seed
INSERT INTO `maintenance_requests` (`tenancy_id`, `unit_id`, `tenant_id`, `landlord_id`, `title`, `description`, `priority`, `category`, `status`, `photos`) VALUES
(1, 1, 3, 2, 'Leaking kitchen tap', 'The main sink mixer tap is dripping continuously, wasting water and filling the under-sink cupboard.', 'medium', 'plumbing', 'open', '[]');

-- Notices seed
INSERT INTO `notices` (`sender_id`, `recipient_id`, `tenancy_id`, `subject`, `message`, `type`, `is_read`) VALUES
(1, NULL, NULL, 'System Launch', 'Welcome to the KejaConnect real-estate system!', 'general', FALSE),
(2, 3, 1, 'Water Shortage Warning', 'Water supply will be rationalized in Greenwood Apartments from 10 AM to 4 PM on Wednesday for regular tank maintenance.', 'general', FALSE);
