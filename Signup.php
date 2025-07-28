<?php
require 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phonenumber'];
    // Automatically set role to Employee for all signups
    $role = 'Employee';
    $password = md5($_POST['password']);
    $id = random_int(0, 999);

    $checkEmail = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($checkEmail);
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "Email Address Already Exists !!";
    } else {
        $insertQuery = "INSERT INTO users (firstName, lastName, email, phone, address, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $address = ''; // Default empty address since form doesn't collect this
        $success = $insertStmt->execute([$firstName, $lastName, $email, $phoneNumber, $address, $role, $password]);
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
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="auth-style.css">
</head>
<body>
        <div class="container" id="signup">
        <h1 class="form-title">Sign Up</h1>
        <form method="post" action="Signup.php">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="First Name" required>
                <label for="fName">First Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" placeholder="Last Name" required>
                <label for="lName">Last Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
                <div class="input-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phonenumber" id="phonenumber" placeholder="Phone Number" required>
                <label for="phonenumber">Phone Number</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <input type="submit" class="btn" value="Submit" name="register">
        </form>
        <p class="or">
            ---------------or----------------
        </p>
        <div class="links">
            <p>Already have a account ?</p>
            <a href="Signin.php" style="color:#0369a1;text-decoration:underline;font-weight:600;">Sign In</a>
        </div>
    </div>
</body>
</html>
