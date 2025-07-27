<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

$admin = new Admin();

// Fetch orders from database
try {
    $orders = $admin->getAllOrders();
} catch (Exception $e) {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="admin-dashboard.css">
    <script src="js/table-enhancer.js"></script>
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
                <a href="admindashboard.php" class="nav-link">
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
                <a href="order-management.php" class="nav-link active">
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
                <a href="system-reports.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>System Reports</span>
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
                <div class="page-title">
                    <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
                    <p>Track and manage all customer orders and fulfillment</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="exportOrdersReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>All Orders</h3>
                    <div class="search-filter">
                        <input type="text" id="orderSearch" placeholder="Search orders...">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>order_id</th>
                                <th>user_id</th>
                                <th>order_date</th>
                                <th>total_amount</th>
                                <th>status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr data-order-id="<?php echo $order['order_id']; ?>">
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo $order['user_id']; ?></td>
                                <td><?php echo date('M j, Y H:i:s', strtotime($order['order_date'])); ?></td>
                                <td>$<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="viewOrder(<?php echo $order['order_id']; ?>)" title="View Order Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-edit" onclick="editOrder(<?php echo $order['order_id']; ?>)" title="Edit Order">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteOrder(<?php echo $order['order_id']; ?>)" title="Cancel Order">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Order management functions
        function exportOrdersReport() {
            window.location.href = 'export-reports.php?type=orders';
        }

        function viewOrder(orderId) {
            alert(`View order details for Order #${orderId}`);
        }

        function editOrder(orderId) {
            alert(`Edit order #${orderId}`);
        }

        // Search and filter functionality
        document.getElementById('orderSearch').addEventListener('input', function() {
            filterOrders();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            filterOrders();
        });

        function filterOrders() {
            const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#ordersTable tbody tr');

            rows.forEach(row => {
                const orderId = row.cells[0].textContent.toLowerCase();
                const customer = row.cells[1].textContent.toLowerCase();
                const status = row.cells[4].textContent.trim();

                const matchesSearch = orderId.includes(searchTerm) || customer.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }
    </script>

    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .customer-info, .contact-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .customer-info {
            flex-direction: row;
            align-items: center;
            gap: 8px;
        }

        .customer-icon {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .btn-view {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</body>
</html>
                        </div>
                        <div class="stat-content">
                            <h3>Total Orders</h3>
                            <div class="stat-number"><?php echo count($orders); ?></div>
                            <div class="stat-change">All time orders</div>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Completed</h3>
                            <div class="stat-number"><?php echo count(array_filter($orders, function($order) { return ($order['status'] ?? '') === 'Completed'; })); ?></div>
                            <div class="stat-change">Successfully delivered</div>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Pending</h3>
                            <div class="stat-number"><?php echo count(array_filter($orders, function($order) { return ($order['status'] ?? '') === 'Pending'; })); ?></div>
                            <div class="stat-change">Processing orders</div>
                        </div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Revenue</h3>
                            <div class="stat-number">$<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></div>
                            <div class="stat-change">Total earnings</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Order Directory</h3>
                    <div class="search-filter">
                        <input type="text" id="orderSearch" placeholder="Search orders...">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <input type="date" id="dateFilter" placeholder="Filter by date">
                    </div>
                </div>
                <div class="table-container">
                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                <tr data-order-id="<?php echo $order['order_id']; ?>">
                                    <td>
                                        <div class="order-id">
                                            <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="customer-details">
                                                <strong><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown Customer'); ?></strong>
                                                <span class="customer-id">ID: <?php echo $order['customer_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="order-date">
                                            <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="amount">
                                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $order['status'] ?? 'Pending';
                                        $statusClass = match(strtolower($status)) {
                                            'completed' => 'success',
                                            'shipped' => 'info',
                                            'processing' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'pending'
                                        };
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $paymentStatus = $order['payment_status'] ?? 'Pending';
                                        $paymentClass = match(strtolower($paymentStatus)) {
                                            'paid' => 'success',
                                            'refunded' => 'info',
                                            'failed' => 'danger',
                                            default => 'pending'
                                        };
                                        ?>
                                        <span class="payment-badge <?php echo $paymentClass; ?>"><?php echo ucfirst($paymentStatus); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-view" onclick="viewOrder(<?php echo $order['order_id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-edit" onclick="editOrder(<?php echo $order['order_id']; ?>)" title="Edit Order">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-ship" onclick="shipOrder(<?php echo $order['order_id']; ?>)" title="Mark as Shipped">
                                                <i class="fas fa-shipping-fast"></i>
                                            </button>
                                            <button class="btn-print" onclick="printOrder(<?php echo $order['order_id']; ?>)" title="Print Invoice">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-shopping-cart"></i>
                                        <p>No orders found. Start by creating your first order.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Order Modal -->
    <div id="createOrderModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Create New Order</h3>
                <span class="close" onclick="closeModal('createOrderModal')">&times;</span>
            </div>
            <form id="createOrderForm" class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="customerId">Customer</label>
                        <select id="customerId" name="customer_id" required>
                            <option value="">Select Customer</option>
                            <option value="1">John Doe</option>
                            <option value="2">Jane Smith</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="orderDate">Order Date</label>
                        <input type="date" id="orderDate" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="totalAmount">Total Amount</label>
                        <input type="number" id="totalAmount" name="total_amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="orderStatus">Status</label>
                        <select id="orderStatus" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paymentStatus">Payment Status</label>
                        <select id="paymentStatus" name="payment_status">
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                            <option value="Failed">Failed</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="orderNotes">Order Notes</label>
                        <textarea id="orderNotes" name="notes" rows="3" placeholder="Additional notes about the order..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('createOrderModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Create Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Order management functions
        function showCreateOrderModal() {
            document.getElementById('createOrderModal').style.display = 'block';
        }

        function viewOrder(orderId) {
            alert(`View order details for ID: ${orderId}`);
        }

        function editOrder(orderId) {
            alert(`Edit order with ID: ${orderId}`);
        }

        function shipOrder(orderId) {
            if (confirm('Mark this order as shipped?')) {
                alert(`Order ${orderId} marked as shipped`);
            }
        }

        function printOrder(orderId) {
            alert(`Print invoice for order ${orderId}`);
        }

        function exportOrders() {
            alert('Export orders functionality would be implemented here');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Search and filter functionality
        function filterOrders() {
            const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('#ordersTable tbody tr');

            rows.forEach(row => {
                if (row.querySelector('.empty-state')) return;
                
                const orderId = row.cells[0].textContent.toLowerCase();
                const customer = row.cells[1].textContent.toLowerCase();
                const date = row.cells[2].textContent;
                const status = row.cells[4].textContent.trim();

                const matchesSearch = orderId.includes(searchTerm) || customer.includes(searchTerm);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesDate = !dateFilter || date.includes(new Date(dateFilter).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'}));

                row.style.display = matchesSearch && matchesStatus && matchesDate ? '' : 'none';
            });
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('orderSearch').addEventListener('input', filterOrders);
            document.getElementById('statusFilter').addEventListener('change', filterOrders);
            document.getElementById('dateFilter').addEventListener('change', filterOrders);

            // Form submission
            document.getElementById('createOrderForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                console.log('Order data:', Object.fromEntries(formData));
                closeModal('createOrderModal');
                alert('Order would be created in the database');
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        .customer-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .btn-ship {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .btn-ship:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .btn-print {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
        }

        .btn-print:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .payment-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-badge.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .payment-badge.pending {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }

        .payment-badge.danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .payment-badge.info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }
    </style>
</body>
</html>
