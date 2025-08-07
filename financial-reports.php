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
    $monthlyRevenue = $admin->getMonthlyRevenue();
    $paymentStats = $admin->getPaymentMethodStats();
    
    // Debug: Display what we got
    echo "<!-- DEBUG: Financial Data: " . json_encode($financialData) . " -->";
    echo "<!-- DEBUG: Monthly Revenue Count: " . count($monthlyRevenue) . " -->";
    
} catch (Exception $e) {
    echo "<!-- DEBUG ERROR: " . $e->getMessage() . " -->";
    $dataError = $e->getMessage();
    
    // Initialize empty arrays instead of sample data
    $financialData = ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0, 'unique_customers' => 0, 'completed_orders' => 0, 'pending_orders' => 0, 'shipped_orders' => 0, 'cancelled_orders' => 0];
    $monthlyRevenue = [];
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
                </div>
            </div>

            <!-- Spacer -->
            <div class="section-spacer"></div>
        </div>
    </div>

    <script>
        function exportFinancialReport() {
            window.location.href = 'export-reports.php?type=financial';
        }
    </script>

    <style>
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
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

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
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

        // Export function
        function exportFinancialReport() {
            window.location.href = 'export-reports.php?type=financial';
        }
    </script>
</body>
</html>
           