<?php
session_start();

// Simple debugging to track form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_entry = date('Y-m-d H:i:s') . " - Form submitted with data: " . json_encode($_POST) . "\n";
    file_put_contents('form_submissions.log', $log_entry, FILE_APPEND);
    
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 10px; border-radius: 5px;'>";
    echo "Form submitted at: " . date('Y-m-d H:i:s') . "<br>";
    echo "Data received: " . json_encode($_POST);
    echo "</div>";
}

// Generate form token
if (!isset($_SESSION['debug_token'])) {
    $_SESSION['debug_token'] = bin2hex(random_bytes(16));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Submission Debug</title>
</head>
<body>
    <h1>Debug Form Submission</h1>
    
    <form method="POST" action="" id="debugForm">
        <input type="hidden" name="token" value="<?php echo $_SESSION['debug_token']; ?>">
        <input type="text" name="test_field" placeholder="Test field" required>
        <button type="submit" id="submitBtn">Submit</button>
    </form>

    <script>
        let submissionCount = 0;
        
        document.getElementById('debugForm').addEventListener('submit', function(e) {
            submissionCount++;
            console.log('Form submission #' + submissionCount);
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            // Re-enable after 3 seconds for testing
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit';
            }, 3000);
        });
    </script>
</body>
</html>
