<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

$admin = new Admin();
$message = '';
$messageType = '';

// Handle success messages from redirects
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Order status updated successfully!';
    $messageType = 'success';
}

if (isset($_GET['cancelled']) && $_GET['cancelled'] == '1') {
    $message = 'Order cancelled successfully!';
    $messageType = 'success';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_order_status':
                try {
                    $success = $admin->updateOrderStatus(
                        $_POST['order_id'],
                        $_POST['status']
                    );
                    
                    if ($success) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=1');
                        exit();
                    } else {
                        $message = 'Failed to update order status.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error updating order: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'cancel_order':
                try {
                    $success = $admin->cancelOrder($_POST['order_id']);
                    
                    if ($success) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?cancelled=1');
                        exit();
                    } else {
                        $message = 'Failed to cancel order.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error cancelling order: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
        }
    }
}

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
    <link rel="stylesheet" href="admin-dashboard.css?v=<?php echo time(); ?>">
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

            <!-- Message Display -->
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

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
                                        <button class="btn-cancel" onclick="cancelOrder(<?php echo $order['order_id']; ?>)" title="Cancel Order">
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

    <!-- Edit Order Status Modal -->
    <div id="editOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Update Order Status</h3>
                <span class="close" onclick="closeModal('editOrderModal')">&times;</span>
            </div>
            <form id="editOrderForm" method="POST">
                <input type="hidden" name="action" value="update_order_status">
                <input type="hidden" name="order_id" id="editOrderId">
                
                <div class="form-group">
                    <label for="orderDetails"><i class="fas fa-info-circle"></i> Order Details</label>
                    <div id="orderDetails" class="order-details-display"></div>
                </div>
                
                <div class="form-group">
                    <label for="editOrderStatus"><i class="fas fa-tasks"></i> Order Status</label>
                    <select id="editOrderStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </form>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editOrderModal')">Cancel</button>
                <button type="submit" form="editOrderForm" class="btn-primary">Update Status</button>
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
            // Fetch order data via AJAX
            fetch(`api/admin_api.php?action=get_order&id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the edit form
                        document.getElementById('editOrderId').value = data.order.order_id;
                        document.getElementById('editOrderStatus').value = data.order.status;
                        
                        // Display order details
                        const orderDetails = document.getElementById('orderDetails');
                        orderDetails.innerHTML = `
                            <div class="order-info">
                                <p><strong>Order ID:</strong> #${data.order.order_id}</p>
                                <p><strong>Customer:</strong> ${data.order.customer_name || 'N/A'}</p>
                                <p><strong>Date:</strong> ${new Date(data.order.order_date).toLocaleDateString()}</p>
                                <p><strong>Amount:</strong> $${parseFloat(data.order.total_amount || 0).toFixed(2)}</p>
                                <p><strong>Current Status:</strong> <span class="status-badge status-${data.order.status.toLowerCase()}">${data.order.status}</span></p>
                            </div>
                        `;
                        
                        // Show the modal
                        document.getElementById('editOrderModal').style.display = 'flex';
                    } else {
                        alert('Error fetching order data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching order data. Please try again.');
                });
        }

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                // Create a form to submit the cancel request
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'cancel_order';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'order_id';
                idInput.value = orderId;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                
                form.submit();
            }
        }

        // Modal functions
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        });

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
            margin: 0 2px;
        }

        .btn-view:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            margin: 0 2px;
        }

        .btn-edit:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(103, 126, 234, 0.4);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #ff9500 0%, #ff6b35 100%);
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
            margin: 0 2px;
        }

        .btn-cancel:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 149, 0, 0.4);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</body>
</html>
                     