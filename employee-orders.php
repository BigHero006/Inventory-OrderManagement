<?php
session_start();
require_once 'session_protection.php';
require_once 'classes/Employee.php';

requireRole('Employee');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];

$employee = new Employee();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            if ($orderId && $status) {
                $result = $employee->updateOrderStatus($orderId, $status);
                if ($result) {
                    $message = "Order status updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update order status.";
                    $messageType = "error";
                }
            }
            break;
    }
}

// Get initial orders
$orders = $employee->getAllOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="employee-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .order-management {
            padding: 20px;
            background: #ffffff;
            min-height: 100vh;
        }
        
        .main-content {
            margin-left: 280px;
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
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #555;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
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
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d1e7dd; color: #0a3622; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
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
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d1e7dd;
            color: #0a3622;
            border: 1px solid #a3cfbb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            position: relative;
            background: white;
            margin: 10% auto;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #7b5cf6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #6b46c1;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
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
                <a href="employee-orders.php" class="active">
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
                    <input type="text" id="searchInput" placeholder="Search orders, customers...">
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

            <!-- Existing Orders Content -->
    <div class="content-card">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="refreshOrdersTable()" class="action-btn" style="padding: 10px 15px;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <a href="employeedashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="filters">
            <div class="filter-group">
                <label>Status Filter:</label>
                <select id="statusFilter">
                    <option value="">All Orders</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Search:</label>
                <input type="text" id="orderSearch" placeholder="Search by customer name or order ID...">
            </div>
        </div>

        <table class="orders-table" id="ordersTable">
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
            <tbody id="ordersTableBody">
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="action-btn" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>)">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modalOrderId">
                
                <div class="form-group">
                    <label for="modalStatus">New Status:</label>
                    <select name="status" id="modalStatus" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div style="text-align: right;">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // AJAX functionality for real-time updates
        let orders = <?php echo json_encode($orders); ?>;
        
        // Filter orders by status
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value;
            filterOrders();
        });
        
        // Search orders
        document.getElementById('orderSearch').addEventListener('input', function() {
            filterOrders();
        });
        
        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
            const tbody = document.getElementById('ordersTableBody');
            const rows = tbody.getElementsByTagName('tr');
            
            for (let row of rows) {
                const status = row.cells[4].textContent.toLowerCase().trim();
                const customerName = row.cells[1].textContent.toLowerCase();
                const orderId = row.cells[0].textContent.toLowerCase();
                
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesSearch = !searchTerm || 
                    customerName.includes(searchTerm) || 
                    orderId.includes(searchTerm);
                
                row.style.display = matchesStatus && matchesSearch ? '' : 'none';
            }
        }
        
        // Update order status modal
        function updateOrderStatus(orderId) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // AJAX form submission
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const orderId = formData.get('order_id');
            const status = formData.get('status');
            
            // Show loading state
            const submitBtn = this.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;
            
            // Update via AJAX
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data); // Debug log
                
                if (data.success) {
                    // Update the table row
                    const rows = document.getElementById('ordersTableBody').getElementsByTagName('tr');
                    for (let row of rows) {
                        const orderIdCell = row.cells[0].textContent;
                        if (orderIdCell === '#' + orderId) {
                            const statusCell = row.cells[4];
                            statusCell.innerHTML = `<span class="status-badge status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                            
                            // Update the orders array for filtering
                            const orderIndex = orders.findIndex(order => order.order_id == orderId);
                            if (orderIndex !== -1) {
                                orders[orderIndex].status = status;
                            }
                            break;
                        }
                    }
                    
                    closeModal();
                    showAlert('Order status updated successfully!', 'success');
                    
                    // Refresh orders from database to ensure data consistency
                    refreshOrdersTable();
                } else {
                    showAlert(data.error || 'Failed to update order status.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the order: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            
            const contentCard = document.querySelector('.content-card');
            const pageHeader = document.querySelector('.page-header');
            contentCard.insertBefore(alertDiv, pageHeader.nextSibling);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Refresh orders table from database
        function refreshOrdersTable() {
            fetch('api/employee_api.php?action=orders')
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        orders = data; // Update the global orders array
                        updateOrdersTableHTML(data);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing orders:', error);
                });
        }
        
        // Update the orders table HTML
        function updateOrdersTableHTML(ordersData) {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';
            
            ordersData.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>#${order.order_id}</td>
                    <td>${escapeHtml(order.customer_name)}</td>
                    <td>${formatDate(order.order_date)}</td>
                    <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
                    <td>
                        <span class="status-badge status-${order.status}">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <button class="action-btn" onclick="updateOrderStatus(${order.order_id})">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // Reapply current filters
            filterOrders();
        }
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Helper function to format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        
        // Enhanced search functionality for the header search box
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
                performGlobalSearch(query);
            }, 300);
        });

        function performGlobalSearch(query) {
            // Search in current orders first
            const currentOrders = orders.filter(order => 
                order.customer_name.toLowerCase().includes(query.toLowerCase()) ||
                order.order_id.toString().includes(query) ||
                order.status.toLowerCase().includes(query.toLowerCase())
            );
            
            let html = '';
            if (currentOrders.length > 0) {
                html += '<div class="search-category"><h4>Orders</h4>';
                currentOrders.slice(0, 5).forEach(order => {
                    html += `
                        <div class="search-item" onclick="highlightOrder(${order.order_id})">
                            <div class="search-title">Order #${order.order_id} - ${order.customer_name}</div>
                            <div class="search-meta">$${parseFloat(order.total_amount).toFixed(2)} • ${order.status}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }
            
            if (html === '') {
                html = '<div class="search-empty">No orders found</div>';
            }
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        function highlightOrder(orderId) {
            // Find and highlight the order row
            const rows = document.querySelectorAll('#ordersTableBody tr');
            rows.forEach(row => {
                if (row.cells[0].textContent === `#${orderId}`) {
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
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>


</body>
</html>
