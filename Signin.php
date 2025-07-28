<?php
session_start();
require 'dbconnect.php';

$errorMsg = '';
if (isset($_POST['email']) && $_POST['email'] != null) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND password = ?');
        $stmt->execute([$email, $password]);
        $value = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($value) {
            $_SESSION['email'] = $value['email'];
            $_SESSION['firstName'] = $value['firstName'];
            $_SESSION['lastName'] = $value['lastName'];
            $_SESSION['user_id'] = $value['id']; // Changed from 'id' to 'user_id' for SessionManager compatibility
            $_SESSION['phone'] = $value['phone'] ?? '';
            $_SESSION['role'] = $value['role'];
            $_SESSION['profile_photo'] = $value['profile_photo'] ?? '';

            if ($value['role'] === 'Admin') {
                header('Location: admindashboard.php');
            } elseif ($value['role'] === 'Employee') {
                header('Location: employeedashboard.php');
            } else {
                // Fallback for any legacy users
                header('Location: admindashboard.php');
            }
            exit();
        } else {
            $errorMsg = 'Invalid email or password!';
        }
    } catch (PDOException $e) {
        $errorMsg = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="auth-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <?php if (!empty($errorMsg)): ?>
    <div class="error-box" id="loginErrorBox">
        <div><?php echo $errorMsg; ?></div>
        <button onclick="document.getElementById('loginErrorBox').style.display='none'">OK</button>
    </div>
    <?php endif; ?>
    <div class="container" id="signIn">
        <h1 class="form-title">SignIn</h1>
        <form method="post" action="Signin.php">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <p class="recover">
                <a href="#">Recover Password</a>
            </p>
            <input type="submit" class="btn" value="Sign In" name="signIn">
        </form>
        <p class="or">
            ---------------or----------------
        </p>
        <div class="links">
            <p>Don't have a account yet ?</p>
            <a href="Signup.php" style="color:#0369a1;text-decoration:underline;font-weight:600;">Sign Up</a>
        </div>
    </div>
</body>
</html>
