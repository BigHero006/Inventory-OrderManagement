<?php
session_start();
require_once 'session_protection.php';
require_once 'classes/Employee.php';

requireRole('Employee');

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$email = $_SESSION['email'];

$employee = new Employee();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_product':
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
            $supplierId = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
            
            if ($name && $price > 0) {
                $result = $employee->addProduct($name, $description, $price, $category, $supplierId);
                if ($result) {
                    $message = "Product added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to add product.";
                    $messageType = "error";
                }
            } else {
                $message = "Please provide valid product information.";
                $messageType = "error";
            }
            break;
    }
}

// Get products and suppliers
$products = $employee->getProducts();
$suppliers = $employee->getSuppliers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="employee-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .product-management {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-primary {
            background: #7b5cf6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            min-width: 100px;
            justify-content: center;
        }
        
        .btn-primary:hover {
            background: #6b46c1;
            color: white;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .back-btn:hover {
            background: #5a6268;
            color: white;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            border-color: #7b5cf6;
            transform: translateY(-2px);
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #7b5cf6;
        }
        
        .product-details {
            margin-bottom: 15px;
        }
        
        .product-category {
            display: inline-block;
            background: #f0f0f0;
            color: #666;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .product-supplier {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-filter input,
        .search-filter select {
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            position: relative;
            background: white;
            margin: 5% auto;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            min-width: 100px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d1e7dd;
            color: #0a3622;
            border: 1px solid #a3cfbb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
    </style>
</head>
<body class="employee-dashboard">
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-boxes"></i>
                <span>Employee Panel</span>
            </div>
            <nav>
                <a href="employeedashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="employee-orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Manage Orders</span>
                </a>
                <a href="employee-products.php" class="active">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="employee-shipments.php">
                    <i class="fas fa-truck"></i>
                    <span>Shipments</span>
                </a>
                <a href="employee-create-order.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Create Order</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header glass-card">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search products...">
                    <div id="searchResults" class="search-results"></div>
                </div>
                <div class="user-info">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></div>
                    <div class="year">Employee</div>
                </div>
            </div>

            <!-- Existing Products Content -->
    <div class="content-card">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-box"></i> Product Management</h1>
            </div>
            <div class="header-actions">
                <button class="btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
                <a href="employeedashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="search-filter">
            <input type="text" id="productSearch" placeholder="Search products...">
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Books">Books</option>
                <option value="Home">Home</option>
                <option value="Sports">Sports</option>
            </select>
        </div>

        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
                <div class="product-header">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                </div>
                <div class="product-details">
                    <div class="product-category"><?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></div>
                    <div class="product-description">
                        <?php echo htmlspecialchars($product['description'] ?? 'No description available'); ?>
                    </div>
                    <div class="product-supplier">
                        Supplier: <?php echo htmlspecialchars($product['supplier_name'] ?? 'Not specified'); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="employeedashboard.php">Dashboard</a></li>
                    <li><a href="employee-orders.php">Orders</a></li>
                    <li><a href="employee-products.php">Products</a></li>
                    <li><a href="employee-shipments.php">Shipments</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Product Management</h4>
                <ul>
                    <li><a href="#add" onclick="showAddProductModal()">Add Product</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#inventory">Inventory</a></li>
                    <li><a href="#reports">Product Reports</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Connect</h4>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Wastu Inventory Management. All rights reserved.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#cookies">Cookie Policy</a>
            </div>
        </div>
    </footer>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Product</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="addProductForm" method="POST">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category">
                        <option value="">Select Category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Books">Books</option>
                        <option value="Home">Home</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select name="supplier_id" id="supplier_id">
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>">
                            <?php echo htmlspecialchars($supplier['company_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show add product modal
        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('addProductModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addProductModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Search and filter functionality
        document.getElementById('productSearch').addEventListener('input', filterProducts);
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
        
        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDescription = card.querySelector('.product-description').textContent.toLowerCase();
                const productCategory = card.dataset.category;
                
                const matchesSearch = productName.includes(searchTerm) || 
                                    productDescription.includes(searchTerm);
                const matchesCategory = !categoryFilter || productCategory === categoryFilter;
                
                card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
            });
        }
        
        // AJAX form submission
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const productData = {
                name: formData.get('name'),
                description: formData.get('description'),
                price: parseFloat(formData.get('price')),
                category: formData.get('category'),
                supplier_id: formData.get('supplier_id') ? parseInt(formData.get('supplier_id')) : null
            };
            
            // Submit via AJAX
            fetch('api/employee_api.php?action=add_product', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(productData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    showAlert('Product added successfully!', 'success');
                    
                    // Reset form
                    document.getElementById('addProductForm').reset();
                    
                    // Optionally reload the page to show the new product
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Failed to add product. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while adding the product.', 'error');
            });
        });
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            
            const contentCard = document.querySelector('.content-card');
            const pageHeader = document.querySelector('.page-header');
            contentCard.insertBefore(alertDiv, pageHeader.nextSibling);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Enhanced search functionality for the header search box
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    performGlobalSearch(query);
                }, 300);
            });
        }

        function performGlobalSearch(query) {
            // Search in current products
            const productCards = document.querySelectorAll('.product-card');
            let found = false;
            let matchingProducts = [];
            
            productCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(query.toLowerCase())) {
                    const productName = card.querySelector('h4').textContent;
                    const productPrice = card.querySelector('.product-price').textContent;
                    matchingProducts.push({
                        name: productName,
                        price: productPrice
                    });
                    found = true;
                }
            });
            
            let html = '';
            if (found && matchingProducts.length > 0) {
                html += '<div class="search-category"><h4>Products</h4>';
                matchingProducts.slice(0, 5).forEach(product => {
                    html += `
                        <div class="search-item" onclick="highlightProduct('${product.name}')">
                            <div class="search-title">${product.name}</div>
                            <div class="search-meta">${product.price}</div>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html = '<div class="search-empty">No products found</div>';
            }
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        function highlightProduct(productName) {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                if (card.querySelector('h4').textContent === productName) {
                    card.style.backgroundColor = '#fff3cd';
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    setTimeout(() => {
                        card.style.backgroundColor = '';
                    }, 3000);
                }
            });
            searchResults.style.display = 'none';
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(event) {
            if (searchInput && searchResults && 
                !searchInput.contains(event.target) && 
                !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>    
</body>
</html>
