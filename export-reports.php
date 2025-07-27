<?php
require_once 'classes/SessionManager.php';
require_once 'classes/Admin.php';

SessionManager::requireRole('Admin');

$admin = new Admin();

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    
    switch ($type) {
        case 'financial':
            exportFinancialReport($admin);
            break;
        case 'users':
            exportUsersReport($admin);
            break;
        case 'products':
            exportProductsReport($admin);
            break;
        case 'suppliers':
            exportSuppliersReport($admin);
            break;
        case 'orders':
            exportOrdersReport($admin);
            break;
        default:
            die('Invalid report type');
    }
}

function exportFinancialReport($admin) {
    $financialData = $admin->getFinancialData();
    $recentOrders = $admin->getAllOrders();
    
    $filename = 'financial_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Financial summary
    fputcsv($output, ['Financial Summary Report']);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
    fputcsv($output, ['']);
    
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Orders', $financialData['total_orders']]);
    fputcsv($output, ['Total Revenue', '$' . number_format($financialData['total_revenue'], 2)]);
    fputcsv($output, ['Average Order Value', '$' . number_format($financialData['avg_order_value'], 2)]);
    fputcsv($output, ['Unique Customers', $financialData['unique_customers']]);
    fputcsv($output, ['']);
    
    // Orders detail
    fputcsv($output, ['Order Details']);
    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Order Date', 'Status', 'Total Amount']);
    
    foreach ($recentOrders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['customer_name'],
            $order['customer_email'],
            $order['order_date'],
            $order['status'],
            '$' . number_format($order['total_amount'] ?? 0, 2)
        ]);
    }
    
    fclose($output);
    exit;
}

function exportUsersReport($admin) {
    $users = $admin->getAllUsers();
    
    $filename = 'users_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['User Management Report']);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
    fputcsv($output, ['']);
    
    fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Address', 'Role', 'Created Date']);
    
    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['firstName'],
            $user['lastName'],
            $user['email'],
            $user['phone'] ?? '',
            $user['address'] ?? '',
            $user['role'],
            $user['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

function exportProductsReport($admin) {
    $products = $admin->getProducts();
    
    $filename = 'products_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Product Inventory Report']);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
    fputcsv($output, ['']);
    
    fputcsv($output, ['Product ID', 'Name', 'Description', 'Price', 'Category', 'Supplier', 'Stock Quantity', 'Min Stock Level', 'Created Date']);
    
    foreach ($products as $product) {
        fputcsv($output, [
            $product['product_id'],
            $product['name'],
            $product['description'],
            '$' . number_format($product['price'], 2),
            $product['category'],
            $product['supplier_name'] ?? 'N/A',
            $product['quantity'] ?? 0,
            $product['min_stock_level'] ?? 0,
            $product['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

function exportSuppliersReport($admin) {
    $suppliers = $admin->getSuppliers();
    
    $filename = 'suppliers_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Supplier Network Report']);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
    fputcsv($output, ['']);
    
    fputcsv($output, ['Supplier ID', 'Company Name', 'Contact Person', 'Email', 'Phone', 'Address', 'Created Date']);
    
    foreach ($suppliers as $supplier) {
        fputcsv($output, [
            $supplier['supplier_id'],
            $supplier['company_name'],
            $supplier['contact_person'],
            $supplier['email'],
            $supplier['phone'],
            $supplier['address'],
            $supplier['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

function exportOrdersReport($admin) {
    $orders = $admin->getAllOrders();
    
    $filename = 'orders_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Order Management Report']);
    fputcsv($output, ['Generated on', date('Y-m-d H:i:s')]);
    fputcsv($output, ['']);
    
    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Order Date', 'Status', 'Total Amount']);
    
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['customer_name'],
            $order['customer_email'],
            $order['order_date'],
            $order['status'],
            '$' . number_format($order['total_amount'] ?? 0, 2)
        ]);
    }
    
    fclose($output);
    exit;
}
?>
