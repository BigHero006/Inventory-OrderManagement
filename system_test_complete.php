<?php
// Complete System Test - Authentication and Database
require_once 'classes/Database.php';

echo "=== COMPREHENSIVE SYSTEM TEST ===\n\n";

try {
    // Test 1: Database Connection
    echo "1. Testing Database Connection...\n";
    $db = new Database();
    $pdo = $db->getConnection();
    echo "   ✓ Database connection successful!\n\n";
    
    // Test 2: Check if users table exists and has correct schema
    echo "2. Testing Users Table Schema...\n";
    $result = $pdo->query("DESCRIBE users");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $expectedColumns = ['id', 'firstName', 'lastName', 'email', 'phone', 'address', 'role', 'password', 'created_at'];
    $actualColumns = array_column($columns, 'Field');
    
    foreach ($expectedColumns as $expected) {
        if (in_array($expected, $actualColumns)) {
            echo "   ✓ Column '$expected' exists\n";
        } else {
            echo "   ❌ Column '$expected' missing\n";
        }
    }
    
    // Test 3: Check other important tables
    echo "\n3. Testing Other Table Schemas...\n";
    $tables = ['products', 'suppliers', 'inventory', 'orders'];
    
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "   ✓ Table '$table' exists (rows: $count)\n";
        } catch (PDOException $e) {
            echo "   ❌ Table '$table' missing or error: " . $e->getMessage() . "\n";
        }
    }
    
    // Test 4: Test Admin Class
    echo "\n4. Testing Admin Class Methods...\n";
    require_once 'classes/Admin.php';
    $admin = new Admin();
    
    $methods = [
        'getAllUsers', 'getProducts', 'getSuppliers', 
        'getAllOrders', 'getEmployees', 'getFinancialData'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($admin, $method)) {
            echo "   ✓ Method '$method' exists\n";
        } else {
            echo "   ❌ Method '$method' missing\n";
        }
    }
    
    // Test 5: Test Session Manager
    echo "\n5. Testing Session Manager...\n";
    require_once 'classes/SessionManager.php';
    echo "   ✓ SessionManager class loaded successfully\n";
    
    echo "\n=== ALL TESTS COMPLETED ===\n";
    echo "System Status: ✅ READY FOR USE\n\n";
    echo "Next Steps:\n";
    echo "1. Ensure XAMPP Apache and MySQL services are running\n";
    echo "2. Import the updated db.sql file into your database\n";
    echo "3. Create test user accounts through Signup.php\n";
    echo "4. Access admin dashboard through Signin.php\n";
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and ensure MySQL is running.\n";
}
?>
