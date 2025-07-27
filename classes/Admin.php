<?php
require_once 'Database.php';

class Admin {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Product management methods
    public function getProducts() {
        $query = "SELECT p.*, i.quantity, i.min_stock_level, i.last_updated as stock_updated,
                         s.company_name as supplier_name
                  FROM products p
                  LEFT JOIN inventory i ON p.product_id = i.product_id
                  LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                  ORDER BY p.created_at DESC";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    public function addProduct($name, $description, $price, $category, $supplier_id, $quantity = 0, $min_stock = 10) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Insert product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, supplier_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $supplier_id]);
            
            $product_id = $conn->lastInsertId();
            
            // Insert into inventory
            $stmt = $conn->prepare("INSERT INTO inventory (product_id, quantity, min_stock_level) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $quantity, $min_stock]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    
    // Supplier management methods
    public function getSuppliers() {
        $query = "SELECT * FROM suppliers ORDER BY company_name";
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    public function addSupplier($company_name, $contact_person, $email, $phone, $address) {
        $stmt = $this->db->getConnection()->prepare("INSERT INTO suppliers (company_name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$company_name, $contact_person, $email, $phone, $address]);
    }
    
    // Order management methods
    public function getAllOrders() {
        $query = "SELECT o.*, 
                         CONCAT(u.firstName, ' ', u.lastName) as customer_name,
                         u.email as customer_email
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    // Employee management methods
    public function getEmployees() {
        $query = "SELECT id, firstName, lastName, email, phone, address, created_at 
                  FROM users 
                  WHERE role = 'Employee' 
                  ORDER BY created_at DESC";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    // User management methods
    public function getAllUsers() {
        $query = "SELECT id, firstName, lastName, email, phone, address, role, created_at 
                  FROM users 
                  ORDER BY created_at DESC";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    public function addUser($firstName, $lastName, $email, $phone, $address, $role, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->getConnection()->prepare("INSERT INTO users (firstName, lastName, email, phone, address, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$firstName, $lastName, $email, $phone, $address, $role, $hashedPassword]);
    }
    
    // Financial reporting methods
    public function getFinancialData() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    IFNULL(SUM(total_amount), 0) as total_revenue,
                    IFNULL(AVG(total_amount), 0) as avg_order_value,
                    COUNT(DISTINCT user_id) as unique_customers
                  FROM orders 
                  WHERE status = 'delivered'";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetch();
    }
    
    public function getRecentOrders($limit = 10) {
        $query = "SELECT o.*, 
                         CONCAT(u.firstName, ' ', u.lastName) as customer_name
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC
                  LIMIT ?";
                  
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Additional methods for API functionality
    public function deleteUser($userId) {
        $stmt = $this->db->getConnection()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function updateUserRole($userId, $role) {
        $stmt = $this->db->getConnection()->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }
    
    public function searchRecords($query, $type = 'all') {
        $results = [];
        
        if ($type === 'all' || $type === 'users') {
            $sql = "SELECT 'user' as type, id, CONCAT(firstName, ' ', lastName) as name, email 
                    FROM users 
                    WHERE firstName LIKE ? OR lastName LIKE ? OR email LIKE ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $userResults = $stmt->fetchAll();
            foreach ($userResults as $row) {
                $results[] = $row;
            }
        }
        
        if ($type === 'all' || $type === 'products') {
            $sql = "SELECT 'product' as type, product_id as id, name, category 
                    FROM products 
                    WHERE name LIKE ? OR category LIKE ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $productResults = $stmt->fetchAll();
            foreach ($productResults as $row) {
                $results[] = $row;
            }
        }
        
        return $results;
    }
    
    public function getUserStats() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'Customer' THEN 1 END) as total_customers,
                    COUNT(CASE WHEN role = 'Employee' THEN 1 END) as total_employees,
                    COUNT(CASE WHEN role = 'Admin' THEN 1 END) as total_admins
                  FROM users";
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetch();
    }
    
    public function getOrderStats() {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
                  FROM orders";
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetch();
    }
    
    public function getSupplierStats() {
        $query = "SELECT COUNT(*) as total_suppliers FROM suppliers";
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetch();
    }
    
    public function getRecentUsers($limit = 10) {
        $query = "SELECT id, firstName, lastName, email, role, created_at 
                  FROM users 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>
