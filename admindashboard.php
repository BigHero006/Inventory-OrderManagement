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
    <link rel="stylesheet" href="admin-dashboard.css?v=<?php echo time(); ?>"
        .footer-content {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 60px !important;
            padding: 80px 50px !important;
            max-width: 1500px !important;
            margin: 0 auto !important;
        }
        .footer-section {
            background: rgba(255, 255, 255, 0.05) !important;
            padding: 30px !important;
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .footer-section h4 {
            color: white !important;
            font-size: 22px !important;
            margin-bottom: 30px !important;
        }
        .footer-section p {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .footer-section ul li a {
            color: rgba(255, 255, 255, 0.8) !important;
            text-decoration: none !important;
        }
        .footer-section ul li a:hover {
            color: white !important;
        }
        .footer-bottom {
            background: rgba(30, 27, 75, 0.8) !important;
            padding: 40px 50px !important;
            border-top: 2px solid rgba(59, 130, 246, 0.3) !important;
        }
        .footer-bottom p {
            color: white !important;
        }
        .footer-links a {
            color: rgba(255, 255, 255, 0.8) !important;
            text-decoration: none !important;
        }
        .social-links a {
            background: rgba(59, 130, 246, 0.1) !important;
            color: white !important;
            width: 55px !important;
            height: 55px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 18px !important;
            margin-right: 15px !important;
        }
    </style>
    <script src="js/table-enhancer.js"></script>
</head>
<body class="admin-dashboard">
    <div class="dashboard gradient-mesh custom-scrollbar">
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
                <div class="header-actions">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="header-user">
                        <img src="https://via.placeholder.com/40" alt="User" class="user-photo">
                        <div class="user-info">
                            <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                            <div class="role">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <div class="date"><?php echo date('l, F j, Y'); ?></div>
                        <h1>Welcome back, <?php echo htmlspecialchars($firstName); ?>!</h1>
                        <p>Your complete solution to order management. Monitor and manage all operations and users efficiently.</p>
                    </div>
                    <div class="welcome-visual">
                        <div class="stats-preview">
                            <div class="mini-stat">
                                <i class="fas fa-users"></i>
                                <span><?php echo $userStats['total_users']; ?> Users</span>
                            </div>
                            <div class="mini-stat">
                                <i class="fas fa-shopping-cart"></i>
                                <span><?php echo $orderStats['total_orders']; ?> Orders</span>
                            </div>
                            <div class="mini-stat">
                                <i class="fas fa-truck"></i>
                                <span><?php echo $supplierStats['total_suppliers']; ?> Suppliers</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="stats-section">
                <h2 class="section-title">System Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Total Users</h3>
                            <div class="stat-number"><?php echo $userStats['total_users']; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-user-shield"></i>
                                    <?php echo $userStats['total_admins'] ?? 0; ?> Admins
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-user-tie"></i>
                                    <?php echo $userStats['total_employees'] ?? 0; ?> Employees
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Order Management</h3>
                            <div class="stat-number"><?php echo $orderStats['total_orders']; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <?php echo $orderStats['pending_orders']; ?> Pending
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-check"></i>
                                    <?php echo $orderStats['delivered_orders']; ?> Delivered
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Suppliers</h3>
                            <div class="stat-number"><?php echo $supplierStats['total_suppliers']; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-handshake"></i>
                                    Active Partners
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h2 class="section-title text-gradient">Quick Actions</h2>
                <div class="actions-grid">
                    <div class="action-card" onclick="navigateToPage('user-management.php')">
                        <div class="action-icon users">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="action-content">
                            <h3>Manage Users</h3>
                            <p>Add, edit, or remove system users and manage permissions</p>
                            <button class="action-btn">Open Panel</button>
                        </div>
                    </div>

                    <div class="action-card" onclick="navigateToPage('order-management.php')">
                        <div class="action-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="action-content">
                            <h3>Process Orders</h3>
                            <p>View, track, and manage customer orders and deliveries</p>
                            <button class="action-btn">View Orders</button>
                        </div>
                    </div>

                    <div class="action-card" onclick="navigateToPage('product-management.php')">
                        <div class="action-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="action-content">
                            <h3>Product Catalog</h3>
                            <p>Manage product catalog, pricing, and availability</p>
                            <button class="action-btn">Manage Products</button>
                        </div>
                    </div>

                    <div class="action-card" onclick="navigateToPage('supplier-management.php')">
                        <div class="action-icon suppliers">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="action-content">
                            <h3>Supplier Network</h3>
                            <p>Manage supplier relationships and procurement</p>
                            <button class="action-btn">View Suppliers</button>
                        </div>
                    </div>

                    <div class="action-card" onclick="navigateToPage('system-reports.php')">
                        <div class="action-icon reports">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-content">
                            <h3>Analytics & Reports</h3>
                            <p>Generate detailed reports and analyze system performance</p>
                            <button class="action-btn">View Reports</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2 class="section-title">Recent Orders</h2>
                <div class="activity-container">
                    <div id="recentOrdersContainer" class="orders-list">
                        <!-- Recent orders will be loaded via AJAX -->
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
                    if (data.success && data.data.length > 0) {
                        displayRecentOrders(data.data);
                    } else {
                        document.getElementById('recentOrdersContainer').innerHTML = 
                            '<div class="empty-state"><i class="fas fa-shopping-cart"></i><p>No recent orders found</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading recent orders:', error);
                    document.getElementById('recentOrdersContainer').innerHTML = 
                        '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load orders</p></div>';
                });
        }
        
        // Display recent orders
        function displayRecentOrders(orders) {
            const container = document.getElementById('recentOrdersContainer');
            let html = '';
            
            orders.forEach(order => {
                const statusClass = order.status.toLowerCase().replace(' ', '-');
                html += `
                    <div class="order-item" onclick="navigateToPage('order-management.php?order=${order.order_id}')">
                        <div class="order-info">
                            <div class="order-header">
                                <span class="order-id">#${order.order_id}</span>
                                <span class="order-status status-${statusClass}">${order.status}</span>
                            </div>
                            <div class="order-customer">${order.customer_name}</div>
                            <div class="order-date">${new Date(order.order_date).toLocaleDateString()}</div>
                        </div>
                        <div class="order-amount">$${parseFloat(order.total_amount || 0).toFixed(2)}</div>
                    </div>
                `;
            });
            
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

    <!-- Particle Background -->
    <div class="particles-bg" id="particles-bg"></div>
    
    <script>
        // Create particle background
        function createParticles() {
            const particlesContainer = document.getElementById('particles-bg');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>
