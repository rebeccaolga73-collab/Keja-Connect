<?php
$conn = mysqli_connect("localhost", "root", "", "kejaconnect");

if (!$conn) {
    die(json_encode(['error' => mysqli_connect_error()]));
}

$sql = "CREATE TABLE IF NOT EXISTS `users` (
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

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => 'users table created']);
} else {
    echo json_encode(['error' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>
