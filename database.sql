-- MediCare Pharmacy Database Schema
-- Created for CEP Assignment #4

CREATE DATABASE IF NOT EXISTS medicare_pharmacy;
USE medicare_pharmacy;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50)
);

-- Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    requires_prescription BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    prescription_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Cart Table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Contact Messages
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Data: Categories
INSERT INTO categories (name, description, icon) VALUES
('Medicines', 'Prescription and OTC medicines', '💊'),
('Vitamins & Supplements', 'Health supplements and vitamins', '🌿'),
('Personal Care', 'Skincare and hygiene products', '🧴'),
('Medical Devices', 'Blood pressure monitors, glucometers etc', '🩺'),
('Baby Care', 'Products for infants and toddlers', '👶'),
('First Aid', 'Bandages, antiseptics, first aid kits', '🩹');

-- Sample Data: Products
INSERT INTO products (category_id, name, description, price, stock, requires_prescription) VALUES
(1, 'Paracetamol 500mg', 'Pain reliever and fever reducer', 50.00, 200, FALSE),
(1, 'Amoxicillin 250mg', 'Antibiotic for bacterial infections', 150.00, 100, TRUE),
(1, 'Omeprazole 20mg', 'For acid reflux and stomach ulcers', 120.00, 150, FALSE),
(1, 'Metformin 500mg', 'For type 2 diabetes management', 90.00, 80, TRUE),
(2, 'Vitamin C 1000mg', 'Immune system booster', 350.00, 300, FALSE),
(2, 'Vitamin D3 5000IU', 'Bone health and immunity', 400.00, 250, FALSE),
(2, 'Omega-3 Fish Oil', 'Heart and brain health', 600.00, 150, FALSE),
(3, 'Sunscreen SPF 50', 'Broad spectrum sun protection', 750.00, 100, FALSE),
(3, 'Hand Sanitizer 500ml', '70% alcohol based sanitizer', 200.00, 400, FALSE),
(4, 'Digital Thermometer', 'Fast and accurate temperature reading', 800.00, 50, FALSE),
(4, 'Blood Pressure Monitor', 'Digital automatic BP monitor', 3500.00, 30, FALSE),
(5, 'Baby Diaper Rash Cream', 'Soothes and protects baby skin', 450.00, 120, FALSE),
(6, 'First Aid Kit', 'Complete kit with bandages and antiseptic', 1200.00, 60, FALSE);

-- Sample Admin User (password: admin123)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin User', 'admin@medicare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
