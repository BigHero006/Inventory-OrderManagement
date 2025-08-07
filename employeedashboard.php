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
    <link rel="stylesheet" href="employee-dashboard.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="employee-dashboard">
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>
    
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
                <a href="employee-create-order.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Order</span>
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
                        <p style="color: #9ca3af;"><?php echo $stats['pending_orders']; ?></p>
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
                    <div class="title">Manage Orders</div>
                    <button onclick="showManageOrdersModal()">Manage Order</button>
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

        function showManageOrdersModal() {
            window.location.href = 'employee-orders.php';
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

        // Mobile menu toggle functionality
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.mobile-menu-toggle');
            
            if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Deep Sea Particle Effect
        function createParticles() {
            const particleCount = 15;
            const container = document.body;

            for (let i = 0; i < particleCount; i++) {
                setTimeout(() => {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    
                    // Random starting position
                    particle.style.left = Math.random() * window.innerWidth + 'px';
                    particle.style.animationDelay = Math.random() * 15 + 's';
                    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                    
                    container.appendChild(particle);
                    
                    // Remove particle after animation
                    setTimeout(() => {
                        if (particle.parentNode) {
                            particle.parentNode.removeChild(particle);
                        }
                    }, 20000);
                }, i * 1000);
            }
        }

        // Start particle effect after page load
        setTimeout(createParticles, 2000);
        
        // Repeat particle effect every 30 seconds
        setInterval(createParticles, 30000);
    </script>
</body>
</html>
