-- Add notifications table for employee notification system
USE inventoryOrderManagement;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_is_read (is_read)
);

-- Insert some sample notifications for testing
INSERT INTO notifications (title, message, type, is_read) VALUES
('New Order Received', 'Order #1001 has been placed by John Doe', 'info', 0),
('Low Stock Alert', 'Smartphone X is running low on stock (5 units remaining)', 'warning', 0),
('Product Added', 'New product "Bluetooth Speaker" has been added to inventory', 'success', 1),
('System Update', 'Inventory management system will be updated tonight at 2 AM', 'info', 0),
('Payment Processed', 'Payment for Order #1002 has been successfully processed', 'success', 1);
