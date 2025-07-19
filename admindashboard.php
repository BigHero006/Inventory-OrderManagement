<?php
session_start();
require_once 'session_protection.php';
require 'dbconnect.php';


requireRole('Admin');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];


try {
    
    $userCountStmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $userCountStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $adminCountStmt = $pdo->query("SELECT COUNT(*) as admin_users FROM users WHERE role = 'Admin'");
    $adminUsers = $adminCountStmt->fetch(PDO::FETCH_ASSOC)['admin_users'];

    $employeeCountStmt = $pdo->query("SELECT COUNT(*) as employee_users FROM users WHERE role = 'Employee'");
    $employeeUsers = $employeeCountStmt->fetch(PDO::FETCH_ASSOC)['employee_users'];

    
    $orderCountStmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $totalOrders = $orderCountStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    
    $productCountStmt = $pdo->query("SELECT COUNT(*) as total_products FROM inventory");
    $totalProducts = $productCountStmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    
    $supplierCountStmt = $pdo->query("SELECT COUNT(*) as total_suppliers FROM suppliers");
    $totalSuppliers = $supplierCountStmt->fetch(PDO::FETCH_ASSOC)['total_suppliers'];

    
    $pendingOrdersStmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'Pending'");
    $pendingOrdersStmt->execute();
    $pendingOrdersCount = $pendingOrdersStmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

    
    $recentOrdersStmt = $pdo->prepare("SELECT order_id, customer_name, order_date, status FROM orders ORDER BY order_date DESC LIMIT 5");
    $recentOrdersStmt->execute();
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $totalUsers = 0;
    $adminUsers = 0;
    $employeeUsers = 0;
    $totalOrders = 0;
    $totalProducts = 0;
    $totalSuppliers = 0;
    $pendingOrdersCount = 0;
    $recentOrders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - System Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-dashboard">
    <div class="dashboard">
        
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <span>Admin Panel</span>
            </div>
            <nav>
                <a href="#dashboard" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="#employees">
                    <i class="fas fa-users-cog"></i>
                    <span>Employee Management</span>
                </a>
                <a href="#orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order Management</span>
                </a>
                <a href="#products">
                    <i class="fas fa-box"></i>
                    <span>Product Management</span>
                </a>
                <a href="#inventory">
                    <i class="fas fa-warehouse"></i>
                    <span>Inventory Control</span>
                </a>
                <a href="#suppliers">
                    <i class="fas fa-truck"></i>
                    <span>Supplier Management</span>
                </a>
                <a href="#financial">
                    <i class="fas fa-credit-card"></i>
                    <span>Financial Reports</span>
                </a>
                <a href="#system">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
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
                    <input type="text" placeholder="Search users, orders, products...">
                </div>
                <div class="user-info">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                    <div class="year">Administrator</div>
                </div>
            </div>

            
            <div class="welcome-banner">
                <div class="text">
                    <div class="date"><?php echo date('F j, Y'); ?></div>
                    <h2>Welcome back to Wastu , <?php echo htmlspecialchars($firstName); ?>!</h2>
                    <p>Your complete solution to inventory and order management. Monitor and manage all operations and users.</p>
                </div>
                <div class="image">
                    <i class="fas fa-chart-line" style="font-size: 80px; opacity: 0.3;"></i>
                </div>
            </div>

            
            <div class="sections">
                <div class="finance-cards">
                    <div class="finance-card">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                        <small>System users</small>
                    </div>
                    <div class="finance-card">
                        <h3>Employees</h3>
                        <p style="color: #11998e;"><?php echo $employeeUsers; ?></p>
                        <small>Active employees</small>
                    </div>
                    <div class="finance-card">
                        <h3>Total Orders</h3>
                        <p><?php echo $totalOrders; ?></p>
                        <small>All orders</small>
                    </div>
                </div>
            </div>

            
            <div class="sections">
                <div class="finance-cards">
                    <div class="finance-card">
                        <h3>Products</h3>
                        <p><?php echo $totalProducts; ?></p>
                        <small>In inventory</small>
                    </div>
                    <div class="finance-card">
                        <h3>Suppliers</h3>
                        <p><?php echo $totalSuppliers; ?></p>
                        <small>Active suppliers</small>
                    </div>
                    <div class="finance-card">
                        <h3>Pending Orders</h3>
                        <p style="color: #ff6b6b;"><?php echo $pendingOrdersCount; ?></p>
                        <small>Need attention</small>
                    </div>
                </div>
            </div>

            
            <div class="daily-notice">
                <h3>Recent Orders Overview</h3>
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
                <h3>Administrative Actions</h3>
                <div class="course-card">
                    <div class="title">User Management</div>
                    <button onclick="showUserManagement()">Manage Users</button>
                </div>
                <div class="course-card">
                    <div class="title">Employee Oversight</div>
                    <button onclick="showEmployeeManagement()">Monitor Employees</button>
                </div>
                <div class="course-card">
                    <div class="title">System Reports</div>
                    <button onclick="showSystemReports()">View Reports</button>
                </div>
                <div class="course-card">
                    <div class="title">Financial Overview</div>
                    <button onclick="showFinancialReports()">Financial Data</button>
                </div>
                <div class="course-card">
                    <div class="title">System Settings</div>
                    <button onclick="showSystemSettings()">Configuration</button>
                </div>
                <div class="course-card">
                    <div class="title">Security & Audit</div>
                    <button onclick="showSecurityAudit()">Security Logs</button>
                </div>
            </div>
        </div>
    </div>

    <script>
       
        function showUserManagement() {
            alert('User Management interface would open here');
        }

        function showEmployeeManagement() {
            alert('Employee Management interface would open here');
        }

        function showSystemReports() {
            alert('System Reports interface would open here');
        }

        function showFinancialReports() {
            alert('Financial Reports interface would open here');
        }

        function showSystemSettings() {
            alert('System Settings interface would open here');
        }

        function showSecurityAudit() {
            alert('Security & Audit interface would open here');
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
            border-left: 4px solid #667eea;
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
        }
        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .action-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .action-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn:hover {
            background: #5a6fd8;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            float: right;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body class="admin-dashboard">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
            <p>Welcome back to Wastu , <?php echo htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?>!</p>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <?php
        
        try {
            
            $userCountStmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
            $totalUsers = $userCountStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

            
            $adminCountStmt = $pdo->query("SELECT COUNT(*) as admin_users FROM users WHERE role = 'Admin'");
            $adminUsers = $adminCountStmt->fetch(PDO::FETCH_ASSOC)['admin_users'];

            
            $employeeCountStmt = $pdo->query("SELECT COUNT(*) as employee_users FROM users WHERE role = 'Employee'");
            $employeeUsers = $employeeCountStmt->fetch(PDO::FETCH_ASSOC)['employee_users'];

            
            $orderCountStmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
            $totalOrders = $orderCountStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

            
            $productCountStmt = $pdo->query("SELECT COUNT(*) as total_products FROM inventory");
            $totalProducts = $productCountStmt->fetch(PDO::FETCH_ASSOC)['total_products'];

            
            $supplierCountStmt = $pdo->query("SELECT COUNT(*) as total_suppliers FROM suppliers");
            $totalSuppliers = $supplierCountStmt->fetch(PDO::FETCH_ASSOC)['total_suppliers'];

        } catch (Exception $e) {
            $totalUsers = 0;
            $adminUsers = 0;
            $employeeUsers = 0;
            $totalOrders = 0;
            $totalProducts = 0;
            $totalSuppliers = 0;
        }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $adminUsers; ?></div>
                <div>Admin Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $employeeUsers; ?></div>
                <div>Employee Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalOrders; ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalProducts; ?></div>
                <div>Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalSuppliers; ?></div>
                <div>Total Suppliers</div>
            </div>
        </div>

        <div class="admin-actions">
            <div class="action-card">
                <h3><i class="fas fa-users"></i> User Management</h3>
                <p>Manage admin and employee accounts, roles, and permissions</p>
                <a href="#" class="action-btn"><i class="fas fa-list"></i> View All Users</a>
                <a href="#" class="action-btn"><i class="fas fa-user-plus"></i> Add User</a>
                <a href="#" class="action-btn"><i class="fas fa-user-cog"></i> Manage Roles</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-users-cog"></i> Employee Management</h3>
                <p>Manage employee accounts and monitor their activities</p>
                <a href="#" class="action-btn"><i class="fas fa-users"></i> View Employees</a>
                <a href="#" class="action-btn"><i class="fas fa-chart-line"></i> Performance Reports</a>
                <a href="#" class="action-btn"><i class="fas fa-clock"></i> Activity Logs</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-boxes"></i> Inventory Management</h3>
                <p>Full control over products, stock levels, and suppliers</p>
                <a href="#" class="action-btn"><i class="fas fa-box"></i> View Inventory</a>
                <a href="#" class="action-btn"><i class="fas fa-plus"></i> Add Product</a>
                <a href="#" class="action-btn"><i class="fas fa-truck"></i> Manage Suppliers</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-shopping-cart"></i> Order Management</h3>
                <p>Complete order oversight and management capabilities</p>
                <a href="#" class="action-btn"><i class="fas fa-list-alt"></i> View All Orders</a>
                <a href="#" class="action-btn"><i class="fas fa-chart-line"></i> Sales Reports</a>
                <a href="#" class="action-btn"><i class="fas fa-shipping-fast"></i> Manage Shipments</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-credit-card"></i> Financial Management</h3>
                <p>Payment processing and financial reporting</p>
                <a href="#" class="action-btn"><i class="fas fa-money-bill"></i> View Payments</a>
                <a href="#" class="action-btn"><i class="fas fa-chart-pie"></i> Revenue Reports</a>
                <a href="#" class="action-btn"><i class="fas fa-file-invoice"></i> Financial Summary</a>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-cog"></i> System Administration</h3>
                <p>System settings, security, and maintenance</p>
                <a href="#" class="action-btn"><i class="fas fa-tools"></i> System Settings</a>
                <a href="#" class="action-btn"><i class="fas fa-file-alt"></i> Audit Logs</a>
                <a href="#" class="action-btn"><i class="fas fa-shield-alt"></i> Security Settings</a>
            </div>
        </div>
    </div>
</body>
</html>
