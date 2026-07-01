<?php
$mysqli = new mysqli("localhost", "root", "", "kejaconnect");

if ($mysqli->connect_errno) {
    die("Error: " . $mysqli->connect_error);
}

// Create database if it doesn't exist
$mysqli->query("CREATE DATABASE IF NOT EXISTS kejaconnect");
$mysqli->select_db("kejaconnect");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  role ENUM('admin', 'landlord', 'tenant') NOT NULL,
  status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  profile_photo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_role (role),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql)) {
    echo "✓ Users table created\n";
} else {
    echo "Error: " . $mysqli->error . "\n";
}

// Insert test users
$users = [
    ['KejaConnect Admin', 'admin@kejaconnect.co.ke', '$2y$10$aNbgROovcapjivEOa5NZb.2drvLofAZEtQE1Z1aAnMTrx5tG5u7z2', '+254712345678', 'admin'],
    ['Mwenda Joseph', 'mwenda.landlord@gmail.com', '$2y$10$5vEW2Zwg8.DJGWsPF1b/yuCRDYvknhKg9MbHE3LXr8I3o3TKWQ3kS', '+254722112233', 'landlord'],
    ['Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '$2y$10$qSrdUh4USsBOSZ/QD7Rhh.pL/NUGDMVlxzqkDtZ9JCMPRzDWBj2aO', '+254733445566', 'tenant']
];

foreach ($users as $user) {
    $sql = "INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssss", $user[0], $user[1], $user[2], $user[3], $user[4]);
    
    if ($stmt->execute()) {
        echo "✓ User inserted: {$user[0]}\n";
    } else {
        if (strpos($stmt->error, 'Duplicate') === false) {
            echo "Error: " . $stmt->error . "\n";
        } else {
            echo "ℹ User already exists: {$user[0]}\n";
        }
    }
    $stmt->close();
}

// Verify
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM users");
$row = $result->fetch_assoc();
echo "\nTotal users in database: " . $row['cnt'] . "\n";

// Show tables
echo "\nTables in database:\n";
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo "  - " . $row[0] . "\n";
}

$mysqli->close();
?>
