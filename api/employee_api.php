<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../classes/Employee.php';
require_once '../session_protection.php';

// Check if user is logged in as employee
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Employee') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$employee = new Employee();
$response = [];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'dashboard_stats':
                    $response = $employee->getDashboardStats();
                    break;
                    
                case 'recent_orders':
                    $limit = $_GET['limit'] ?? 5;
                    $response = $employee->getRecentOrders($limit);
                    break;
                    
                case 'search':
                    $query = $_GET['query'] ?? '';
                    $type = $_GET['type'] ?? 'all';
                    if (empty($query)) {
                        $response = ['error' => 'Search query is required'];
                    } else {
                        $response = $employee->search($query, $type);
                    }
                    break;
                    
                case 'orders':
                    $status = $_GET['status'] ?? null;
                    $limit = $_GET['limit'] ?? 50;
                    $offset = $_GET['offset'] ?? 0;
                    $response = $employee->getAllOrders($status, $limit, $offset);
                    break;
                    
                case 'products':
                    $limit = $_GET['limit'] ?? 50;
                    $offset = $_GET['offset'] ?? 0;
                    $response = $employee->getProducts($limit, $offset);
                    break;
                    
                case 'suppliers':
                    $response = $employee->getSuppliers();
                    break;
                    
                case 'notifications':
                    $limit = $_GET['limit'] ?? 10;
                    $unread_only = $_GET['unread_only'] ?? false;
                    $response = $employee->getNotifications($limit, $unread_only);
                    break;
                    
                default:
                    http_response_code(400);
                    $response = ['error' => 'Invalid action'];
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'add_product':
                    $name = $input['name'] ?? '';
                    $description = $input['description'] ?? '';
                    $price = $input['price'] ?? 0;
                    $category = $input['category'] ?? '';
                    $supplierId = $input['supplier_id'] ?? null;
                    
                    if (empty($name) || $price <= 0) {
                        http_response_code(400);
                        $response = ['error' => 'Invalid product data'];
                    } else {
                        $result = $employee->addProduct($name, $description, $price, $category, $supplierId);
                        $response = ['success' => $result];
                    }
                    break;
                    
                case 'create_order':
                    $userId = $input['user_id'] ?? null;
                    $items = $input['items'] ?? [];
                    $totalAmount = $input['total_amount'] ?? 0;
                    
                    if (!$userId || empty($items) || $totalAmount <= 0) {
                        http_response_code(400);
                        $response = ['error' => 'Invalid order data'];
                    } else {
                        $orderId = $employee->createOrder($userId, $items, $totalAmount);
                        $response = ['success' => $orderId !== false, 'order_id' => $orderId];
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    $response = ['error' => 'Invalid action'];
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'update_order_status':
                    $orderId = $input['order_id'] ?? null;
                    $status = $input['status'] ?? '';
                    
                    if (!$orderId || empty($status)) {
                        http_response_code(400);
                        $response = ['error' => 'Invalid order update data'];
                    } else {
                        $result = $employee->updateOrderStatus($orderId, $status);
                        $response = ['success' => $result];
                    }
                    break;
                    
                case 'mark_notifications_read':
                    $notificationIds = $input['notification_ids'] ?? [];
                    
                    if (empty($notificationIds)) {
                        http_response_code(400);
                        $response = ['error' => 'No notification IDs provided'];
                    } else {
                        $result = $employee->markNotificationsRead($notificationIds);
                        $response = ['success' => $result];
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    $response = ['error' => 'Invalid action'];
            }
            break;
            
        default:
            http_response_code(405);
            $response = ['error' => 'Method not allowed'];
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Internal server error: ' . $e->getMessage()];
}

echo json_encode($response);
?>
