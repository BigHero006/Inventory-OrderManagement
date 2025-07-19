<?php
session_start();
require_once 'session_protection.php';
require 'dbconnect.php';


requireRole('Employee');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];


$ordersStmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders");
$ordersStmt->execute();
$ordersCount = $ordersStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];


$productsStmt = $pdo->prepare("SELECT COUNT(*) as total_products FROM inventory");
$productsStmt->execute();
$productsCount = $productsStmt->fetch(PDO::FETCH_ASSOC)['total_products'];


$pendingOrdersStmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'Pending'");
$pendingOrdersStmt->execute();
$pendingOrdersCount = $pendingOrdersStmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

$recentOrdersStmt = $pdo->prepare("SELECT order_id, customer_name, order_date, status FROM orders ORDER BY order_date DESC LIMIT 5");
$recentOrdersStmt->execute();
$recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Inventory & Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="employee-dashboard">
    <div class="dashboard">
        
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>Employee Panel</span>
            </div>
            <nav>
                <a href="#dashboard" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Orders</span>
                </a>
                <a href="#products">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="#inventory">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory</span>
                </a>
                <a href="#shipments">
                    <i class="fas fa-truck"></i>
                    <span>Shipments</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        
        <div class="main-content">
            
            <div class="header">
                <div class="search-box">
                    <input type="text" placeholder="Search orders, products...">
                </div>
                <div class="user-info">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                    <div class="year">Employee</div>
                </div>
            </div>

            
            <div class="welcome-banner">
                <div class="text">
                    <div class="date"><?php echo date('F j, Y'); ?></div>
                    <h2>Welcome back to Wastu , <?php echo htmlspecialchars($firstName); ?>!</h2>
                    <p>Manage orders and inventory efficiently. Here's your daily overview.Have a great day <3 .</p>
                </div>
                <div class="image">
                    <i class="fas fa-clipboard-list" style="font-size: 80px; opacity: 0.3;"></i>
                </div>
            </div>

            
            <div class="sections">
                <div class="finance-cards">
                    <div class="finance-card">
                        <h3>Total Orders</h3>
                        <p><?php echo $ordersCount; ?></p>
                        <small>All time orders</small>
                    </div>
                    <div class="finance-card">
                        <h3>Total Products</h3>
                        <p><?php echo $productsCount; ?></p>
                        <small>In inventory</small>
                    </div>
                    <div class="finance-card">
                        <h3>Pending Orders</h3>
                        <p style="color: #ff6b6b;"><?php echo $pendingOrdersCount; ?></p>
                        <small>Need attention</small>
                    </div>
                </div>
            </div>

            
            <div class="daily-notice">
                <h3>Recent Orders</h3>
                <?php if (empty($recentOrders)): ?>
                    <p>No orders found.</p>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="notice-item">
                            <strong>Order #<?php echo $order['order_id']; ?> - <?php echo htmlspecialchars($order['customer_name']); ?></strong>
                            <p>Status: <span class="status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></p>
                            <small>Date: <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            
            <div class="enrolled-courses">
                <h3>Quick Actions</h3>
                <div class="course-card">
                    <div class="title">Add New Product</div>
                    <button onclick="showAddProductModal()">Add Product</button>
                </div>
                <div class="course-card">
                    <div class="title">Create New Order</div>
                    <button onclick="showAddOrderModal()">New Order</button>
                </div>
                <div class="course-card">
                    <div class="title">Update Inventory</div>
                    <button onclick="showInventoryModal()">Update Stock</button>
                </div>
                <div class="course-card">
                    <div class="title">Process Shipment</div>
                    <button onclick="showShipmentModal()">Ship Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        function showAddProductModal() {
            alert('Add Product functionality would open here');
        }

        function showAddOrderModal() {
            alert('Add Order functionality would open here');
        }

        function showInventoryModal() {
            alert('Inventory update functionality would open here');
        }

        function showShipmentModal() {
            alert('Shipment processing functionality would open here');
        }

        
        document.querySelectorAll('.sidebar nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    document.querySelectorAll('.sidebar nav a').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    </script>

    <style>
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-shipped { color: #2196f3; font-weight: bold; }
        .status-delivered { color: #4caf50; font-weight: bold; }
        .status-cancelled { color: #f44336; font-weight: bold; }
        
        .notice-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #7b5cf6;
        }
        
        .notice-item p {
            margin: 5px 0;
        }
        
        .notice-item small {
            color: #666;
        }
    </style>
</body>
</html>
