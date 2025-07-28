<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

$admin = new Admin();

// Fetch financial data
try {
    $financialData = $admin->getFinancialData();
    $recentOrders = $admin->getRecentOrders(10);
} catch (Exception $e) {
    $financialData = ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0, 'unique_customers' => 0];
    $recentOrders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - Admin Dashboard</title>
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
                <a href="financial-reports.php" class="nav-link active">
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
                    <h1><i class="fas fa-credit-card"></i> Financial Reports</h1>
                    <p>Access financial data and business insights</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="exportFinancialReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>

            <!-- Financial Statistics -->
            <div class="stats-section">
                <h2 class="section-title">Financial Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Total Revenue</h3>
                            <div class="stat-number">$<?php echo number_format($financialData['total_revenue'] ?? 0, 2); ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-chart-up"></i>
                                    All time earnings
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Total Orders</h3>
                            <div class="stat-number"><?php echo $financialData['total_orders'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-box"></i>
                                    Completed orders
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Average Order Value</h3>
                            <div class="stat-number">$<?php echo number_format($financialData['avg_order_value'] ?? 0, 2); ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-calculator"></i>
                                    Per order average
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Unique Customers</h3>
                            <div class="stat-number"><?php echo $financialData['unique_customers'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-user-plus"></i>
                                    Customer base
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Revenue Transactions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Revenue Transactions</h3>
                    <div class="search-filter">
                        <select id="timeFilter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table class="financial-table" id="financialTable">
                        <thead>
                            <tr>
                                <th class="sortable">Transaction ID</th>
                                <th class="sortable">Customer</th>
                                <th class="sortable">Date</th>
                                <th class="sortable">Order Status</th>
                                <th class="sortable">Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td>
                                    <div class="customer-info">
                                        <i class="fas fa-user-circle"></i>
                                        <span><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td class="amount-cell">$<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                <td>
                                    <button class="btn-view" onclick="viewTransaction(<?php echo $order['order_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Charts Section -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Revenue Analytics</h3>
                </div>
                <div class="charts-container">
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-line"></i>
                        <h4>Revenue Trends</h4>
                        <p>Monthly revenue visualization will be displayed here</p>
                    </div>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-pie"></i>
                        <h4>Revenue by Category</h4>
                        <p>Product category breakdown will be displayed here</p>
                    </div>
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
                <h4>Reports</h4>
                <ul>
                    <li><a href="financial-reports.php">Financial</a></li>
                    <li><a href="system-reports.php">System</a></li>
                    <li><a href="export-reports.php">Export</a></li>
                    <li><a href="admindashboard.php">Dashboard</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Financial Tools</h4>
                <ul>
                    <li><a href="#revenue">Revenue Analysis</a></li>
                    <li><a href="#expenses">Expense Tracking</a></li>
                    <li><a href="#profit">Profit Margins</a></li>
                    <li><a href="#forecasts">Forecasts</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Export Options</h4>
                <div class="social-links">
                    <a href="#pdf" aria-label="PDF Export"><i class="fas fa-file-pdf"></i></a>
                    <a href="#excel" aria-label="Excel Export"><i class="fas fa-file-excel"></i></a>
                    <a href="#csv" aria-label="CSV Export"><i class="fas fa-file-csv"></i></a>
                    <a href="#print" aria-label="Print"><i class="fas fa-print"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Wastu Financial Reports. All rights reserved.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#audit">Audit Trail</a>
            </div>
        </div>
    </footer>

    <script>
        function exportFinancialReport() {
            window.location.href = 'export-reports.php?type=financial';
        }

        function viewTransaction(orderId) {
            alert(`View transaction details for Order #${orderId}`);
        }

        // Time filter functionality
        document.getElementById('timeFilter').addEventListener('change', function() {
            const filter = this.value;
            alert(`Filter by: ${filter} - Functionality will be implemented`);
        });
    </script>

    <style>
        .amount-cell {
            font-weight: bold;
            color: #28a745;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customer-info i {
            color: #667eea;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .chart-placeholder {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .chart-placeholder i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .chart-placeholder h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .chart-placeholder p {
            color: #666;
            margin: 0;
        }

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

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>
           