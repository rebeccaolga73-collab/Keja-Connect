<?php
set_time_limit(300);

$mysqli = new mysqli("localhost", "root", "", "kejaconnect");

if ($mysqli->connect_errno) {
    http_response_code(500);
    die("<h1>Connection Error</h1><p>" . htmlspecialchars($mysqli->connect_error) . "</p>");
}

$mysqli->set_charset("utf8mb4");

// SQL statements to execute
$tables_sql = [
    // 1. USERS TABLE
    "CREATE TABLE IF NOT EXISTS `users` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 2. PROPERTIES TABLE
    "CREATE TABLE IF NOT EXISTS `properties` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `landlord_id` INT NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `address` VARCHAR(255) NOT NULL,
      `county` VARCHAR(100) NOT NULL,
      `property_type` ENUM('apartment', 'bedsitter', 'studio', 'maisonette', 'bungalow') NOT NULL,
      `total_units` INT NOT NULL DEFAULT 1,
      `description` TEXT NULL,
      `amenities` TEXT NULL,
      `photos` JSON NULL,
      `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_landlord` (`landlord_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 3. UNITS TABLE
    "CREATE TABLE IF NOT EXISTS `units` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 4. TENANCIES TABLE
    "CREATE TABLE IF NOT EXISTS `tenancies` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 5. PAYMENTS TABLE
    "CREATE TABLE IF NOT EXISTS `payments` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `tenancy_id` INT NOT NULL,
      `tenant_id` INT NOT NULL,
      `landlord_id` INT NOT NULL,
      `amount` DECIMAL(10, 2) NOT NULL,
      `payment_date` DATE NOT NULL,
      `payment_method` ENUM('mpesa', 'bank', 'cash') NOT NULL,
      `mpesa_code` VARCHAR(50) NULL,
      `receipt_number` VARCHAR(50) NOT NULL UNIQUE,
      `month_paid_for` VARCHAR(20) NOT NULL,
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 6. MAINTENANCE REQUESTS TABLE
    "CREATE TABLE IF NOT EXISTS `maintenance_requests` (
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
      `photos` JSON NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `resolved_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_m_tenancy` (`tenancy_id`),
      INDEX `idx_m_landlord` (`landlord_id`),
      INDEX `idx_m_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 7. NOTICES TABLE
    "CREATE TABLE IF NOT EXISTS `notices` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `sender_id` INT NOT NULL,
      `recipient_id` INT NULL,
      `tenancy_id` INT NULL,
      `subject` VARCHAR(150) NOT NULL,
      `message` TEXT NOT NULL,
      `type` ENUM('rent_reminder', 'eviction', 'maintenance', 'general') NOT NULL DEFAULT 'general',
      `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_recipient` (`recipient_id`),
      INDEX `idx_sender` (`sender_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 8. DOCUMENTS TABLE
    "CREATE TABLE IF NOT EXISTS `documents` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 9. AUDIT LOGS TABLE
    "CREATE TABLE IF NOT EXISTS `audit_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NULL,
      `action` VARCHAR(100) NOT NULL,
      `description` TEXT NOT NULL,
      `ip_address` VARCHAR(45) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
      INDEX `idx_audit_user` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    // 10. PASSWORD RESETS TABLE
    "CREATE TABLE IF NOT EXISTS `password_resets` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `email` VARCHAR(100) NOT NULL,
      `token` VARCHAR(64) NOT NULL,
      `expires_at` DATETIME NOT NULL,
      INDEX `idx_reset_email` (`email`),
      INDEX `idx_reset_token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

// Seed data SQL
$seed_sql = [
    // Insert users
    "INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`, `status`) VALUES
    ('KejaConnect Admin', 'admin@kejaconnect.co.ke', '\$2y\$10\$wT8Ksc6vO1G1336Z9Z7cO.iZ8K4G6Kz.UAnO88uT9wDsh6t0yS/uK', '+254712345678', 'admin', 'active'),
    ('Mwenda Joseph', 'mwenda.landlord@gmail.com', '\$2y\$10\$kP/MAsb1hD1fT7B81Q9yFeB4zW2t9h.MvSOnIeYnO9bM47m98zS1C', '+254722112233', 'landlord', 'active'),
    ('Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '\$2y\$10\$v4T2G6Ie88BSh3f6vU6bN.F9oYpWjH6OsnfP7kU6vYvJ2D7O4mZ/y', '+254733445566', 'tenant', 'active')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert properties
    "INSERT INTO `properties` (`landlord_id`, `name`, `address`, `county`, `property_type`, `total_units`, `description`, `amenities`, `photos`) VALUES
    (2, 'Greenwood Apartments', 'Ngong Road, near Junction Mall', 'Nairobi', 'apartment', 2, 'Luxury modern apartments with fast fiber internet and spacious parking.', 'Fiber Optic, High-speed Lift, Solar Panels, 24/7 Guards', '[\"https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80\"]'),
    (2, 'Sunset Maisonettes', 'Section 9', 'Kiambu', 'maisonette', 1, 'Cozy gated community units ideal for growing families.', 'Borehole, Private Garden, Gated, Gym', '[\"https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80\"]')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert units
    "INSERT INTO `units` (`property_id`, `unit_number`, `floor`, `bedrooms`, `bathrooms`, `rent_amount`, `deposit_amount`, `status`) VALUES
    (1, 'A101', 0, 2, 2, 45000.00, 45000.00, 'occupied'),
    (1, 'A102', 1, 3, 3, 55000.00, 55000.00, 'vacant'),
    (2, 'M01', 0, 4, 4, 85000.00, 85000.00, 'vacant')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert tenancies
    "INSERT INTO `tenancies` (`unit_id`, `tenant_id`, `landlord_id`, `start_date`, `end_date`, `rent_amount`, `deposit_paid`, `deposit_amount`, `status`) VALUES
    (1, 3, 2, '2026-01-01', '2026-12-31', 45000.00, 45000.00, 45000.00, 'active')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert payments
    "INSERT INTO `payments` (`tenancy_id`, `tenant_id`, `landlord_id`, `amount`, `payment_date`, `payment_method`, `mpesa_code`, `receipt_number`, `month_paid_for`, `status`, `notes`) VALUES
    (1, 3, 2, 45000.00, '2026-05-01', 'mpesa', 'QRE3YHJ9KK', 'REC-2026-0001', '2026-05', 'confirmed', 'Paid on time.'),
    (1, 3, 2, 45000.00, '2026-06-02', 'mpesa', 'QRF4YHN8ML', 'REC-2026-0002', '2026-06', 'pending', 'Please approve.')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert maintenance requests
    "INSERT INTO `maintenance_requests` (`tenancy_id`, `unit_id`, `tenant_id`, `landlord_id`, `title`, `description`, `priority`, `category`, `status`, `photos`) VALUES
    (1, 1, 3, 2, 'Leaking kitchen tap', 'The main sink mixer tap is dripping continuously, wasting water and filling the under-sink cupboard.', 'medium', 'plumbing', 'open', '[]')
    ON DUPLICATE KEY UPDATE id=id",
    
    // Insert notices
    "INSERT INTO `notices` (`sender_id`, `recipient_id`, `tenancy_id`, `subject`, `message`, `type`, `is_read`) VALUES
    (1, NULL, NULL, 'System Launch', 'Welcome to the KejaConnect real-estate system!', 'general', FALSE),
    (2, 3, 1, 'Water Shortage Warning', 'Water supply will be rationalized in Greenwood Apartments from 10 AM to 4 PM on Wednesday for regular tank maintenance.', 'general', FALSE)
    ON DUPLICATE KEY UPDATE id=id"
];

$output = "<h1 style='color: #2c3e50; font-family: sans-serif; margin: 20px;'>KejaConnect Database Setup</h1>";
$output .= "<div style='font-family: sans-serif; margin: 20px; background: #f8f9fa; padding: 20px; border-radius: 5px;'>";

$tables_created = 0;
$tables_failed = 0;

// Create tables
$output .= "<h2>Creating Tables...</h2><ul>";
foreach ($tables_sql as $sql) {
    $table_name = preg_match('/`(\w+)`/', $sql, $matches) ? $matches[1] : 'unknown';
    
    if ($mysqli->query($sql)) {
        $output .= "<li style='color: green;'>✓ Table <strong>$table_name</strong> created successfully</li>";
        $tables_created++;
    } else {
        if (strpos($mysqli->error, 'already exists') !== false) {
            $output .= "<li style='color: orange;'>ℹ Table <strong>$table_name</strong> already exists</li>";
            $tables_created++;
        } else {
            $output .= "<li style='color: red;'>✗ Error creating <strong>$table_name</strong>: " . htmlspecialchars($mysqli->error) . "</li>";
            $tables_failed++;
        }
    }
}
$output .= "</ul>";

// Insert seed data
$output .= "<h2>Inserting Seed Data...</h2><ul>";
$seed_inserted = 0;
foreach ($seed_sql as $sql) {
    $type = preg_match('/INTO\s+`(\w+)`/', $sql, $matches) ? $matches[1] : 'unknown';
    
    if ($mysqli->query($sql)) {
        $output .= "<li style='color: green;'>✓ Data for <strong>$type</strong> inserted</li>";
        $seed_inserted++;
    } else {
        if (strpos($mysqli->error, 'Duplicate entry') !== false) {
            $output .= "<li style='color: orange;'>ℹ Data for <strong>$type</strong> already exists</li>";
        } else {
            $output .= "<li style='color: red;'>✗ Error inserting data for <strong>$type</strong>: " . htmlspecialchars($mysqli->error) . "</li>";
        }
    }
}
$output .= "</ul>";

// Verify all tables
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
$table_count = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $table_count = $row['cnt'];
}

$user_count = 0;
$result2 = $mysqli->query("SELECT COUNT(*) as cnt FROM users");
if ($result2) {
    $row2 = $result2->fetch_assoc();
    $user_count = $row2['cnt'];
}

$output .= "<h2 style='color: #27ae60;'>✓ Setup Complete!</h2>";
$output .= "<p><strong>Summary:</strong></p>";
$output .= "<ul>";
$output .= "<li>Tables created: <strong>$table_count</strong></li>";
$output .= "<li>Users in database: <strong>$user_count</strong></li>";
$output .= "</ul>";

$output .= "<h3>Test Credentials:</h3>";
$output .= "<table style='border-collapse: collapse; width: 100%;'>";
$output .= "<tr style='background: #ecf0f1;'><th style='border: 1px solid #bdc3c7; padding: 10px; text-align: left;'>Role</th><th style='border: 1px solid #bdc3c7; padding: 10px; text-align: left;'>Email</th><th style='border: 1px solid #bdc3c7; padding: 10px; text-align: left;'>Password</th></tr>";
$output .= "<tr><td style='border: 1px solid #bdc3c7; padding: 10px;'>Admin</td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>admin@kejaconnect.co.ke</code></td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>Admin@123</code></td></tr>";
$output .= "<tr><td style='border: 1px solid #bdc3c7; padding: 10px;'>Landlord</td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>mwenda.landlord@gmail.com</code></td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>Landlord@123</code></td></tr>";
$output .= "<tr><td style='border: 1px solid #bdc3c7; padding: 10px;'>Tenant</td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>wanjiku.tenant@yahoo.com</code></td><td style='border: 1px solid #bdc3c7; padding: 10px;'><code>Tenant@123</code></td></tr>";
$output .= "</table>";

$output .= "<p style='margin-top: 20px;'><a href='http://localhost/kejaconnect/login.php' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Login</a></p>";

$output .= "</div>";

echo $output;

$mysqli->close();
?>
