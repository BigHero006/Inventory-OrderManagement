<?php
session_start();
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$firstName = SessionManager::get('firstName');
$lastName = SessionManager::get('lastName');

$admin = new Admin();
$message = '';
$messageType = '';

// Generate form token to prevent duplicate submissions
if (!isset($_SESSION['supplier_form_token'])) {
    $_SESSION['supplier_form_token'] = bin2hex(random_bytes(32));
}

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = 'Supplier added successfully!';
    $messageType = 'success';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_supplier':
                // Check for form token to prevent duplicate submissions
                $token = $_POST['supplier_form_token'] ?? '';
                $sessionToken = $_SESSION['supplier_form_token'] ?? '';
                
                if (empty($token) || $token !== $sessionToken) {
                    $message = 'Invalid form submission. Please try again.';
                    $messageType = 'error';
                    break;
                }
                
                // Clear the token to prevent reuse
                unset($_SESSION['supplier_form_token']);
                
                try {
                    $success = $admin->addSupplier(
                        $_POST['company_name'],
                        $_POST['contact_person'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['address']
                    );
                    
                    if ($success) {
                        $message = 'Supplier added successfully!';
                        $messageType = 'success';
                        
                        // Generate new token for next submission
                        $_SESSION['supplier_form_token'] = bin2hex(random_bytes(32));
                        
                        // Redirect to prevent resubmission on page refresh
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                        exit();
                    } else {
                        $message = 'Failed to add supplier.';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Fetch suppliers from database
try {
    $suppliers = $admin->getSuppliers();
} catch (Exception $e) {
    $suppliers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management - Admin Dashboard</title>
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
                <a href="order-management.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order Management</span>
                </a>
                <a href="product-management.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Product Management</span>
                </a>
                <a href="supplier-management.php" class="nav-link active">
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
                    <h1><i class="fas fa-truck"></i> Supplier Management</h1>
                    <p>Manage your supplier network and procurement relationships</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="exportSuppliersReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button class="btn-primary" onclick="openModal('addSupplierModal')">
                        <i class="fas fa-plus"></i> Add Supplier
                    </button>
                </div>
            </div>

            <!-- Message Display -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php endif; ?>

            <!-- Suppliers Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Suppliers Database Table</h3>
                    <div class="search-filter">
                        <input type="text" class="search-input" id="supplierSearch" placeholder="Search suppliers...">
                        <button class="export-btn" onclick="exportSuppliers()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="database-table" id="suppliersTable">
                        <thead>
                            <tr>
                                <th class="id-column">supplier_id</th>
                                <th>company_name</th>
                                <th>contact_person</th>
                                <th>email</th>
                                <th>phone</th>
                                <th>address</th>
                                <th>created_at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr data-supplier-id="<?php echo $supplier['supplier_id']; ?>">
                                <td class="id-column"><?php echo $supplier['supplier_id']; ?></td>
                                <td><?php echo htmlspecialchars($supplier['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></td>
                                <td><?php echo $supplier['created_at'] ?? '<span class="null-value">NULL</span>'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="editSupplier(<?php echo $supplier['supplier_id']; ?>)" title="Edit Supplier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteSupplier(<?php echo $supplier['supplier_id']; ?>)" title="Delete Supplier">
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

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Supplier</h3>
                <span class="close" onclick="closeModal('addSupplierModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm" method="POST" action="">
                    <input type="hidden" name="action" value="add_supplier">
                    <input type="hidden" name="supplier_form_token" value="<?php echo $_SESSION['supplier_form_token']; ?>">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Full address..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addSupplierModal')">Cancel</button>
                <button type="submit" form="addSupplierForm" class="btn-primary">Add Supplier</button>
            </div>
        </div>
    </div>

    <script>
        // Supplier management functions
        function exportSuppliersReport() {
            window.location.href = 'export-reports.php?type=suppliers';
        }

        function editSupplier(supplierId) {
            alert(`Edit supplier with ID: ${supplierId}`);
        }

        function deleteSupplier(supplierId) {
            if (confirm('Are you sure you want to delete this supplier?')) {
                alert(`Delete supplier with ID: ${supplierId}`);
            }
        }

        // Search functionality  
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('supplierSearch');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#suppliersTable tbody tr');

                    rows.forEach(row => {
                        const supplierName = row.cells[1].textContent.toLowerCase();
                        const contactInfo = row.cells[2].textContent.toLowerCase();

                        const matches = supplierName.includes(searchTerm) || contactInfo.includes(searchTerm);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }

            // Form submission handling
            const addSupplierForm = document.getElementById('addSupplierForm');
            if (addSupplierForm) {
                // Remove any existing event listeners to prevent duplicates
                addSupplierForm.removeEventListener('submit', handleSupplierFormSubmit);
                addSupplierForm.addEventListener('submit', handleSupplierFormSubmit);
            }
        });

        // Separate function to handle supplier form submission
        function handleSupplierFormSubmit(e) {
            // Prevent multiple submissions
            const submitBtn = document.querySelector('button[form="addSupplierForm"]');
            if (submitBtn.disabled) {
                console.log('Supplier form already being submitted, preventing duplicate');
                e.preventDefault();
                return false;
            }
            
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            // Phone validation (basic)
            const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return false;
            }
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Supplier...';
            
            return true;
        }
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
</body>
</html>
