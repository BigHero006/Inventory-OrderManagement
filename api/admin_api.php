<?php
header('Content-Type: application/json');
require_once '../classes/Database.php';
require_once '../classes/Admin.php';
require_once '../classes/SessionManager.php';

SessionManager::requireRole('Admin');

class ApiHandler {
    private $admin;

    public function __construct() {
        $this->admin = new Admin();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'GET':
                $this->handleGet($action);
                break;
            case 'POST':
                $this->handlePost($action);
                break;
            case 'DELETE':
                $this->handleDelete($action);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function handleGet($action) {
        switch ($action) {
            case 'search':
                $query = $_GET['q'] ?? '';
                $type = $_GET['type'] ?? 'all';
                $results = $this->admin->searchRecords($query, $type);
                echo json_encode(['success' => true, 'data' => $results]);
                break;
                
            case 'users':
                $users = $this->admin->getAllUsers();
                echo json_encode(['success' => true, 'data' => $users]);
                break;
                
            case 'stats':
                $userStats = $this->admin->getUserStats();
                $orderStats = $this->admin->getOrderStats();
                $supplierStats = $this->admin->getSupplierStats();
                
                $stats = array_merge($userStats, $orderStats, $supplierStats);
                echo json_encode(['success' => true, 'data' => $stats]);
                break;
                
            case 'recent_orders':
                $orders = $this->admin->getRecentOrders();
                echo json_encode(['success' => true, 'data' => $orders]);
                break;
                
            case 'recent_users':
                $users = $this->admin->getRecentUsers();
                echo json_encode(['success' => true, 'data' => $users]);
                break;
                
            case 'get_supplier':
                $supplierId = $_GET['id'] ?? 0;
                try {
                    $supplier = $this->admin->getSupplierById($supplierId);
                    if ($supplier) {
                        echo json_encode(['success' => true, 'supplier' => $supplier]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                break;
                
            case 'get_employee':
                $employeeId = $_GET['id'] ?? 0;
                try {
                    $employee = $this->admin->getEmployeeById($employeeId);
                    if ($employee) {
                        echo json_encode(['success' => true, 'employee' => $employee]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Employee not found']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                break;
                
            case 'get_order':
                $orderId = $_GET['id'] ?? 0;
                try {
                    $order = $this->admin->getOrderById($orderId);
                    if ($order) {
                        echo json_encode(['success' => true, 'order' => $order]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Order not found']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    }

    private function handlePost($action) {
        switch ($action) {
            case 'update_user_role':
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = $input['user_id'] ?? 0;
                $role = $input['role'] ?? '';
                
                if ($this->admin->updateUserRole($userId, $role)) {
                    echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update user role']);
                }
                break;
                
            case 'add_product':
                $productName = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = (float)($_POST['price'] ?? 0);
                $category = $_POST['category'] ?? '';
                $supplier_id = $_POST['supplier_id'] ?? null;
                $quantity = (int)($_POST['quantity'] ?? 0);
                $min_stock = (int)($_POST['min_stock'] ?? 10);
                
                // Convert empty string to null for supplier_id
                if ($supplier_id === '') {
                    $supplier_id = null;
                } else {
                    $supplier_id = (int)$supplier_id;
                }
                
                try {
                    if ($this->admin->addProduct($productName, $description, $price, $category, $supplier_id, $quantity, $min_stock)) {
                        echo json_encode(['success' => true, 'message' => 'Product added successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                break;
                
            case 'add_supplier':
                $company_name = $_POST['company_name'] ?? '';
                $contact_person = $_POST['contact_person'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $address = $_POST['address'] ?? '';
                
                try {
                    if ($this->admin->addSupplier($company_name, $contact_person, $email, $phone, $address)) {
                        echo json_encode(['success' => true, 'message' => 'Supplier added successfully']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add supplier']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    }

    private function handleDelete($action) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'delete_user':
                $userId = $input['user_id'] ?? 0;
                
                if ($this->admin->deleteUser($userId)) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
                }
                break;
                
            default:
                echo json_encode(['error' => 'Invalid action']);
        }
    }
}

try {
    $api = new ApiHandler();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
