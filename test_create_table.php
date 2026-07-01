<?php
set_time_limit(600);

$mysqli = new mysqli("localhost", "root", "", "kejaconnect");

if ($mysqli->connect_errno) {
    die("Error: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

// Test 1: Check if users table exists
$result = $mysqli->query("SELECT COUNT(*) FROM users");
if ($result) {
    echo "✓ Users table exists\n";
} else {
    echo "✗ Users table does not exist. Creating...\n";
    $sql = "CREATE TABLE `users` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($mysqli->query($sql)) {
        echo "✓ Users table created\n";
    } else {
        echo "✗ Error: " . $mysqli->error . "\n";
    }
}

// List all tables
echo "\nTables in database:\n";
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo "  - " . $row[0] . "\n";
}

$mysqli->close();
?>
