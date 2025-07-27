<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

// Initialize Admin class and fetch statistics
try {
    $admin = new Admin();
    $userStats = $admin->getUserStats();
    $orderStats = $admin->getOrderStats();
    $supplierStats = $admin->getSupplierStats();
    
    // Ensure all stats have default values if null
    $userStats = $userStats ?: ['total_users' => 0, 'admin_users' => 0, 'employee_users' => 0];
    $orderStats = $orderStats ?: ['total_orders' => 0, 'pending_orders' => 0, 'shipped_orders' => 0, 'delivered_orders' => 0];
    $supplierStats = $supplierStats ?: ['total_suppliers' => 0];
    
} catch (Exception $e) {
    // Set default values if database connection fails
    $userStats = ['total_users' => 0, 'admin_users' => 0, 'employee_users' => 0];
    $orderStats = ['total_orders' => 0, 'pending_orders' => 0, 'shipped_orders' => 0, 'delivered_orders' => 0];
    $supplierStats = ['total_suppliers' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - System Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="admin-dashboard.css">
</head>
<body class="admin-dashboard">
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <span>Admin Panel</span>
            </div>
            <nav>
                <a href="admindashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="user-management.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="employee-management.php" class="nav-link">
                    <i class="fas fa-users-cog"></i>
                    <span>Employee Management</span>
                </a>
                <a href="order-management.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order Management</span>
                </a>
                <a href="product-management.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Product Management</span>
                </a>
                <a href="supplier-management.php" class="nav-link">
                    <i class="fas fa-truck"></i>
                    <span>Supplier Management</span>
                </a>
                <a href="financial-reports.php" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    <span>Financial Reports</span>
                </a>
                <a href="logout.php" class="nav-link logout-nav">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search users, orders, products...">
                        <button id="searchBtn"><i class="fas fa-search"></i></button>
                    </div>
                    <div id="searchResults" class="search-results"></div>
                </div>
                <div class="user-info">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                    <div class="year">Administrator</div>
                </div>
            </div>

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="text">
                    <div class="date"><?php echo date('F j, Y'); ?></div>
                    <h2>Welcome back to Wastu, <?php echo htmlspecialchars($firstName); ?>!</h2>
                    <p>Your complete solution to inventory and order management. Monitor and manage all operations and users.</p>
                </div>
                <div class="image">
                    <i class="fas fa-chart-line" style="font-size: 80px; opacity: 0.3;"></i>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="stats-section">
                <div class="finance-cards">
                    <div class="finance-card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $userStats['total_users']; ?></p>
                            <small>System users</small>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="card-icon employee">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-content">
                            <h3>Employees</h3>
                            <p class="stat-number employee-color"><?php echo $userStats['employee_users']; ?></p>
                            <small>Active employees</small>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="card-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-content">
                            <h3>Total Orders</h3>
                            <p class="stat-number"><?php echo $orderStats['total_orders']; ?></p>
                            <small>All orders</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="stats-section">
                <div class="finance-cards">
                    <div class="finance-card">
                        <div class="card-icon suppliers">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="card-content">
                            <h3>Suppliers</h3>
                            <p class="stat-number"><?php echo $supplierStats['total_suppliers']; ?></p>
                            <small>Active suppliers</small>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="card-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h3>Pending Orders</h3>
                            <p class="stat-number pending-color"><?php echo $orderStats['pending_orders']; ?></p>
                            <small>Need attention</small>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="card-icon delivered">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-content">
                            <h3>Delivered Orders</h3>
                            <p class="stat-number"><?php echo $orderStats['delivered_orders']; ?></p>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="daily-notice">
                <h3>Recent Orders Overview</h3>
                <div id="recentOrdersContainer">
                    <!-- Recent orders will be loaded via AJAX -->
                </div>
            </div>

            <!-- Administrative Actions -->
            <div class="enrolled-courses">
                <h3>Administrative Actions</h3>
                <div class="actions-grid">
                    <div class="course-card" onclick="navigateToPage('user-management.php')">
                        <div class="action-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="title">User Management</div>
                        <button>Manage Users</button>
                    </div>
                    <div class="course-card" onclick="navigateToPage('employee-management.php')">
                        <div class="action-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="title">Employee Oversight</div>
                        <button>Monitor Employees</button>
                    </div>
                    <div class="course-card" onclick="navigateToPage('system-reports.php')">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="title">System Reports</div>
                        <button>View Reports</button>
                    </div>
                    <div class="course-card" onclick="navigateToPage('financial-reports.php')">
                        <div class="action-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="title">Financial Overview</div>
                        <button>Financial Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let searchTimeout;
        
        // Navigation function
        function navigateToPage(page) {
            window.location.href = page;
        }
        
        // Search functionality with AJAX
        function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            const resultsContainer = document.getElementById('searchResults');
            
            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';
                return;
            }
            
            // Show loading
            resultsContainer.innerHTML = '<div class="search-loading">Searching...</div>';
            resultsContainer.style.display = 'block';
            
            // AJAX request
            fetch(`api/admin_api.php?action=search&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.data);
                    } else {
                        resultsContainer.innerHTML = '<div class="search-error">Search failed</div>';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = '<div class="search-error">Search error occurred</div>';
                });
        }
        
        // Display search results
        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('searchResults');
            let html = '';
            
            if (results.users && results.users.length > 0) {
                html += '<div class="search-category"><h4>Users</h4>';
                results.users.forEach(user => {
                    html += `<div class="search-item" onclick="navigateToPage('user-management.php?user=${user.id}')">
                        <i class="fas fa-user"></i>
                        <span>${user.name}</span>
                        <small>${user.email}</small>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (results.products && results.products.length > 0) {
                html += '<div class="search-category"><h4>Products</h4>';
                results.products.forEach(product => {
                    html += `<div class="search-item" onclick="navigateToPage('product-management.php?product=${product.id}')">
                        <i class="fas fa-box"></i>
                        <span>${product.name}</span>
                        <small>${product.category}</small>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (results.orders && results.orders.length > 0) {
                html += '<div class="search-category"><h4>Orders</h4>';
                results.orders.forEach(order => {
                    html += `<div class="search-item" onclick="navigateToPage('order-management.php?order=${order.id}')">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Order #${order.id}</span>
                        <small>${order.name}</small>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (html === '') {
                html = '<div class="search-no-results">No results found</div>';
            }
            
            resultsContainer.innerHTML = html;
        }
        
        // Load recent orders via AJAX
        function loadRecentOrders() {
            fetch('api/admin_api.php?action=recent_orders')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayRecentOrders(data.data);
                    }
                })
                .catch(error => console.error('Error loading recent orders:', error));
        }
        
        // Display recent orders
        function displayRecentOrders(orders) {
            const container = document.getElementById('recentOrdersContainer');
            let html = '';
            
            if (orders.length === 0) {
                html = '<p>No recent orders found.</p>';
            } else {
                orders.forEach(order => {
                    html += `
                        <div class="notice-item">
                            <strong>Order #${order.order_id} - ${order.customer_name}</strong>
                            <p>Status: <span class="status-${order.status.toLowerCase()}">${order.status}</span></p>
                            <small>Date: ${new Date(order.order_date).toLocaleDateString()}</small>
                        </div>
                    `;
                });
            }
            
            container.innerHTML = html;
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const searchResults = document.getElementById('searchResults');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(performSearch, 300);
                });
            }
            
            if (searchBtn) {
                searchBtn.addEventListener('click', performSearch);
            }
            
            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container') && searchResults) {
                    searchResults.style.display = 'none';
                }
            });
            
            // Navigation links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                    }
                });
            });
            
            // Load initial data
            loadRecentOrders();
        });
    </script>
</body>
</html>
