CREATE DATABASE IF NOT EXISTS `meddirect` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `meddirect`;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  profile_picture VARCHAR(255) DEFAULT NULL,
  address VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  remember_token VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category_type ENUM('liquid','solid') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  category_id INT NOT NULL,
  vendor_name VARCHAR(160) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  availability INT NOT NULL DEFAULT 0,
  description TEXT NOT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_medicines_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  medicine_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cart_user_medicine (user_id, medicine_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  shipping_address VARCHAR(255) NOT NULL,
  status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  payment_method VARCHAR(60) NOT NULL,
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  medicine_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(60) NOT NULL,
  transaction_id VARCHAR(80) NOT NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (id, name, email, password_hash, role, address, phone) VALUES
(1, 'Admin User', 'admin@meddirect.test', '$2y$10$0QyjULIOJ2exBRsVOq0Z.ecHxS7MFT8N4o/A4j4YsvmkUiS1p82h2', 'admin', 'MedDirect HQ, Dhaka', '01700000000'),
(2, 'Customer User', 'customer@meddirect.test', '$2y$10$0QyjULIOJ2exBRsVOq0Z.ecHxS7MFT8N4o/A4j4YsvmkUiS1p82h2', 'customer', 'Banani, Dhaka', '01800000000'),
(3, 'Pharmacy Manager', 'manager@meddirect.test', '$2y$10$0QyjULIOJ2exBRsVOq0Z.ecHxS7MFT8N4o/A4j4YsvmkUiS1p82h2', 'admin', 'MedDirect Warehouse, Dhaka', '01711111111'),
(4, 'Nadia Rahman', 'nadia@meddirect.test', '$2y$10$0QyjULIOJ2exBRsVOq0Z.ecHxS7MFT8N4o/A4j4YsvmkUiS1p82h2', 'customer', 'Dhanmondi, Dhaka', '01811111111'),
(5, 'Samiul Karim', 'samiul@meddirect.test', '$2y$10$0QyjULIOJ2exBRsVOq0Z.ecHxS7MFT8N4o/A4j4YsvmkUiS1p82h2', 'customer', 'Uttara, Dhaka', '01911111111');

INSERT IGNORE INTO categories (id, name, category_type) VALUES
(1, 'Aspirin genre', 'solid'),
(2, 'Paracetamol genre', 'solid'),
(3, 'Cough syrup genre', 'liquid'),
(4, 'Antacid genre', 'liquid'),
(5, 'Vitamin genre', 'solid');

INSERT IGNORE INTO medicines (id, name, category_id, vendor_name, price, availability, description, image_path) VALUES
(1, 'Aspirin 75mg Tablets', 1, 'Square Pharma', 60.00, 120, 'Low-dose aspirin tablets for prescribed cardiovascular support.', 'uploads/medicines/aspirin.svg'),
(2, 'Paracetamol 500mg', 2, 'Beximco Pharma', 25.00, 200, 'Fever and pain relief tablet for adult use.', 'uploads/medicines/paracetamol.svg'),
(3, 'Relief Cough Syrup', 3, 'Acme Laboratories', 95.00, 45, 'Liquid cough remedy for dry cough symptoms.', 'uploads/medicines/cough.svg'),
(4, 'Digest Antacid Suspension', 4, 'Incepta Pharma', 130.00, 32, 'Liquid antacid suspension for acidity relief.', 'uploads/medicines/antacid.svg'),
(5, 'Vitamin C 1000mg', 5, 'Healthcare Pharma', 180.00, 75, 'Solid vitamin supplement for immune support.', 'uploads/medicines/vitamin.svg');

INSERT IGNORE INTO orders (id, user_id, total_amount, shipping_address, status, payment_method, order_date) VALUES
(1, 2, 145.00, 'Banani, Dhaka', 'pending', 'bKash', '2026-05-15 10:30:00'),
(2, 4, 180.00, 'Dhanmondi, Dhaka', 'accepted', 'Cash on Delivery', '2026-05-14 13:20:00'),
(3, 5, 130.00, 'Uttara, Dhaka', 'rejected', 'Nagad', '2026-05-13 17:45:00');

INSERT IGNORE INTO order_items (id, order_id, medicine_id, quantity, unit_price) VALUES
(1, 1, 2, 2, 25.00),
(2, 1, 3, 1, 95.00),
(3, 2, 5, 1, 180.00),
(4, 3, 4, 1, 130.00);

INSERT IGNORE INTO payments (id, order_id, amount, payment_method, transaction_id) VALUES
(1, 1, 145.00, 'bKash', 'DEMO-BKASH-001'),
(2, 2, 180.00, 'Cash on Delivery', 'DEMO-COD-002'),
(3, 3, 130.00, 'Nagad', 'DEMO-NAGAD-003');
