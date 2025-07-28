CREATE DATABASE IF NOT EXISTS inventoryOrderManagement;
USE inventoryOrderManagement;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    role ENUM('Admin', 'Employee', 'Customer') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    supplier_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT NOT NULL DEFAULT 10,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    status ENUM('pending', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('Credit Card', 'PayPal', 'Bank Transfer') DEFAULT 'Credit Card',
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'shipped', 'delivered', 'cancelled') NOT NULL,
    change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (firstName, lastName, email, phone, address, role, password) 
VALUES ('Admin', 'User', 'admin@gmail.com', '1234567890', 'Admin Address', 'Admin', MD5('Admin123'))
ON DUPLICATE KEY UPDATE email = 'admin@gmail.com';

-- Insert sample suppliers
INSERT INTO suppliers (supplier_id, company_name, contact_person, email, phone, address) VALUES
(1, 'Puspa.pvt', 'Puspa Kutu', 'puspa@puspa.com', '9800000001', 'Kathmandu, Nepal'),
(2, 'TechCorp Solutions', 'Aayushman Shrestha', 'aayushman@techcorp.com', '9800000002', 'Sanepa, Lalitpur'),
(3, 'Global Electronics', 'Swornika Rajbhandari', 'sworrb@globalelec.com', '9800000003', 'New Road, Kathmandu'),
(4, 'Premium Parts Ltd', 'Sajit Rahaman', 'Sajeet@premium.com', '9800000004', 'Bhaktapur, Nepal'),
(5, 'Innovation Inc', 'Abhay Shrestha', 'Avay@innovation.com', '9800000005', 'Pokhara, Nepal');

-- Insert sample products
INSERT INTO products (name, description, category, price, supplier_id) VALUES
('Smartphone X', 'Latest model with advanced features', 'Electronics', 499.99, 3),
('Laptop Pro', 'High performance laptop for professionals', 'Electronics', 899.99, 2),
('Bluetooth Speaker', 'Portable wireless speaker', 'Electronics', 59.99, 4),
('LED TV 42"', 'Full HD LED television', 'Electronics', 299.99, 3),
('Running Shoes', 'Comfortable sports shoes', 'Sports', 79.99, 1),
('Yoga Mat', 'Eco-friendly yoga mat', 'Sports', 19.99, 5),
('Football', 'Official size football', 'Sports', 24.99, 1),
('T-shirt Classic', '100% cotton t-shirt', 'Clothing', 14.99, 1),
('Jeans Slim Fit', 'Stylish slim fit jeans', 'Clothing', 39.99, 4),
('Winter Jacket', 'Warm and waterproof jacket', 'Clothing', 89.99, 2),
('Cookware Set', 'Non-stick cookware set', 'Home', 49.99, 5),
('Table Lamp', 'Modern design table lamp', 'Home', 29.99, 3),
('Bookshelf', 'Wooden bookshelf for home', 'Home', 99.99, 4),
('Novel: The Journey', 'Bestselling fiction novel', 'Books', 9.99, 5),
('Textbook: Physics 101', 'Comprehensive physics textbook', 'Books', 29.99, 2),
('Children Story Book', 'Illustrated story book for kids', 'Books', 7.99, 1);