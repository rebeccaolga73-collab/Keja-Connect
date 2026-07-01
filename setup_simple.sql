CREATE DATABASE IF NOT EXISTS kejaconnect DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kejaconnect;

CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (full_name, email, password, phone, role, status) VALUES
    ('KejaConnect Admin', 'admin@kejaconnect.co.ke', '\\\.iZ8K4G6Kz.UAnO88uT9wDsh6t0yS/uK', '+254712345678', 'admin', 'active'),
    ('Mwenda Joseph', 'mwenda.landlord@gmail.com', '\\\/MAsb1hD1fT7B81Q9yFeB4zW2t9h.MvSOnIeYnO9bM47m98zS1C', '+254722112233', 'landlord', 'active'),
    ('Wanjiku Kamau', 'wanjiku.tenant@yahoo.com', '\\\.F9oYpWjH6OsnfP7kU6vYvJ2D7O4mZ/y', '+254733445566', 'tenant', 'active');

SELECT 'Setup Complete!' as status;
