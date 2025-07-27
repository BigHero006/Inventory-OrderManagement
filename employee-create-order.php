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
        case 'create_order':
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $items = json_decode($_POST['items'], true);
            $totalAmount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
            
            if ($userId && !empty($items) && $totalAmount > 0) {
                $orderId = $employee->createOrder($userId, $items, $totalAmount);
                if ($orderId) {
                    $message = "Order created successfully! Order ID: #$orderId";
                    $messageType = "success";
                } else {
                    $message = "Failed to create order.";
                    $messageType = "error";
                }
            } else {
                $message = "Please provide valid order information.";
                $messageType = "error";
            }
            break;
    }
}

// Get products and users for the form
$products = $employee->getProducts();

// Get users (customers) for order creation
require_once 'dbconnect.php';
$usersStmt = $pdo->prepare("SELECT id, firstName, lastName, email FROM users WHERE role = 'Customer' ORDER BY firstName, lastName");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order - Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .order-creation {
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
            max-width: 800px;
            margin: 0 auto 20px;
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
        
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .form-section h3 {
            margin: 0 0 20px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            border-color: #7b5cf6;
            outline: none;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e1e5e9;
        }
        
        .remove-item {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .remove-item:hover {
            background: #c82333;
        }
        
        .add-item-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .add-item-btn:hover {
            background: #218838;
        }
        
        .order-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #7b5cf6;
        }
        
        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #7b5cf6;
            text-align: right;
            margin-top: 15px;
        }
        
        .btn-primary {
            background: #7b5cf6;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: #6b46c1;
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .item-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body class="order-creation">
    <div class="content-card">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-plus-circle"></i> Create New Order</h1>
            </div>
            <a href="employeedashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <form id="orderForm" method="POST">
            <input type="hidden" name="action" value="create_order">
            <input type="hidden" name="items" id="orderItems">
            <input type="hidden" name="total_amount" id="totalAmount">
            
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Customer Information</h3>
                <div class="form-group">
                    <label for="user_id">Select Customer *</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">Choose a customer...</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-shopping-cart"></i> Order Items</h3>
                <div class="order-items" id="orderItemsContainer">
                    <div class="item-row">
                        <div class="form-group">
                            <label>Product</label>
                            <select class="product-select" required>
                                <option value="">Select product...</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>">
                                    <?php echo htmlspecialchars($product['name']) . ' - $' . number_format($product['price'], 2); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" class="quantity-input" min="1" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Unit Price</label>
                            <input type="number" class="price-input" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <label>Subtotal</label>
                            <input type="number" class="subtotal-input" step="0.01" readonly>
                        </div>
                        <button type="button" class="remove-item" onclick="removeItem(this)" style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="add-item-btn" onclick="addItem()">
                    <i class="fas fa-plus"></i> Add Another Item
                </button>
                
                <div class="order-summary">
                    <h4>Order Summary</h4>
                    <div class="total-amount">
                        Total: $<span id="totalDisplay">0.00</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-check"></i> Create Order
            </button>
        </form>
    </div>

    <script>
        let itemCount = 1;
        
        // Add event listeners to initial item
        document.addEventListener('DOMContentLoaded', function() {
            attachItemEventListeners(document.querySelector('.item-row'));
        });
        
        function attachItemEventListeners(itemRow) {
            const productSelect = itemRow.querySelector('.product-select');
            const quantityInput = itemRow.querySelector('.quantity-input');
            const priceInput = itemRow.querySelector('.price-input');
            const subtotalInput = itemRow.querySelector('.subtotal-input');
            
            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.dataset.price || 0;
                priceInput.value = parseFloat(price).toFixed(2);
                updateSubtotal(itemRow);
            });
            
            quantityInput.addEventListener('input', function() {
                updateSubtotal(itemRow);
            });
        }
        
        function updateSubtotal(itemRow) {
            const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(itemRow.querySelector('.price-input').value) || 0;
            const subtotal = quantity * price;
            
            itemRow.querySelector('.subtotal-input').value = subtotal.toFixed(2);
            updateTotal();
        }
        
        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            document.getElementById('totalDisplay').textContent = total.toFixed(2);
            document.getElementById('totalAmount').value = total.toFixed(2);
        }
        
        function addItem() {
            itemCount++;
            const container = document.getElementById('orderItemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'item-row';
            newItem.innerHTML = `
                <div class="form-group">
                    <label>Product</label>
                    <select class="product-select" required>
                        <option value="">Select product...</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>">
                            <?php echo htmlspecialchars($product['name']) . ' - $' . number_format($product['price'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" class="quantity-input" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label>Unit Price</label>
                    <input type="number" class="price-input" step="0.01" readonly>
                </div>
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="number" class="subtotal-input" step="0.01" readonly>
                </div>
                <button type="button" class="remove-item" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(newItem);
            attachItemEventListeners(newItem);
            updateRemoveButtons();
        }
        
        function removeItem(button) {
            button.closest('.item-row').remove();
            updateTotal();
            updateRemoveButtons();
        }
        
        function updateRemoveButtons() {
            const items = document.querySelectorAll('.item-row');
            items.forEach((item, index) => {
                const removeBtn = item.querySelector('.remove-item');
                removeBtn.style.display = items.length > 1 ? 'block' : 'none';
            });
        }
        
        // Form submission
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const items = [];
            document.querySelectorAll('.item-row').forEach(row => {
                const productId = row.querySelector('.product-select').value;
                const quantity = row.querySelector('.quantity-input').value;
                const unitPrice = row.querySelector('.price-input').value;
                
                if (productId && quantity && unitPrice) {
                    items.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity),
                        unit_price: parseFloat(unitPrice)
                    });
                }
            });
            
            if (items.length === 0) {
                alert('Please add at least one item to the order.');
                return;
            }
            
            document.getElementById('orderItems').value = JSON.stringify(items);
            
            // Submit the form
            this.submit();
        });
    </script>
</body>
</html>
