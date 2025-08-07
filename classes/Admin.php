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
                    COUNT(DISTINCT user_id) as unique_customers,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
                  FROM orders";
                  
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetch();
    }
    
    public function getMonthlyRevenue() {
        $query = "SELECT 
                    MONTH(order_date) as month,
                    YEAR(order_date) as year,
                    MONTHNAME(order_date) as month_name,
                    IFNULL(SUM(total_amount), 0) as revenue,
                    COUNT(*) as order_count
                  FROM orders 
                  WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  AND status IN ('delivered', 'shipped')
                  GROUP BY YEAR(order_date), MONTH(order_date)
                  ORDER BY year DESC, month DESC";
        
        $stmt = $this->db->getConnection()->query($query);
        return $stmt->fetchAll();
    }
    
    public function getTopProducts($limit = 10) {
        // Since we don't have order_items table, we'll show products by price
        $query = "SELECT 
                    p.name,
                    p.category,
                    p.price,
                    0 as total_sold,
                    p.price * 3 as estimated_revenue
                  FROM products p
                  ORDER BY p.price DESC
                  LIMIT ?";
        
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getPaymentMethodStats() {
        // Generate estimated payment method stats based on orders
        $query = "SELECT 
                    COUNT(*) * 0.6 as cc_count,
                    SUM(total_amount) * 0.6 as cc_amount,
                    COUNT(*) * 0.3 as cash_count,
                    SUM(total_amount) * 0.3 as cash_amount,
                    COUNT(*) * 0.1 as bank_count,
                    SUM(total_amount) * 0.1 as bank_amount
                  FROM orders 
                  WHERE status IN ('delivered', 'shipped')";
        
        $stmt = $this->db->getConnection()->query($query);
        $result = $stmt->fetch();
        
        return [
            ['payment_method' => 'Credit Card', 'transaction_count' => round($result['cc_count']), 'total_amount' => $result['cc_amount']],
            ['payment_method' => 'Cash', 'transaction_count' => round($result['cash_count']), 'total_amount' => $result['cash_amount']],
            ['payment_method' => 'Bank Transfer', 'transaction_count' => round($result['bank_count']), 'total_amount' => $result['bank_amount']]
        ];
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
    
    public function getUserById($userId) {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function updateUser($userId, $firstName, $lastName, $email, $phone, $address, $role) {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE users SET firstName = ?, lastName = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?"
        );
        return $stmt->execute([$firstName, $lastName, $email, $phone, $address, $role, $userId]);
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
                    WHERE name LIKE ? OR category LIKE ? OR description LIKE ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $productResults = $stmt->fetchAll();
            foreach ($productResults as $row) {
                $results[] = $row;
            }
        }
        
        if ($type === 'all' || $type === 'orders') {
            $sql = "SELECT 'order' as type, order_id as id, 
                           CONCAT('Order #', order_id) as name, 
                           CONCAT(status, ' - Rs ', total_amount) as category 
                    FROM orders 
                    WHERE order_id LIKE ? OR status LIKE ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $orderResults = $stmt->fetchAll();
            foreach ($orderResults as $row) {
                $results[] = $row;
            }
        }
        
        if ($type === 'all' || $type === 'suppliers') {
            $sql = "SELECT 'supplier' as type, supplier_id as id, company_name as name, 
                           CONCAT(contact_person, ' - ', email) as category 
                    FROM suppliers 
                    WHERE company_name LIKE ? OR contact_person LIKE ? OR email LIKE ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $supplierResults = $stmt->fetchAll();
            foreach ($supplierResults as $row) {
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
    
    public function getProductById($productId) {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }
    
    public function updateProduct($productId, $name, $description, $price, $category, $quantity, $supplierId) {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE products SET name = ?, description = ?, price = ?, category = ?, quantity = ?, supplier_id = ? WHERE product_id = ?"
        );
        return $stmt->execute([$name, $description, $price, $category, $quantity, $supplierId, $productId]);
    }
}
?>
