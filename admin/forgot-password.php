<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    try {
        // Check if email exists
        $stmt = $connection->prepare("SELECT id, username, full_name FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token
            $stmt = $connection->prepare("
                INSERT INTO password_resets (user_id, token, expiry) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['id'], $token, $expiry]);
            
            // Send reset email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
            $to = $email;
            $subject = "Password Reset - AMCOS Management System";
            $message = "Dear " . htmlspecialchars($user['full_name']) . ",\n\n";
            $message .= "You have requested to reset your password. Click the link below to reset your password:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you did not request this password reset, please ignore this email.\n\n";
            $message .= "Best regards,\nAMCOS Management System";
            $headers = "From: noreply@amcos.com";

            if(mail($to, $subject, $message, $headers)) {
                $success = "Password reset instructions have been sent to your email.";
            } else {
                $error = "Failed to send reset email. Please try again later.";
            }
        } else {
            // Don't reveal if email exists or not for security
            $success = "If your email exists in our system, you will receive password reset instructions.";
        }
    } catch(PDOException $e) {
        $error = "System error. Please try again later.";
        error_log("Password reset error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-password-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
        }
        .card-header {
            background: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 30px 20px;
            border-bottom: none;
        }
        .card-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .btn-reset {
            background: #28a745;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            width: 100%;
            margin-bottom: 20px;
            color: white;
        }
        .btn-reset:hover {
            background: #218838;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
        .auth-links a:hover {
            color: #28a745;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-password-container">
            <div class="card">
                <div class="card-header">
                    <img src="../assets/images/logo.png" alt="AMCOS Logo" onerror="this.src='https://via.placeholder.com/80'">
                    <div class="logo-text">Forgot Password</div>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="Enter your email address" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-reset">
                            <i class="fas fa-paper-plane"></i> Send Reset Link
                        </button>
                        
                        <div class="auth-links">
                            <a href="index.php">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 