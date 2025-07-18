<?php
require 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phonenumber'];
    $role = $_POST['role'];
    $password = md5($_POST['password']);
    $id = random_int(0, 999);

    $checkEmail = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($checkEmail);
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "Email Address Already Exists !!";
    } else {
        if (empty($role)) {
            echo "Role is required!";
            exit();
        }
        if ($role !== 'Employee' && $role !== 'Admin') {
            echo "Invalid role selected!";
            exit();
        }
       

        $insertQuery = "INSERT INTO users (id, firstName, lastName, email, phoneNumber, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $success = $insertStmt->execute([$id, $firstName, $lastName, $email, $phoneNumber, $role, $password]);
        if (!$success) {
            $errorInfo = $insertStmt->errorInfo();
            echo "Database insert error: " . htmlspecialchars($errorInfo[2]);
            exit();
        }
        header('Location: Signin.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
        <div class="container" id="signup">
        <h1 class="form-title">Sign up</h1>
        <form method="post" action="Signup.php">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="" required>
                <label for="fname">First Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" placeholder="" required>
                <label for="lname">Last Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="" required>
                <label for="email">Email</label>
            </div>
                <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phonenumber" id="phonenumber" placeholder="" required>
                <label for="phonenumber">Phone Number</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="" required>
                <label for="password">Password</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user-tag"></i>
                <select name="role" id="role" required>
                    <option value="">Select Role</option>
                    <option value="Employee">Employee</option>
                    <option value="Admin">Admin</option>
                </select>
                <label for="role">Role</label>
            </div>
            <input type="submit" class="btn" value="Submit" name="register">
        </form>
        <p class="or">
            ---------------or----------------
             </p>
            <div class="links">
                <p>Already have a account ?</p>
                <button id="signInButton" onclick="window.location.href='Signin.php'">Sign In</button>
            </div>
    </div>
</body>
</html>
