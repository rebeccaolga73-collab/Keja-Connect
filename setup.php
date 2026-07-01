<?php
/**
 * KejaConnect Professional Database Setup
 * Handles existing tables by cleaning and reinitializing
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

header('Content-Type: text/html; charset=utf-8');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'kejaconnect';

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>KejaConnect Database Setup</title>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; max-width: 900px; margin: 0; padding: 20px; background: #f0f2f5; }
.container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
h1 { color: #1a73e8; border-bottom: 3px solid #1a73e8; padding-bottom: 10px; }
h2 { color: #333; margin-top: 30px; }
.success { background: #d4edda; border-left: 4px solid #28a745; padding: 12px; margin: 8px 0; color: #155724; border-radius: 4px; }
.error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 12px; margin: 8px 0; color: #721c24; border-radius: 4px; }
.info { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 12px; margin: 8px 0; color: #0c5460; border-radius: 4px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th { background: #1a73e8; color: white; padding: 12px; text-align: left; }
td { padding: 12px; border-bottom: 1px solid #ddd; }
tr:hover { background: #f9f9f9; }
.credentials { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; margin: 15px 0; border-radius: 4px; }
.button { display: inline-block; background: #1a73e8; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 15px; }
</style>
</head>
<body>
<div class="container">
<h1>🏢 KejaConnect Database Setup</h1>

<?php

// Connect to MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    echo '<div class="error"><strong>❌ Connection Error:</strong> ' . htmlspecialchars($mysqli->connect_error) . '</div>';
    echo '</div></body></html>';
    exit;
}

echo '<div class="success"><strong>✓ Connected to MySQL</strong></div>';
$mysqli->set_charset("utf8mb4");

// Step 1: Create database
echo '<h2>Step 1: Database Setup</h2>';

if ($mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo '<div class="success">✓ Database <strong>' . $db_name . '</strong> exists and is ready</div>';
} else {
    echo '<div class="error">✗ Error creating database: ' . htmlspecialchars($mysqli->error) . '</div>';
}

$mysqli->select_db($db_name);

// Step 2: Create all tables
echo '<h2>Step 2: Creating Tables</h2>';

// Disable FK checks for setup
$mysqli->query("SET FOREIGN_KEY_CHECKS=0");

// Define all tables
$tables_sql = [
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
    
    "CREATE TABLE IF NOT EXISTS `payments` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `tenancy_id` INT NOT NULL,
      `tenant_id` INT NOT NULL,
      `landlord_id` INT NOT NULL,
      `amount` DECIMAL(10, 2) NOT NULL,
      `payment_date` DATE NOT NULL,
      `payment_method` ENUM('mpesa', 'bank', 'cash') NOT NULL,
      `receipt_number` VARCHAR(50) NULL UNIQUE,
      `notes` TEXT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`tenancy_id`) REFERENCES `tenancies` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_tenancy_payment` (`tenancy_id`),
      INDEX `idx_tenant_payment` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `maintenance_requests` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `unit_id` INT NOT NULL,
      `tenant_id` INT NOT NULL,
      `landlord_id` INT NOT NULL,
      `title` VARCHAR(150) NOT NULL,
      `description` TEXT NOT NULL,
      `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
      `status` ENUM('open', 'assigned', 'in_progress', 'completed', 'rejected') NOT NULL DEFAULT 'open',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `completed_at` TIMESTAMP NULL,
      FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`landlord_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_unit_maintenance` (`unit_id`),
      INDEX `idx_maintenance_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `notices` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `sender_id` INT NOT NULL,
      `recipient_id` INT NOT NULL,
      `title` VARCHAR(200) NOT NULL,
      `message` TEXT NOT NULL,
      `notice_type` ENUM('payment_reminder', 'maintenance', 'lease_renewal', 'eviction', 'other') NOT NULL,
      `read_at` TIMESTAMP NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      INDEX `idx_recipient` (`recipient_id`),
      INDEX `idx_sender` (`sender_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
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
    
    "CREATE TABLE IF NOT EXISTS `password_resets` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `email` VARCHAR(100) NOT NULL,
      `token` VARCHAR(64) NOT NULL,
      `expires_at` DATETIME NOT NULL,
      INDEX `idx_reset_email` (`email`),
      INDEX `idx_reset_token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

$tables_created = 0;
foreach ($tables_sql as $sql) {
    if ($mysqli->query($sql)) {
        $tables_created++;
    } else {
        echo '<div class="error">✗ Error creating table: ' . htmlspecialchars($mysqli->error) . '</div>';
    }
}

echo '<div class="success">✓ All tables created (' . $tables_created . '/10)</div>';

// Re-enable FK checks
$mysqli->query("SET FOREIGN_KEY_CHECKS=1");

// Step 3: Insert test data
echo '<h2>Step 3: Inserting Test Data</h2>';

// Password hashes: Admin@123, Landlord@123, Tenant@123
$admin_hash = '$2y$10$aNbgROovcapjivEOa5NZb.2drvLofAZEtQE1Z1aAnMTrx5tG5u7z2';
$landlord_hash = '$2y$10$5vEW2Zwg8.DJGWsPF1b/yuCRDYvknhKg9MbHE3LXr8I3o3TKWQ3kS';
$tenant_hash = '$2y$10$qSrdUh4USsBOSZ/QD7Rhh.pL/NUGDMVlxzqkDtZ9JCMPRzDWBj2aO';

// Insert users
$user_sql = "INSERT INTO users (full_name, email, password, phone, role, status) VALUES
    ('KejaConnect Admin', 'admin@kejaconnect.co.ke', '$admin_hash', '+254712345678', 'admin', 'active'),
    ('Mwenda Joseph', 'mwenda.landlord@gmail.com', '$landlord_hash', '+254722112233', 'landlord', 'active'),
    ('Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '$tenant_hash', '+254733445566', 'tenant', 'active')";

if ($mysqli->query($user_sql)) {
    echo '<div class="success">✓ Users created (3/3)</div>';
} else {
    echo '<div class="error">✗ Error: ' . htmlspecialchars($mysqli->error) . '</div>';
}

// Insert properties
$property_sql = "INSERT INTO properties (landlord_id, name, address, county, property_type, total_units, description, amenities, status) VALUES
    (2, 'Westlands Premium Apartments', '123 Westlands Ave, Nairobi', 'Nairobi', 'apartment', 3, 'Modern 3-unit apartment complex', 'WiFi, Parking, 24/7 Security, Water Tank', 'active'),
    (2, 'Kilimani Garden Units', '456 Kilimani Road, Nairobi', 'Nairobi', 'bedsitter', 5, '5-unit bedsitter complex', 'Parking, Generator, Water Supply', 'active')";

if ($mysqli->query($property_sql)) {
    echo '<div class="success">✓ Properties created (2/2)</div>';
} else {
    echo '<div class="error">✗ Error: ' . htmlspecialchars($mysqli->error) . '</div>';
}

// Insert units
$units_sql = "INSERT INTO units (property_id, unit_number, floor, bedrooms, bathrooms, rent_amount, deposit_amount, status) VALUES
    (1, 'A1', 0, 2, 1, 45000.00, 45000.00, 'occupied'),
    (1, 'B2', 1, 3, 2, 65000.00, 65000.00, 'vacant'),
    (1, 'C3', 2, 2, 1, 50000.00, 50000.00, 'occupied'),
    (2, '101', 1, 1, 1, 15000.00, 15000.00, 'occupied'),
    (2, '102', 1, 1, 1, 15000.00, 15000.00, 'vacant')";

if ($mysqli->query($units_sql)) {
    echo '<div class="success">✓ Units created (5/5)</div>';
} else {
    echo '<div class="error">✗ Error: ' . htmlspecialchars($mysqli->error) . '</div>';
}

// Insert tenancy
$tenancy_sql = "INSERT INTO tenancies (unit_id, tenant_id, landlord_id, start_date, end_date, rent_amount, deposit_paid, deposit_amount, status) VALUES
    (1, 3, 2, '2025-01-01', '2026-12-31', 45000.00, 45000.00, 45000.00, 'active')";

if ($mysqli->query($tenancy_sql)) {
    echo '<div class="success">✓ Tenancies created (1/1)</div>';
} else {
    echo '<div class="error">✗ Error: ' . htmlspecialchars($mysqli->error) . '</div>';
}

// Insert payments
$payment_sql = "INSERT INTO payments (tenancy_id, tenant_id, landlord_id, amount, payment_date, payment_method, receipt_number) VALUES
    (1, 3, 2, 45000.00, '2025-01-15', 'mpesa', 'RCP001'),
    (1, 3, 2, 45000.00, '2025-02-15', 'mpesa', 'RCP002')";

if ($mysqli->query($payment_sql)) {
    echo '<div class="success">✓ Payments created (2/2)</div>';
} else {
    echo '<div class="error">✗ Error: ' . htmlspecialchars($mysqli->error) . '</div>';
}

// Step 4: Verification
echo '<h2>Step 4: Verification</h2>';

$counts = [];
$tables_list = ['users', 'properties', 'units', 'tenancies', 'payments', 'maintenance_requests', 'notices', 'documents', 'audit_logs', 'password_resets'];

foreach ($tables_list as $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM `$table`");
    if ($result) {
        $row = $result->fetch_assoc();
        $counts[$table] = $row['cnt'];
    }
}

echo '<table>';
echo '<tr><th>Table Name</th><th>Records</th></tr>';
foreach ($counts as $table => $count) {
    $icon = $count > 0 ? '✓' : '○';
    echo '<tr><td>' . htmlspecialchars($table) . '</td><td>' . $icon . ' ' . $count . '</td></tr>';
}
echo '</table>';

// Display credentials
echo '<h2>Test Accounts</h2>';
echo '<div class="credentials">
<strong>Admin Account</strong><br>
Email: <code>admin@kejaconnect.co.ke</code><br>
Password: <code>Admin@123</code>
</div>';

echo '<div class="credentials">
<strong>Landlord Account</strong><br>
Email: <code>mwenda.landlord@gmail.com</code><br>
Password: <code>Landlord@123</code>
</div>';

echo '<div class="credentials">
<strong>Tenant Account</strong><br>
Email: <code>wanjiku.tenant@yahoo.com</code><br>
Password: <code>Tenant@123</code>
</div>';

// Complete message
echo '<h2 style="color: #28a745;">✓ Setup Complete!</h2>';
echo '<div class="success"><strong>Your database is ready to use.</strong></div>';
echo '<a href="login.php" class="button">Go to Login →</a>';

$mysqli->close();

?>

</div>
</body>
</html>
