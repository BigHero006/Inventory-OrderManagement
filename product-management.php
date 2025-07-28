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
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = 'Product added successfully!';
    $messageType = 'success';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                // Check for form token to prevent duplicate submissions
                $token = $_POST['form_token'] ?? '';
                $sessionToken = $_SESSION['form_token'] ?? '';
                
                if (empty($token) || $token !== $sessionToken) {
                    $message = 'Invalid form submission. Please try again.';
                    $messageType = 'error';
                    break;
                }
                
                // Clear the token to prevent reuse
                unset($_SESSION['form_token']);
                
                try {
                    $success = $admin->addProduct(
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['price'],
                        $_POST['category'],
                        $_POST['supplier_id'],
                        $_POST['quantity'] ?? 0,
                        $_POST['min_stock'] ?? 10
                    );
                    
                    if ($success) {
                        $message = 'Product added successfully!';
                        $messageType = 'success';
                        
                        // Generate new token for next submission
                        $_SESSION['form_token'] = bin2hex(random_bytes(32));
                        
                        // Redirect to prevent resubmission on page refresh
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                        exit();
                    } else {
                        $message = 'Failed to add product.';
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

// Fetch products and suppliers from database
try {
    $products = $admin->getProducts();
    $suppliers = $admin->getSuppliers();
} catch (Exception $e) {
    $products = [];
    $suppliers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Dashboard</title>
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
                <a href="product-management.php" class="nav-link active">
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
                    <h1><i class="fas fa-box"></i> Product Management</h1>
                    <p>Manage product inventory and catalog</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="exportProductsReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <button class="btn-primary" onclick="showAddProductModal()">
                        <i class="fas fa-plus"></i> Add Product
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

            <!-- Products Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Products Database Table</h3>
                    <div class="search-filter">
                        <input type="text" id="productSearch" placeholder="Search products...">
                        <select id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Books">Books</option>
                            <option value="Home">Home</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table class="database-table" id="productsTable">
                        <thead>
                            <tr>
                                <th class="id-column">product_id</th>
                                <th>name</th>
                                <th>description</th>
                                <th>category</th>
                                <th>price</th>
                                <th class="id-column">supplier_id</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr data-product-id="<?php echo $product['product_id']; ?>">
                                <td class="id-column"><?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                                <td><span class="product-price">Rs <?php echo number_format($product['price'], 2); ?></span></td>
                                <td class="id-column"><?php echo $product['supplier_id'] ?? '<span class="null-value">NULL</span>'; ?></td>
                                <td><?php echo $product['created_at'] ?? '<span class="null-value">NULL</span>'; ?></td>
                                <td><?php echo $product['updated_at'] ?? '<span class="null-value">NULL</span>'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="editProduct(<?php echo $product['product_id']; ?>)" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>)" title="Delete Product">
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

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Product</h3>
                <span class="close" onclick="closeModal('addProductModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addProductForm" method="POST" action="">
                    <input type="hidden" name="action" value="add_product">
                    <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token']; ?>">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="supplier_id">Supplier ID</label>
                        <select id="supplier_id" name="supplier_id">
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>">
                                <?php echo $supplier['supplier_id'] . ' - ' . htmlspecialchars($supplier['company_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" form="addProductForm" class="btn-primary">Add Product</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Product management functions
        function exportProductsReport() {
            window.location.href = 'export-reports.php?type=products';
        }

        function editProduct(productId) {
            // Fetch product data from API
            fetch(`api/admin_api.php?action=get_product&id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        
                        // Populate form fields
                        document.getElementById('edit_product_id').value = product.product_id;
                        document.getElementById('edit_name').value = product.name;
                        document.getElementById('edit_description').value = product.description;
                        document.getElementById('edit_price').value = product.price;
                        document.getElementById('edit_quantity').value = product.quantity;
                        document.getElementById('edit_category').value = product.category;
                        document.getElementById('edit_supplier_id').value = product.supplier_id;
                        
                        // Show modal
                        document.getElementById('editProductModal').style.display = 'flex';
                    } else {
                        alert('Error loading product data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product data');
                });
        }
        
        function updateProduct() {
            const form = document.getElementById('editProductForm');
            const formData = new FormData(form);
            formData.append('action', 'update_product');
            
            fetch('api/admin_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product updated successfully!');
                    closeModal('editProductModal');
                    location.reload(); // Refresh the page to show updated data
                } else {
                    alert('Error updating product: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating product');
            });
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                alert(`Delete product with ID: ${productId}`);
            }
        }

        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('productSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            
            if (searchInput) {
                searchInput.addEventListener('input', filterProducts);
            }
            if (categoryFilter) {
                categoryFilter.addEventListener('change', filterProducts);
            }

            // Form submission handling
            const addProductForm = document.getElementById('addProductForm');
            if (addProductForm) {
                console.log('Form found, adding submit listener');
                
                // Remove any existing event listeners to prevent duplicates
                addProductForm.removeEventListener('submit', handleFormSubmit);
                addProductForm.addEventListener('submit', handleFormSubmit);
            } else {
                console.error('Form not found');
            }
        });

        // Separate function to handle form submission
        function handleFormSubmit(e) {
            console.log('Form submit triggered');
            
            // Prevent multiple submissions
            const submitBtn = document.querySelector('button[form="addProductForm"]');
            if (submitBtn.disabled) {
                console.log('Form already being submitted, preventing duplicate');
                e.preventDefault();
                return false;
            }
            
            const price = parseFloat(document.getElementById('price').value);
            
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                return false;
            }
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Product...';
            
            console.log('Form validation passed, submitting...');
            // Allow form to submit normally
            return true;
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const rows = document.querySelectorAll('#productsTable tbody tr');

            rows.forEach(row => {
                const productName = row.cells[1].textContent.toLowerCase();
                const category = row.cells[3].textContent.trim();

                const matchesSearch = productName.includes(searchTerm);
                const matchesCategory = !categoryFilter || category === categoryFilter;

                row.style.display = matchesSearch && matchesCategory ? '' : 'none';
            });
        }
    </script>
    
    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Product</h3>
                <span class="close" onclick="closeModal('editProductModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <div class="form-group">
                        <label for="edit_name">Product Name</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_price">Price (Rs)</label>
                            <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_quantity">Quantity</label>
                            <input type="number" id="edit_quantity" name="quantity" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_category">Category</label>
                            <select id="edit_category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Books">Books</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Clothing">Clothing</option>
                                <option value="Home">Home</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_supplier_id">Supplier</label>
                            <select id="edit_supplier_id" name="supplier_id" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['supplier_id']; ?>">
                                        <?php echo htmlspecialchars($supplier['company_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="button" class="btn-primary" onclick="updateProduct()">Update Product</button>
            </div>
        </div>
    </div>
</body>
</html>
