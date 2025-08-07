<?php
require_once 'Database.php';

class Employee {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Get dashboard statistics
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Total orders
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total_orders FROM orders");
            $stmt->execute();
            $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
            
            // Total products
            $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT product_id) as total_products FROM order_items");
            $stmt->execute();
            $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
            
            // Pending orders
            $stmt = $this->conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
            $stmt->execute();
            $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
            return ['total_orders' => 0, 'total_products' => 0, 'pending_orders' => 0];
        }
    }
    
    // Get recent orders
    public function getRecentOrders($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT o.order_id, CONCAT(u.firstName, ' ', u.lastName) as customer_name, 
                       o.order_date, o.status, o.total_amount
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.order_date DESC 
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent orders: " . $e->getMessage());
            return [];
        }
    }
    
    // Search orders and products
    public function search($query, $type = 'all') {
        try {
            $results = [];
            $searchTerm = '%' . $query . '%';
            
            if ($type === 'all' || $type === 'orders') {
                $stmt = $this->conn->prepare("
                    SELECT o.order_id, CONCAT(u.firstName, ' ', u.lastName) as customer_name, 
                           o.order_date, o.status, o.total_amount, 'order' as result_type
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.order_id LIKE :search OR CONCAT(u.firstName, ' ', u.lastName) LIKE :search
                    ORDER BY o.order_date DESC
                    LIMIT 20
                ");
                $stmt->bindParam(':search', $searchTerm);
                $stmt->execute();
                $results['orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if ($type === 'all' || $type === 'products') {
                $stmt = $this->conn->prepare("
                    SELECT p.product_id, p.name, p.description, p.price, p.category, 'product' as result_type
                    FROM products p 
                    WHERE p.name LIKE :search OR p.description LIKE :search OR p.category LIKE :search
                    ORDER BY p.name
                    LIMIT 20
                ");
                $stmt->bindParam(':search', $searchTerm);
                $stmt->execute();
                $results['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error searching: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all orders for management
    public function getAllOrders($status = null, $limit = 50, $offset = 0) {
        try {
            $whereClause = $status ? "WHERE o.status = :status" : "";
            
            $stmt = $this->conn->prepare("
                SELECT o.order_id, CONCAT(u.firstName, ' ', u.lastName) as customer_name, 
                       o.order_date, o.status, o.total_amount, u.email as customer_email
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                $whereClause
                ORDER BY o.order_date DESC 
                LIMIT :limit OFFSET :offset
            ");
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting orders: " . $e->getMessage());
            return [];
        }
    }
    
    // Update order status
    public function updateOrderStatus($orderId, $status) {
        try {
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status");
            }
            
            $stmt = $this->conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
    
    // Get products for management
    public function getProducts($limit = 50, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("
                SELECT p.product_id, p.name, p.description, p.price, p.category, 
                       s.company_name as supplier_name, p.created_at
                FROM products p 
                LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                ORDER BY p.name
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting products: " . $e->getMessage());
            return [];
        }
    }
    
    // Add new product
    public function addProduct($name, $description, $price, $category, $supplierId = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO products (name, description, price, category, supplier_id, created_at) 
                VALUES (:name, :description, :price, :category, :supplier_id, NOW())
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':supplier_id', $supplierId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding product: " . $e->getMessage());
            return false;
        }
    }
    
    // Get suppliers
    public function getSuppliers() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM suppliers ORDER BY company_name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting suppliers: " . $e->getMessage());
            return [];
        }
    }
    
    // Create new order
    public function createOrder($userId, $items, $totalAmount) {
        try {
            $this->conn->beginTransaction();
            
            // Insert order
            $stmt = $this->conn->prepare("
                INSERT INTO orders (user_id, order_date, total_amount, status) 
                VALUES (:user_id, NOW(), :total_amount, 'pending')
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->execute();
            
            $orderId = $this->conn->lastInsertId();
            
            // Insert order items
            foreach ($items as $item) {
                $stmt = $this->conn->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (:order_id, :product_id, :quantity, :price)
                ");
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['unit_price']);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }
    
    // Get notifications for employee
    public function getNotifications($limit = 10, $unreadOnly = false) {
        try {
            $whereClause = $unreadOnly ? "WHERE is_read = 0" : "";
            $stmt = $this->conn->prepare("
                SELECT notification_id, title, message, type, is_read, created_at 
                FROM notifications 
                $whereClause
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format timestamps for display
            foreach ($notifications as &$notification) {
                $notification['time_ago'] = $this->timeAgo($notification['created_at']);
            }
            
            return $notifications;
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    // Mark notifications as read
    public function markNotificationsRead($notificationIds) {
        try {
            if (empty($notificationIds)) {
                // Mark all notifications as read
                $stmt = $this->conn->prepare("
                    UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE is_read = 0
                ");
                return $stmt->execute();
            } else {
                // Mark specific notifications as read
                $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';
                $stmt = $this->conn->prepare("
                    UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE notification_id IN ($placeholders)
                ");
                
                return $stmt->execute($notificationIds);
            }
        } catch (Exception $e) {
            error_log("Error marking notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    // Get unread notification count
    public function getUnreadNotificationCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting unread notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    // Helper function to format time ago
    private function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';
        
        return date('M j, Y', strtotime($datetime));
    }
}
?>
