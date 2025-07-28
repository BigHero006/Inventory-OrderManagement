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
    $message = 'Employee updated successfully!';
    $messageType = 'success';
}

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $message = 'Employee removed successfully!';
    $messageType = 'success';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'edit_employee':
                try {
                    $success = $admin->updateEmployee(
                        $_POST['employee_id'],
                        $_POST['firstName'],
                        $_POST['lastName'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['address']
                    );
                    
                    if ($success) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=1');
                        exit();
                    } else {
                        $message = 'Failed to update employee.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error updating employee: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'delete_employee':
                try {
                    $success = $admin->deleteEmployee($_POST['employee_id']);
                    
                    if ($success) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?deleted=1');
                        exit();
                    } else {
                        $message = 'Failed to remove employee.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error removing employee: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Fetch employees from database
try {
    $employees = $admin->getEmployees();
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

            <!-- Message Display -->
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

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

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Employee Management</h4>
                <p>Comprehensive employee administration and workforce management system.</p>
            </div>
            <div class="footer-section">
                <h4>Management</h4>
                <ul>
                    <li><a href="admindashboard.php">Dashboard</a></li>
                    <li><a href="employee-management.php">Employees</a></li>
                    <li><a href="user-management.php">Users</a></li>
                    <li><a href="order-management.php">Orders</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Employee Actions</h4>
                <ul>
                    <li><a href="#add">Add Employee</a></li>
                    <li><a href="#permissions">Permissions</a></li>
                    <li><a href="#schedule">Schedules</a></li>
                    <li><a href="#performance">Performance</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>HR Tools</h4>
                <div class="social-links">
                    <a href="#profiles" aria-label="Profiles"><i class="fas fa-user-circle"></i></a>
                    <a href="#payroll" aria-label="Payroll"><i class="fas fa-money-check"></i></a>
                    <a href="#attendance" aria-label="Attendance"><i class="fas fa-clock"></i></a>
                    <a href="#reports" aria-label="Reports"><i class="fas fa-chart-line"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Wastu Employee Management. All rights reserved.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#hr-policy">HR Policy</a>
            </div>
        </div>
    </footer>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Edit Employee</h3>
                <span class="close" onclick="closeModal('editEmployeeModal')">&times;</span>
            </div>
            <form id="editEmployeeForm" method="POST">
                <input type="hidden" name="action" value="edit_employee">
                <input type="hidden" name="employee_id" id="editEmployeeId">
                
                <div class="form-group">
                    <label for="editFirstName"><i class="fas fa-user"></i> First Name</label>
                    <input type="text" id="editFirstName" name="firstName" required>
                </div>
                
                <div class="form-group">
                    <label for="editLastName"><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" id="editLastName" name="lastName" required>
                </div>
                
                <div class="form-group">
                    <label for="editEmail"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="editPhone"><i class="fas fa-phone"></i> Phone</label>
                    <input type="tel" id="editPhone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="editAddress"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea id="editAddress" name="address" rows="3"></textarea>
                </div>
            </form>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editEmployeeModal')">Cancel</button>
                <button type="submit" form="editEmployeeForm" class="btn-primary">Update Employee</button>
            </div>
        </div>
    </div>

    <script>
        // Employee management functions
        function viewEmployee(employeeId) {
            alert(`View employee activities for Employee #${employeeId}`);
        }

        function editEmployee(employeeId) {
            // Fetch employee data via AJAX
            fetch(`api/admin_api.php?action=get_employee&id=${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the edit form
                        document.getElementById('editEmployeeId').value = data.employee.id;
                        document.getElementById('editFirstName').value = data.employee.firstName || '';
                        document.getElementById('editLastName').value = data.employee.lastName || '';
                        document.getElementById('editEmail').value = data.employee.email || '';
                        document.getElementById('editPhone').value = data.employee.phone || '';
                        document.getElementById('editAddress').value = data.employee.address || '';
                        
                        // Show the modal
                        document.getElementById('editEmployeeModal').style.display = 'flex';
                    } else {
                        alert('Error fetching employee data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching employee data. Please try again.');
                });
        }

        function deleteEmployee(employeeId) {
            if (confirm('Are you sure you want to remove this employee? This action will mark them as inactive.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_employee';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'employee_id';
                idInput.value = employeeId;
                
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

        .btn-delete {
            background: linear-gradient(135deg, #ff416c 0%, #ff4757 100%);
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

        .btn-delete:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 65, 108, 0.4);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</body>
</html>
                           