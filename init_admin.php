<?php
/*
 * Admin User Initialization Script
 * Run this script once to create the default admin user in the database
 */

require_once 'dbconnect.php';

try {
    // Check if admin user already exists
    $checkAdmin = "SELECT * FROM users WHERE email = 'admin@gmail.com'";
    $stmt = $pdo->prepare($checkAdmin);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Admin user already exists!<br>";
    } else {
        // Create admin user
        $insertAdmin = "INSERT INTO users (firstName, lastName, email, phone, address, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $adminStmt = $pdo->prepare($insertAdmin);
        $success = $adminStmt->execute([
            'Admin',
            'User', 
            'admin@gmail.com',
            '1234567890',
            'Admin Address',
            'Admin',
            md5('Admin123')
        ]);
        
        if ($success) {
            echo "Admin user created successfully!<br>";
            echo "Email: admin@gmail.com<br>";
            echo "Password: Admin123<br>";
        } else {
            echo "Failed to create admin user!<br>";
        }
    }
    
    echo "<br><a href='Signin.php'>Go to Login</a>";
    
} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
}
?>
