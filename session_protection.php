<?php


function requireRole($requiredRole) {
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
  
    if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
        header('Location: Signin.php');
        exit();
    }
    
    
    if ($_SESSION['role'] !== $requiredRole) {
        
        if ($_SESSION['role'] === 'Admin') {
            header('Location: admindashboard.php');
        } elseif ($_SESSION['role'] === 'Employee') {
            header('Location: employeedashboard.php');
        } else {
            header('Location: Signin.php');
        }
        exit();
    }
}

function requireAnyRole($allowedRoles = ['Admin', 'Employee']) {
   
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    
    if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
        header('Location: Signin.php');
        exit();
    }
    
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: Signin.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Employee';
}

function getUserInfo() {
    return [
        'firstName' => $_SESSION['firstName'] ?? '',
        'lastName' => $_SESSION['lastName'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'id' => $_SESSION['id'] ?? ''
    ];
}
?>
