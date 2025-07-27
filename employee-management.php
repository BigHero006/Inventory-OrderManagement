<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

$admin = new Admin();

// Fetch employees from database
try {
    $employees = $admin->getEmployees(); // We'll add this method
} catch (Exception $e) {
    $employees = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Admin Dashboard</title>
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
                <a href="employee-management.php" class="nav-link active">
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
                <div class="page-title">
                    <h1><i class="fas fa-users-cog"></i> Employee Activities</h1>
                    <p>Monitor and manage employee activities and performance</p>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Employee Activities</h3>
                    <div class="search-filter">
                        <input type="text" id="employeeSearch" placeholder="Search employees...">
                    </div>
                </div>
                <div class="table-container">
                    <table class="employees-table" id="employeesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Join Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                            <tr data-employee-id="<?php echo $employee['id']; ?>">
                                <td><?php echo $employee['id']; ?></td>
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">
                                            <?php echo strtoupper(substr($employee['firstName'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="employee-name"><?php echo htmlspecialchars($employee['firstName'] . ' ' . $employee['lastName']); ?></span>
                                            <small>Employee</small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge active">
                                        Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="viewEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-edit" onclick="editEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteEmployee(<?php echo $employee['id']; ?>)">
                                            <i class="fas fa-trash"></i>
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
        // Employee management functions
        function viewEmployee(employeeId) {
            alert(`View employee activities for Employee #${employeeId}`);
        }

        function editEmployee(employeeId) {
            alert(`Edit employee #${employeeId}`);
        }

        function deleteEmployee(employeeId) {
            if (confirm(`Are you sure you want to delete employee #${employeeId}?`)) {
                alert(`Delete employee #${employeeId} - This functionality would be implemented with AJAX`);
            }
        }

        // Search functionality
        document.getElementById('employeeSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#employeesTable tbody tr');

            rows.forEach(row => {
                const employeeName = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();

                const matches = employeeName.includes(searchTerm) || email.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
        });
    </script>

    <style>
        .employee-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
                            <h3>Online Now</h3>
                            <p class="stat-number online-color" id="onlineEmployees">0</p>
                            <small>Currently active</small>
                        </div>
                    </div>
                    <div class="finance-card">
                        <div class="card-icon performance">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-content">
                            <h3>Performance</h3>
                            <p class="stat-number performance-color">85%</p>
                            <small>Average rating</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Activities -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Employee Activities</h3>
                    <div class="header-actions">
                        <button class="btn-primary" onclick="refreshActivities()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div id="employeeActivities">
                    <!-- Activities will be loaded via AJAX -->
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Performance Metrics</h3>
                </div>
                <div class="performance-grid">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="metric-info">
                            <h4>Tasks Completed</h4>
                            <p class="metric-value">247</p>
                            <small class="metric-change positive">+12% from last week</small>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="metric-info">
                            <h4>Average Response Time</h4>
                            <p class="metric-value">2.3h</p>
                            <small class="metric-change negative">+5% from last week</small>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="metric-info">
                            <h4>Customer Satisfaction</h4>
                            <p class="metric-value">4.7/5</p>
                            <small class="metric-change positive">+3% from last week</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load employee stats
        function loadEmployeeStats() {
            fetch('api/admin_api.php?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalEmployees').textContent = data.data.employee_users || 0;
                        document.getElementById('onlineEmployees').textContent = Math.floor(Math.random() * data.data.employee_users) || 0;
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
        }

        // Load employee activities
        function loadEmployeeActivities() {
            const activities = [
                { user: 'John Doe', action: 'Updated inventory item #1234', time: '5 minutes ago', type: 'update' },
                { user: 'Jane Smith', action: 'Processed order #5678', time: '12 minutes ago', type: 'order' },
                { user: 'Mike Johnson', action: 'Added new product category', time: '25 minutes ago', type: 'create' },
                { user: 'Sarah Wilson', action: 'Generated sales report', time: '1 hour ago', type: 'report' },
                { user: 'David Brown', action: 'Updated customer information', time: '2 hours ago', type: 'update' }
            ];

            const container = document.getElementById('employeeActivities');
            let html = '';

            activities.forEach(activity => {
                html += `
                    <div class="activity-item ${activity.type}">
                        <div class="activity-icon">
                            <i class="fas ${getActivityIcon(activity.type)}"></i>
                        </div>
                        <div class="activity-content">
                            <strong>${activity.user}</strong>
                            <p>${activity.action}</p>
                            <small>${activity.time}</small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function getActivityIcon(type) {
            switch(type) {
                case 'update': return 'fa-edit';
                case 'order': return 'fa-shopping-cart';
                case 'create': return 'fa-plus';
                case 'report': return 'fa-chart-bar';
                default: return 'fa-info';
            }
        }

        function refreshActivities() {
            loadEmployeeActivities();
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployeeStats();
            loadEmployeeActivities();
        });
    </script>

    <style>
        .online-color {
            color: #4CAF50 !important;
        }

        .performance-color {
            color: #FF9800 !important;
        }

        .card-icon.online {
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
        }

        .card-icon.performance {
            background: linear-gradient(135deg, #FF9800 0%, #FFC107 100%);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }

        .activity-item:hover {
            background: #f8f9ff;
            transform: translateX(5px);
        }

        .activity-item.update {
            border-left-color: #4facfe;
            background: linear-gradient(135deg, #f8fbff 0%, #f0f8ff 100%);
        }

        .activity-item.order {
            border-left-color: #11998e;
            background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
        }

        .activity-item.create {
            border-left-color: #43e97b;
            background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
        }

        .activity-item.report {
            border-left-color: #fa709a;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe0e6 100%);
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 18px;
        }

        .activity-content strong {
            font-size: 16px;
            color: #333;
        }

        .activity-content p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .activity-content small {
            color: #999;
            font-size: 12px;
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .metric-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
            display: flex;
            align-items: center;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 24px;
        }

        .metric-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .metric-change {
            font-size: 12px;
            font-weight: 600;
        }

        .metric-change.positive {
            color: #4CAF50;
        }

        .metric-change.negative {
            color: #f44336;
        }
    </style>
</body>
</html>
