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
                           