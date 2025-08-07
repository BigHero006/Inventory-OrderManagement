<?php
// Temporarily bypass session for testing
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'Admin';
    $_SESSION['firstName'] = 'Admin';
    $_SESSION['lastName'] = 'User';
}

require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

// SessionManager::requireRole('Admin'); // Commented for testing

$firstName = $_SESSION['firstName'] ?? 'Admin';
$lastName = $_SESSION['lastName'] ?? 'User';

$admin = new Admin();

// Fetch financial data
$dataError = null;
try {
    $financialData = $admin->getFinancialData();
    $recentOrders = $admin->getRecentOrders(6);
    $monthlyRevenue = $admin->getMonthlyRevenue();
    $topProducts = $admin->getTopProducts(3);
    $paymentStats = $admin->getPaymentMethodStats();
    
    // Debug: Display what we got
    echo "<!-- DEBUG: Financial Data: " . json_encode($financialData) . " -->";
    echo "<!-- DEBUG: Recent Orders Count: " . count($recentOrders) . " -->";
    echo "<!-- DEBUG: Monthly Revenue Count: " . count($monthlyRevenue) . " -->";
    
} catch (Exception $e) {
    echo "<!-- DEBUG ERROR: " . $e->getMessage() . " -->";
    $dataError = $e->getMessage();
    
    // Initialize empty arrays instead of sample data
    $financialData = ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0, 'unique_customers' => 0, 'completed_orders' => 0, 'pending_orders' => 0, 'shipped_orders' => 0, 'cancelled_orders' => 0];
    $recentOrders = [];
    $monthlyRevenue = [];
    $topProducts = [];
    $paymentStats = [];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <!-- Database Status Message -->
            <?php if ($dataError): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Database Error:</strong> Unable to connect to database. Please check your XAMPP server and database setup.
                <br><small>Technical details: <?php echo htmlspecialchars($dataError); ?></small>
                <br><a href="setup_database.php" style="color: #991b1b; text-decoration: underline;">→ Run Database Setup</a>
            </div>
            <?php elseif (($financialData['total_orders'] ?? 0) == 0): ?>
            <div class="alert alert-info" style="background: #dbeafe; color: #1e40af; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #93c5fd;">
                <i class="fas fa-info-circle"></i>
                <strong>No Data Available:</strong> There are currently no orders in the database. 
                <br><a href="setup_database.php" style="color: #1e40af; text-decoration: underline;">→ Add Sample Data</a> | 
                <a href="order-management.php" style="color: #1e40af; text-decoration: underline;">→ Manage Orders</a>
            </div>
            <?php endif; ?>

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

            <!-- Spacer -->
            <div class="section-spacer"></div>

            <!-- Order Status Breakdown -->
            <div class="stats-section">
                <h2 class="section-title">Order Status Overview</h2>
                <div class="order-status-grid">
                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Delivered Orders</h3>
                            <div class="stat-number"><?php echo $financialData['completed_orders'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-percentage"></i>
                                    <?php 
                                    $totalOrders = $financialData['total_orders'] ?? 1;
                                    $completionRate = $totalOrders > 0 ? round(($financialData['completed_orders'] / $totalOrders) * 100, 1) : 0;
                                    echo $completionRate . '% completion rate';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Shipped Orders</h3>
                            <div class="stat-number"><?php echo $financialData['shipped_orders'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-route"></i>
                                    In transit
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Pending Orders</h3>
                            <div class="stat-number"><?php echo $financialData['pending_orders'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    Awaiting processing
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card-enhanced stagger-up hover-lift">
                        <div class="stat-icon-enhanced" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="text-gradient">Cancelled Orders</h3>
                            <div class="stat-number"><?php echo $financialData['cancelled_orders'] ?? 0; ?></div>
                            <div class="stat-details">
                                <span class="detail-item">
                                    <i class="fas fa-ban"></i>
                                    Cancelled orders
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacer -->
            <div class="section-spacer"></div>

            <!-- Financial Charts Section -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Revenue Analytics</h3>
                </div>
                <div class="charts-container">
                    <div class="chart-container">
                        <canvas id="revenueChart" style="max-height: 300px;"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="paymentChart" style="max-height: 300px;"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="orderStatusChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Spacer -->
            <div class="section-spacer"></div>

            <!-- Recent Orders Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-shopping-cart"></i> Recent Orders from Order Management</h3>
                    <div class="card-actions">
                        <a href="order-management.php" class="btn btn-secondary">
                            <i class="fas fa-external-link-alt"></i> View All Orders
                        </a>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table enhanced-table" data-sortable="true" data-searchable="true">
                        <thead>
                            <tr>
                                <th data-column="order_id">Order ID</th>
                                <th data-column="customer">Customer</th>
                                <th data-column="total_amount">Amount</th>
                                <th data-column="status">Status</th>
                                <th data-column="order_date">Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><span class="order-id">#ORD<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                                    <td>
                                        <div class="customer-info">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest Customer'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="amount-cell">Rs <?php echo number_format($order['total_amount'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <button onclick="viewTransaction(<?php echo $order['order_id']; ?>)" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div style="padding: 40px;">
                                            <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                                            <p>No recent orders found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Spacer -->
            <div class="section-spacer"></div>

            <!-- Top Products Section -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-star"></i> Top Products by Value</h3>
                </div>
                <div class="products-grid">
                    <?php if (!empty($topProducts)): ?>
                        <?php foreach ($topProducts as $index => $product): ?>
                        <div class="product-card hover-lift">
                            <div class="product-rank">#<?php echo $index + 1; ?></div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                <div class="product-stats">
                                    <span class="product-price">Rs <?php echo number_format($product['price'], 2); ?></span>
                                    <span class="product-revenue">Est. Revenue: Rs <?php echo number_format($product['estimated_revenue'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-box-open"></i>
                            <p>No product data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportFinancialReport() {
            window.location.href = 'export-reports.php?type=financial';
        }

        function viewTransaction(orderId) {
            window.location.href = 'order-management.php?highlight=' + orderId;
        }

        // Time filter functionality
        document.getElementById('timeFilter').addEventListener('change', function() {
            const filter = this.value;
            // In a full implementation, this would filter the data
            location.reload();
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
            gap: 30px;
            margin-top: 30px;
        }

        .charts-container:has(#orderStatusChart) {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }

        @media (max-width: 1200px) {
            .charts-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            min-height: 200px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 25px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .product-rank {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .product-info h4 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 18px;
        }

        .product-category {
            color: #666;
            font-size: 14px;
            margin: 0 0 15px 0;
            text-transform: capitalize;
        }

        .product-stats {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .product-price {
            font-weight: bold;
            color: #28a745;
            font-size: 16px;
        }

        .product-revenue {
            color: #667eea;
            font-size: 14px;
        }

        .no-data {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-data i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
            display: block;
        }

        .order-id {
            font-family: monospace;
            font-weight: bold;
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .enhanced-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .enhanced-table th,
        .enhanced-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .enhanced-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .enhanced-table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .section-spacer {
            height: 50px;
        }

        .stats-section {
            margin-bottom: 50px;
        }

        .content-card {
            margin-bottom: 50px;
            padding: 30px;
        }

        .stats-grid {
            gap: 30px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            min-height: 250px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .products-grid {
            gap: 25px;
            margin-top: 25px;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        .order-status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .order-status-grid {
                grid-template-columns: 1fr;
                max-width: 400px;
            }
        }
    </style>

    <script>
        // Monthly Revenue Chart
        <?php if (!empty($monthlyRevenue)): ?>
        const revenueData = {
            labels: [<?php echo implode(',', array_map(function($r) { return "'" . $r['month_name'] . " " . $r['year'] . "'"; }, $monthlyRevenue)); ?>],
            datasets: [{
                label: 'Monthly Revenue',
                data: [<?php echo implode(',', array_column($monthlyRevenue, 'revenue')); ?>],
                backgroundColor: 'rgba(123, 92, 246, 0.2)',
                borderColor: 'rgba(123, 92, 246, 1)',
                borderWidth: 2,
                fill: true
            }]
        };
        
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: revenueData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Revenue Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueCtx.font = '16px Arial';
        revenueCtx.fillStyle = '#666';
        revenueCtx.textAlign = 'center';
        revenueCtx.fillText('No revenue data available', revenueCtx.canvas.width/2, revenueCtx.canvas.height/2);
        <?php endif; ?>

        // Payment Method Chart
        <?php if (!empty($paymentStats)): ?>
        const paymentData = {
            labels: [<?php echo implode(',', array_map(function($p) { return "'" . $p['payment_method'] . "'"; }, $paymentStats)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($paymentStats, 'total_amount')); ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 205, 86, 1)'
                ],
                borderWidth: 2
            }]
        };

        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: paymentData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Revenue by Payment Method'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php else: ?>
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        paymentCtx.font = '16px Arial';
        paymentCtx.fillStyle = '#666';
        paymentCtx.textAlign = 'center';
        paymentCtx.fillText('No payment data available', paymentCtx.canvas.width/2, paymentCtx.canvas.height/2);
        <?php endif; ?>

        // Order Status Chart
        const orderStatusData = {
            labels: ['Delivered', 'Shipped', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    <?php echo $financialData['completed_orders'] ?? 0; ?>,
                    <?php echo $financialData['shipped_orders'] ?? 0; ?>,
                    <?php echo $financialData['pending_orders'] ?? 0; ?>,
                    <?php echo $financialData['cancelled_orders'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 2
            }]
        };

        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: orderStatusData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Order Status Distribution'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Export function
        function exportFinancialReport() {
            window.location.href = 'export-reports.php?type=financial';
        }
    </script>
</body>
</html>
           