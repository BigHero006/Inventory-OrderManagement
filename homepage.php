<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Home - YantraStud</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header class="home-header">
        <div class="container header-container">
            <div class="logo">YantraStud</div>
            <nav class="nav-links">
                <a href="home.php">Home</a>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php'; ?>">Dashboard</a>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'courses.php' : 'login.php'; ?>">My Courses</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <section class="home-section">
        <div class="home-content">
            <div class="info-box" style="max-width: 600px; padding: 40px 50px; background-color: #f9d6d5; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <h2 style="font-weight: 900; font-size: 2.5rem; margin-bottom: 10px;">WELCOME YantraStud</h2>
                <h3 style="font-weight: 700; font-size: 1.5rem; margin-bottom: 20px;">Designed for Excellence</h3>
                <ul style="font-weight: 600; font-size: 1.1rem; line-height: 1.6;">
                    <li>24/7 online access to resources and course materials</li>
                    <li>Interactive and engaging learning activities</li>
                    <li>Promote personalized and collaborative learning</li>
                </ul>
            </div>
        </div>
     
    </section>
    <footer class="home-footer">
        YantraStud &copy; <?php echo date("Y"); ?>
    </footer>
</body>
</html>
