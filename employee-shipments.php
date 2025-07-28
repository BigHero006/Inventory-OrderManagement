<?php
session_start();
require_once 'session_protection.php';
require_once 'classes/Employee.php';

requireRole('Employee');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];

$employee = new Employee();

// Get orders that can be shipped (processing status)
$shippableOrders = $employee->getAllOrders('processing');
$shippedOrders = $employee->getAllOrders('shipped');
$deliveredOrders = $employee->getAllOrders('delivered');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Management - Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="employee-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .shipment-management {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .back-btn:hover {
            background: #5a6268;
            color: white;
        }
        
        .shipment-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .tab-btn {
            background: #f8f9fa;
            color: #666;
            border: none;
            padding: 15px 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            background: #7b5cf6;
            color: white;
        }
        
        .tab-btn:hover:not(.active) {
            background: #e9ecef;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .shipment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #7b5cf6;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d1e7dd; color: #0a3622; }
        
        .action-btn {
            background: #7b5cf6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .action-btn:hover {
            background: #6b46c1;
        }
        
        .ship-btn {
            background: #28a745;
        }
        
        .ship-btn:hover {
            background: #218838;
        }
        
        .deliver-btn {
            background: #17a2b8;
        }
        
        .deliver-btn:hover {
            background: #138496;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .tracking-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body class="employee-dashboard">
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>Employee Panel</span>
            </div>
            <nav>
                <a href="employeedashboard.php">
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
                <a href="employee-shipments.php" class="active">
                    <i class="fas fa-truck"></i>
                    <span>Shipments</span>
                </a>
                <a href="employee-create-order.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Order</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header glass-card">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search shipments...">
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

            <!-- Existing Shipments Content -->
    <div class="content-card">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-truck"></i> Shipment Management</h1>
            </div>
            <a href="employeedashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="shipment-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($shippableOrders); ?></div>
                <div class="stat-label">Ready to Ship</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($shippedOrders); ?></div>
                <div class="stat-label">In Transit</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($deliveredOrders); ?></div>
                <div class="stat-label">Delivered</div>
            </div>
        </div>

        <div class="shipment-tabs">
            <button class="tab-btn active" onclick="showTab('ready-to-ship')">
                <i class="fas fa-box"></i> Ready to Ship
            </button>
            <button class="tab-btn" onclick="showTab('in-transit')">
                <i class="fas fa-truck"></i> In Transit
            </button>
            <button class="tab-btn" onclick="showTab('delivered')">
                <i class="fas fa-check-circle"></i> Delivered
            </button>
        </div>

        <!-- Ready to Ship Tab -->
        <div id="ready-to-ship" class="tab-content active">
            <?php if (empty($shippableOrders)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No orders ready to ship</h3>
                <p>All processing orders will appear here when ready for shipment.</p>
            </div>
            <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shippableOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-processing">Processing</span>
                        </td>
                        <td>
                            <button class="action-btn ship-btn" onclick="shipOrder(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-shipping-fast"></i> Ship Now
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- In Transit Tab -->
        <div id="in-transit" class="tab-content">
            <?php if (empty($shippedOrders)): ?>
            <div class="empty-state">
                <i class="fas fa-truck"></i>
                <h3>No orders in transit</h3>
                <p>Shipped orders will appear here.</p>
            </div>
            <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Shipped Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shippedOrders as $order): ?>
                    <tr>
                        <td>
                            #<?php echo $order['order_id']; ?>
                            <div class="tracking-info">
                                <i class="fas fa-map-marker-alt"></i> Tracking: TRK<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-shipped">Shipped</span>
                        </td>
                        <td>
                            <button class="action-btn deliver-btn" onclick="markDelivered(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-check"></i> Mark Delivered
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Delivered Tab -->
        <div id="delivered" class="tab-content">
            <?php if (empty($deliveredOrders)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>No delivered orders</h3>
                <p>Successfully delivered orders will appear here.</p>
            </div>
            <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Delivered Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveredOrders as $order): ?>
                    <tr>
                        <td>
                            #<?php echo $order['order_id']; ?>
                            <div class="tracking-info">
                                <i class="fas fa-check-circle"></i> Delivered successfully
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-delivered">Delivered</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Wastu Inventory</h4>
                <p>Efficient order and inventory management system designed for modern businesses.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="employeedashboard.php">Dashboard</a></li>
                    <li><a href="employee-orders.php">Orders</a></li>
                    <li><a href="employee-products.php">Products</a></li>
                    <li><a href="employee-shipments.php">Shipments</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Shipment Tracking</h4>
                <ul>
                    <li><a href="#process">Process Shipments</a></li>
                    <li><a href="#track">Track Packages</a></li>
                    <li><a href="#delivered">Delivered Orders</a></li>
                    <li><a href="#reports">Shipping Reports</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Connect</h4>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Wastu Inventory Management. All rights reserved.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#cookies">Cookie Policy</a>
            </div>
        </div>
    </footer>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab button
            event.target.classList.add('active');
        }
        
        // Ship order function
        function shipOrder(orderId) {
            if (confirm('Are you sure you want to ship this order?')) {
                updateOrderStatus(orderId, 'shipped', 'Order has been shipped successfully!');
            }
        }
        
        // Mark as delivered function
        function markDelivered(orderId) {
            if (confirm('Are you sure this order has been delivered?')) {
                updateOrderStatus(orderId, 'delivered', 'Order marked as delivered successfully!');
            }
        }
        
        // Update order status via AJAX
        function updateOrderStatus(orderId, status, successMessage) {
            fetch('api/employee_api.php?action=update_order_status', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: parseInt(orderId),
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(successMessage, 'success');
                    
                    // Reload page after 2 seconds to update the lists
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('Failed to update order status.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the order.', 'error');
            });
        }
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.cssText = `
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                max-width: 400px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                ${type === 'success' ? 
                    'background: #d1e7dd; color: #0a3622; border: 1px solid #a3cfbb;' : 
                    'background: #f8d7da; color: #721c24; border: 1px solid #f1aeb5;'
                }
            `;
            
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            
            document.body.appendChild(alertDiv);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Enhanced search functionality for the header search box
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    performGlobalSearch(query);
                }, 300);
            });
        }

        function performGlobalSearch(query) {
            // Search in shipment orders
            const orderRows = document.querySelectorAll('tr');
            let found = false;
            let matchingOrders = [];
            
            orderRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query.toLowerCase()) && row.cells.length > 1) {
                    const orderId = row.cells[0]?.textContent || '';
                    const customer = row.cells[1]?.textContent || '';
                    if (orderId && customer) {
                        matchingOrders.push({
                            id: orderId,
                            customer: customer
                        });
                        found = true;
                    }
                }
            });
            
            let html = '';
            if (found && matchingOrders.length > 0) {
                html += '<div class="search-category"><h4>Shipments</h4>';
                matchingOrders.slice(0, 5).forEach(order => {
                    html += `
                        <div class="search-item" onclick="highlightShipment('${order.id}')">
                            <div class="search-title">${order.id} - ${order.customer}</div>
                            <div class="search-meta">Shipment Order</div>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html = '<div class="search-empty">No shipments found</div>';
            }
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        function highlightShipment(orderId) {
            const rows = document.querySelectorAll('tr');
            rows.forEach(row => {
                if (row.cells[0] && row.cells[0].textContent === orderId) {
                    row.style.backgroundColor = '#fff3cd';
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 3000);
                }
            });
            searchResults.style.display = 'none';
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(event) {
            if (searchInput && searchResults && 
                !searchInput.contains(event.target) && 
                !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>
