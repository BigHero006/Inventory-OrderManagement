<?php
// Test script to verify order status updates
session_start();
require_once 'classes/Employee.php';

// Temporarily set session for testing
$_SESSION['role'] = 'Employee';

try {
    $employee = new Employee();
    
    echo "<h2>Testing Order Status Updates</h2>";
    
    // Get all orders first
    echo "<h3>Current Orders:</h3>";
    $orders = $employee->getAllOrders();
    
    if (empty($orders)) {
        echo "No orders found in database.<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Date</th></tr>";
        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td>" . $order['order_id'] . "</td>";
            echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
            echo "<td>" . $order['status'] . "</td>";
            echo "<td>" . $order['order_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test updating the first order's status
        if (count($orders) > 0) {
            $testOrderId = $orders[0]['order_id'];
            $currentStatus = $orders[0]['status'];
            
            // Choose a different status for testing
            $newStatus = ($currentStatus === 'pending') ? 'processing' : 'pending';
            
            echo "<h3>Testing Status Update:</h3>";
            echo "Updating Order #$testOrderId from '$currentStatus' to '$newStatus'<br>";
            
            $result = $employee->updateOrderStatus($testOrderId, $newStatus);
            
            if ($result) {
                echo "<span style='color: green;'>✓ Status update successful!</span><br>";
                
                // Verify the update
                $updatedOrders = $employee->getAllOrders();
                $updatedOrder = array_filter($updatedOrders, function($o) use ($testOrderId) {
                    return $o['order_id'] == $testOrderId;
                });
                
                if (!empty($updatedOrder)) {
                    $updatedOrder = array_values($updatedOrder)[0];
                    echo "New status in database: " . $updatedOrder['status'] . "<br>";
                    
                    if ($updatedOrder['status'] === $newStatus) {
                        echo "<span style='color: green;'>✓ Database update verified!</span><br>";
                    } else {
                        echo "<span style='color: red;'>✗ Database update failed - status mismatch</span><br>";
                    }
                }
            } else {
                echo "<span style='color: red;'>✗ Status update failed!</span><br>";
            }
        }
    }
    
    // Test database connection
    echo "<h3>Database Connection Test:</h3>";
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<span style='color: green;'>✓ Database connection successful!</span><br>";
        
        // Check if orders table exists and has the right structure
        $stmt = $conn->prepare("DESCRIBE orders");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Orders table structure:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<span style='color: red;'>✗ Database connection failed!</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
}

echo "<br><a href='employee-orders.php'>← Back to Order Management</a>";
?>
