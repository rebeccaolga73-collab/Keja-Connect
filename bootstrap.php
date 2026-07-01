<?php
// Direct minimal setup
$mysqli = new mysqli("localhost", "root", "");

if ($mysqli->connect_errno) {
    http_response_code(500);
    die("Connection failed: " . $mysqli->connect_error);
}

// Select database
$mysqli->select_db("kejaconnect");

// Create users table - simple version
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'landlord', 'tenant') DEFAULT 'tenant',
    status ENUM('active', 'suspended') DEFAULT 'active',
    profile_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($mysqli->query($users_table)) {
    echo "✓ Users table created\n";
} else {
    echo "✗ Error: " . $mysqli->error . "\n";
}

// Try to insert a test admin user
$password_hash = password_hash('password123', PASSWORD_BCRYPT);
$insert_user = "INSERT INTO users (full_name, email, password, phone, role, status) 
                VALUES ('Admin User', 'admin@kejaconnect.com', '$password_hash', '+254700000000', 'admin', 'active')
                ON DUPLICATE KEY UPDATE id=id;";

if ($mysqli->query($insert_user)) {
    echo "✓ Test admin user inserted\n";
} else {
    if (strpos($mysqli->error, 'Duplicate entry') !== false) {
        echo "ℹ Admin user already exists\n";
    } else {
        echo "✗ Error: " . $mysqli->error . "\n";
    }
}

// List tables
$result = $mysqli->query("SHOW TABLES");
if ($result) {
    echo "\n✓ Tables in database:\n";
    while ($row = $result->fetch_row()) {
        echo "  - " . $row[0] . "\n";
    }
}

$mysqli->close();
?>
