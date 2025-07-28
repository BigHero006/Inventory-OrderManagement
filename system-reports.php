<?php
require_once 'classes/SessionManager.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="admin-dashboard.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="financial-reports.php" class="nav-link">
                    <i class="fas fa-credit-card"></i>
                    <span>Financial Reports</span>
                </a>
                <a href="system-reports.php" class="nav-link active">
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
                    <h1><i class="fas fa-chart-bar"></i> System Reports</h1>
                    <p>Comprehensive analytics and system insights</p>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button class="btn-secondary" onclick="refreshReports()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Report Filters</h3>
                </div>
                <div class="filter-section">
                    <div class="filter-group">
                        <label for="dateRange">Date Range</label>
                        <select id="dateRange">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType">
                            <option value="all" selected>All Reports</option>
                            <option value="users">User Analytics</option>
                            <option value="orders">Order Analytics</option>
                        </select>
                    </div>
                    <button class="btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- User Growth Chart -->
                <div class="content-card chart-card">
                    <div class="card-header">
                        <h3>User Growth</h3>
                        <span class="chart-period">Last 30 days</span>
                    </div>
                    <canvas id="userGrowthChart"></canvas>
                </div>

                <!-- Order Trends Chart -->
                <div class="content-card chart-card">
                    <div class="card-header">
                        <h3>Order Trends</h3>
                        <span class="chart-period">Last 30 days</span>
                    </div>
                    <canvas id="orderTrendsChart"></canvas>
                </div>

                <!-- Revenue Chart -->
                <div class="content-card chart-card">
                    <div class="card-header">
                        <h3>Revenue Overview</h3>
                        <span class="chart-period">Last 30 days</span>
                    </div>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Detailed Reports Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Detailed Analytics</h3>
                    <div class="search-filter">
                        <input type="text" id="reportSearch" placeholder="Search reports...">
                    </div>
                </div>
                <div class="table-container">
                    <table class="reports-table" id="reportsTable">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Current Value</th>
                                <th>Previous Period</th>
                                <th>Change</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody id="reportsTableBody">
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let charts = {};

        // Initialize charts
        function initializeCharts() {
            // User Growth Chart
            const userCtx = document.getElementById('userGrowthChart').getContext('2d');
            charts.userGrowth = new Chart(userCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'New Users',
                        data: [12, 19, 3, 5],
                        borderColor: 'rgb(102, 126, 234)',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Order Trends Chart
            const orderCtx = document.getElementById('orderTrendsChart').getContext('2d');
            charts.orderTrends = new Chart(orderCtx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Orders',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        backgroundColor: 'rgba(17, 153, 142, 0.8)',
                        borderColor: 'rgb(17, 153, 142)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            charts.revenue = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [4000, 3000, 5000, 4500, 6000, 5500],
                        borderColor: 'rgb(250, 112, 154)',
                        backgroundColor: 'rgba(250, 112, 154, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Load detailed reports
        function loadDetailedReports() {
            const reports = [
                { metric: 'Total Users', current: '1,234', previous: '1,156', change: '+6.7%', trend: 'up' },
                { metric: 'Total Orders', current: '567', previous: '523', change: '+8.4%', trend: 'up' },
                { metric: 'Revenue', current: '$45,678', previous: '$42,345', change: '+7.9%', trend: 'up' },
                { metric: 'Products', current: '89', previous: '87', change: '+2.3%', trend: 'up' },
                { metric: 'Avg Order Value', current: '$127.50', previous: '$134.20', change: '-5.0%', trend: 'down' },
                { metric: 'Customer Satisfaction', current: '4.8/5', previous: '4.7/5', change: '+2.1%', trend: 'up' },
            ];

            const tbody = document.getElementById('reportsTableBody');
            let html = '';

            reports.forEach(report => {
                html += `
                    <tr>
                        <td><strong>${report.metric}</strong></td>
                        <td>${report.current}</td>
                        <td>${report.previous}</td>
                        <td class="change ${report.trend}">${report.change}</td>
                        <td class="trend-icon ${report.trend}">
                            <i class="fas fa-arrow-${report.trend === 'up' ? 'up' : 'down'}"></i>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // Event handlers
        function applyFilters() {
            const dateRange = document.getElementById('dateRange').value;
            const reportType = document.getElementById('reportType').value;
            
            console.log('Applying filters:', { dateRange, reportType });
            // Here you would fetch new data based on filters
            alert('Filters applied! (This would update the charts and data in a real implementation)');
        }

        function exportReport() {
            // Generate XML report
            const xmlData = generateXMLReport();
            downloadFile(xmlData, 'system-report.xml', 'application/xml');
        }

        function generateXMLReport() {
            const currentDate = new Date().toISOString();
            const xmlDeclaration = '<' + '?xml version="1.0" encoding="UTF-8"' + '?' + '>';
            return `${xmlDeclaration}
<SystemReport generated="${currentDate}">
    <Summary>
        <TotalUsers>1234</TotalUsers>
        <TotalOrders>567</TotalOrders>
        <Revenue>45678</Revenue>
        <ProductCount>89</ProductCount>
    </Summary>
    <UserGrowth>
        <Week number="1">12</Week>
        <Week number="2">19</Week>
        <Week number="3">3</Week>
        <Week number="4">5</Week>
    </UserGrowth>
    <OrderTrends>
        <Day name="Monday">65</Day>
        <Day name="Tuesday">59</Day>
        <Day name="Wednesday">80</Day>
        <Day name="Thursday">81</Day>
        <Day name="Friday">56</Day>
        <Day name="Saturday">55</Day>
        <Day name="Sunday">40</Day>
    </OrderTrends>
    <Inventory>
        <InStock>300</InStock>
        <LowStock>50</LowStock>
        <OutOfStock>20</OutOfStock>
    </Inventory>
</SystemReport>`;
        }

        function downloadFile(data, filename, type) {
            const blob = new Blob([data], { type: type });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function refreshReports() {
            loadDetailedReports();
            // Refresh charts with new data
            Object.values(charts).forEach(chart => {
                chart.update();
            });
            alert('Reports refreshed!');
        }

        // Search functionality
        document.getElementById('reportSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#reportsTable tbody tr');
            
            rows.forEach(row => {
                const metric = row.cells[0].textContent.toLowerCase();
                row.style.display = metric.includes(searchTerm) ? '' : 'none';
            });
        });

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadDetailedReports();
        });
    </script>

    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-card {
            min-height: 350px;
        }

        .chart-card canvas {
            height: 250px !important;
        }

        .chart-period {
            font-size: 12px;
            color: #999;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .filter-section {
            display: flex;
            gap: 20px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .filter-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .reports-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .reports-table td {
            padding: 18px 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .reports-table tbody tr {
            transition: all 0.3s ease;
        }

        .reports-table tbody tr:hover {
            background: #f8f9ff;
        }

        .change.up {
            color: #4CAF50;
            font-weight: 600;
        }

        .change.down {
            color: #f44336;
            font-weight: 600;
        }

        .trend-icon.up {
            color: #4CAF50;
        }

        .trend-icon.down {
            color: #f44336;
        }

        .btn-secondary {
            background: #e0e6ed;
            color: #666;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #d0d6dd;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select {
                min-width: 100%;
            }
        }
    </style>
</body>
</html>
