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
    
    public function getSupplierById($supplier_id) {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        return $stmt->fetch();
    }
    
    public function updateSupplier($supplier_id, $company_name, $contact_person, $email, $phone, $address) {
        $stmt = $this->db->getConnection()->prepare("UPDATE suppliers SET company_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE supplier_id = ?");
        return $stmt->execute([$company_name, $contact_person, $email, $phone, $address, $supplier_id]);
    }
    
    public function deleteSupplier($supplier_id) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Check if supplier has associated products
            $stmt = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE supplier_id = ?");
            $stmt->execute([$supplier_id]);
            $result = $stmt->fetch();
            
            if ($result['product_count'] > 0) {
                // Update products to remove supplier reference
                $stmt = $conn->prepare("UPDATE products SET supplier_id = NULL WHERE supplier_id = ?");
                $stmt->execute([$supplier_id]);
            }
            
            // Delete the supplier
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
            $stmt->execute([$supplier_id]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
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
    
    public function getEmployeeById($employee_id) {
        $stmt = $this->db->getConnection()->prepare("SELECT id, firstName, lastName, email, phone, address, role, created_at FROM users WHERE id = ? AND role = 'Employee'");
        $stmt->execute([$employee_id]);
        return $stmt->fetch();
    }
    
    public function updateEmployee($employee_id, $firstName, $lastName, $email, $phone, $address) {
        $stmt = $this->db->getConnection()->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, phone = ?, address = ? WHERE id = ? AND role = 'Employee'");
        return $stmt->execute([$firstName, $lastName, $email, $phone, $address, $employee_id]);
    }
    
    public function deleteEmployee($employee_id) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Check if employee has any associated orders or activities
            // For safety, we'll just mark them as inactive rather than deleting
            $stmt = $conn->prepare("UPDATE users SET role = 'Former Employee' WHERE id = ? AND role = 'Employee'");
            $stmt->execute([$employee_id]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    
    // Enhanced Order management methods
    public function getOrderById($order_id) {
        $query = "SELECT o.*, 
                         CONCAT(u.firstName, ' ', u.lastName) as customer_name,
                         u.email as customer_email
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.order_id = ?";
                  
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute([$order_id]);
        return $stmt->fetch();
    }
    
    public function updateOrderStatus($order_id, $status) {
        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $valid_statuses)) {
            throw new Exception('Invalid order status');
        }
        
        $stmt = $this->db->getConnection()->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        return $stmt->execute([$status, $order_id]);
    }
    
    public function cancelOrder($order_id) {
        return $this->updateOrderStatus($order_id, 'cancelled');
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
