<?php
session_start();
require_once 'session_protection.php';
require_once 'classes/Employee.php';

requireRole('Employee');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];

// Use Employee class for OOP implementation
$employee = new Employee();
$stats = $employee->getDashboardStats();
$recentOrders = $employee->getRecentOrders();

// Set secure cookie for user preferences
if (!isset($_COOKIE['employee_theme'])) {
    setcookie('employee_theme', 'default', time() + (86400 * 30), '/', '', false, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="employee-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="employee-dashboard">
    <div class="dashboard">
        
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>Employee Panel</span>
            </div>
            <nav>
                <a href="employeedashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="employee-orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Orders</span>
                </a>
                <a href="employee-products.php">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="employee-shipments.php">
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
            <!-- Header -->
            <div class="header glass-card">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search orders, products...">
                    <div id="searchResults" class="search-results"></div>
                </div>
                <div class="user-info">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                    <div class="year">Employee</div>
                </div>
            </div>

            
            <div class="welcome-banner glass-card fade-in">
                <div class="text">
                    <div class="date"><?php echo date('F j, Y'); ?></div>
                    <h2>Welcome back to Wastu, <?php echo htmlspecialchars($firstName); ?>!</h2>
                    <p>Manage orders and products efficiently. Here's your daily overview. Have a great day! 💜</p>
                </div>
                <div class="image">
                    <i class="fas fa-clipboard-list" style="font-size: 80px; opacity: 0.3;"></i>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="sections">
                <div class="finance-cards slide-up">
                    <div class="finance-card glass-card">
                        <h3>Total Orders</h3>
                        <p><?php echo $stats['total_orders']; ?></p>
                        <small>All time orders</small>
                    </div>
                    <div class="finance-card glass-card">
                        <h3>Total Products</h3>
                        <p><?php echo $stats['total_products']; ?></p>
                        <small>Distinct products</small>
                    </div>
                    <div class="finance-card glass-card">
                        <h3>Pending Orders</h3>
                        <p style="color: #ff6b6b;"><?php echo $stats['pending_orders']; ?></p>
                        <small>Need attention</small>
                    </div>
                </div>
            </div>

            
            <div class="daily-notice glass-card fade-in">
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

            <!-- Quick Actions -->
            <div class="enrolled-courses glass-card slide-up">
                <h3>Quick Actions</h3>
                <div class="course-card">
                    <div class="title">Add New Product</div>
                    <button onclick="showAddProductModal()">Add Product</button>
                </div>
                <div class="course-card">
                    <div class="title">Manage Suppliers</div>
                    <button onclick="showAddSupplierModal()">Manage Suppliers</button>
                </div>
                <div class="course-card">
                    <div class="title">Create New Order</div>
                    <button onclick="showAddOrderModal()">New Order</button>
                </div>
                <div class="course-card">
                    <div class="title">Process Shipment</div>
                    <button onclick="showShipmentModal()">Ship Order</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick Actions - Redirect to respective pages
        function showAddProductModal() {
            window.location.href = 'employee-products.php';
        }

        function showAddSupplierModal() {
            window.location.href = 'supplier-management.php';
        }

        function showAddOrderModal() {
            window.location.href = 'employee-create-order.php';
        }

        function showShipmentModal() {
            window.location.href = 'employee-shipments.php';
        }

        // AJAX Search functionality
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        function performSearch(query) {
            fetch(`api/employee_api.php?action=search&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        function displaySearchResults(data) {
            let html = '';
            
            // Display orders
            if (data.orders && data.orders.length > 0) {
                html += '<div class="search-category"><h4>Orders</h4>';
                data.orders.forEach(order => {
                    html += `
                        <div class="search-item" onclick="navigateToOrder(${order.order_id})">
                            <div class="search-title">Order #${order.order_id} - ${order.customer_name}</div>
                            <div class="search-meta">$${parseFloat(order.total_amount).toFixed(2)} • ${order.status}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }
            
            // Display products
            if (data.products && data.products.length > 0) {
                html += '<div class="search-category"><h4>Products</h4>';
                data.products.forEach(product => {
                    html += `
                        <div class="search-item" onclick="navigateToProduct(${product.product_id})">
                            <div class="search-title">${product.name}</div>
                            <div class="search-meta">$${parseFloat(product.price).toFixed(2)} • ${product.category}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }
            
            if (html === '') {
                html = '<div class="search-empty">No results found</div>';
            }
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        function navigateToOrder(orderId) {
            window.location.href = `employee-orders.php#order-${orderId}`;
        }

        function navigateToProduct(productId) {
            window.location.href = `employee-products.php#product-${productId}`;
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Sidebar navigation handling
        document.querySelectorAll('.sidebar nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Only prevent default for hash links (none in our updated nav)
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    document.querySelectorAll('.sidebar nav a').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Real-time dashboard updates using AJAX
        function updateDashboardStats() {
            fetch('api/employee_api.php?action=dashboard_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.total_orders !== undefined) {
                        document.querySelector('.finance-card:nth-child(1) p').textContent = data.total_orders;
                    }
                    if (data.total_products !== undefined) {
                        document.querySelector('.finance-card:nth-child(2) p').textContent = data.total_products;
                    }
                    if (data.pending_orders !== undefined) {
                        document.querySelector('.finance-card:nth-child(3) p').textContent = data.pending_orders;
                    }
                })
                .catch(error => console.error('Error updating stats:', error));
        }

        // Update stats every 30 seconds
        setInterval(updateDashboardStats, 30000);

        // Cookie management for user preferences
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;secure;samesite=strict`;
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Load user preferences
        document.addEventListener('DOMContentLoaded', function() {
            const theme = getCookie('employee_theme');
            if (theme) {
                document.body.classList.add(`theme-${theme}`);
            }
        });
    </script>

    <style>
        /* Enhanced search functionality styles */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 400px;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }
        
        .search-category {
            padding: 15px 0;
        }
        
        .search-category:not(:last-child) {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .search-category h4 {
            margin: 0 0 10px 15px;
            color: #0c4a6e;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .search-item {
            padding: 12px 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .search-item:hover {
            background-color: #f1f5f9;
            border-left-color: #0c4a6e;
        }
        
        .search-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .search-meta {
            font-size: 12px;
            color: #666;
        }
        
        .search-empty {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        
        /* Status colors */
        .status-pending { color: #ff9800; font-weight: bold; }
        .status-shipped { color: #2196f3; font-weight: bold; }
        .status-delivered { color: #4caf50; font-weight: bold; }
        .status-cancelled { color: #f44336; font-weight: bold; }
        .status-processing { color: #9c27b0; font-weight: bold; }
        
        /* Enhanced notice items */
        .notice-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #0c4a6e;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .notice-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .notice-item p {
            margin: 8px 0;
        }
        
        .notice-item small {
            color: #666;
            font-size: 12px;
        }
        
        /* Enhanced quick actions */
        .enrolled-courses {
            margin-top: 30px;
        }
        
        .course-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .course-card:hover {
            border-color: #0c4a6e;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(12, 74, 110, 0.15);
        }
        
        .course-card .title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .course-card button {
            background: #0c4a6e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 80%;
        }
        
        .course-card button:hover {
            background: #083344;
            transform: translateY(-2px);
        }
        
        /* Enhanced finance cards */
        .finance-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .finance-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .finance-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .finance-card p {
            font-size: 36px;
            font-weight: 700;
            color: #0c4a6e;
            margin: 0;
            line-height: 1;
        }
        
        .finance-card small {
            color: #888;
            font-size: 12px;
            margin-top: 8px;
            display: block;
        }
        
        /* Enhanced sidebar */
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            margin: 5px 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar nav a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background: white;
        }
        
        .search-box input:focus {
            border-color: #0c4a6e;
            box-shadow: 0 0 0 3px rgba(12, 74, 110, 0.1);
        }
        
        /* Enhanced welcome banner */
        .welcome-banner {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-banner .text h2 {
            color: #333;
            margin: 10px 0;
            font-size: 28px;
        }
        
        .welcome-banner .text p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        .welcome-banner .date {
            color: #0c4a6e;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .search-box {
                max-width: 250px;
            }
            
            .finance-cards {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
        }
        
        /* Animation for loading states */
        .loading {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Theme support */
        .theme-dark {
            --bg-primary: #1a1a2e;
            --bg-secondary: #16213e;
            --text-primary: #eee;
            --text-secondary: #aaa;
        }

        /* Dashboard Footer Styles */
        .dashboard-footer {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-top: 50px;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            z-index: 1;
        }
        
        .footer-content {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            padding: 50px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-section h4 {
            color: white;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
        }
        
        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 30px;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
        
        .footer-section p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin: 0;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 12px;
        }
        
        .footer-section ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .footer-section ul li a:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .social-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .footer-bottom {
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .footer-bottom p {
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            font-size: 14px;
        }
        
        .footer-links {
            display: flex;
            gap: 25px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        /* Footer responsive design */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 40px 20px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .footer-links {
                gap: 15px;
            }
        }
    </style>
</body>
</html>
