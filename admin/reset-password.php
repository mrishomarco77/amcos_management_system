<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    header("Location: forgot-password.php");
    exit();
}

// Check if token is valid and not expired
try {
    $stmt = $connection->prepare("
        SELECT pr.*, u.email, u.full_name 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = ? AND pr.used = 0 AND pr.expiry > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $error = "Invalid or expired reset link. Please request a new one.";
    }
} catch(PDOException $e) {
    $error = "System error. Please try again later.";
    error_log("Password reset error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $reset['user_id']]);
            
            // Mark reset token as used
            $stmt = $connection->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = "Password has been reset successfully. You can now login with your new password.";
        } catch(PDOException $e) {
            $error = "Failed to reset password. Please try again.";
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-password-container {
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
        <div class="reset-password-container">
            <div class="card">
                <div class="card-header">
                    <img src="../assets/images/logo.png" alt="AMCOS Logo" onerror="this.src='https://via.placeholder.com/80'">
                    <div class="logo-text">Reset Password</div>
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
                        <div class="text-center">
                            <a href="index.php" class="btn btn-reset">Go to Login</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" placeholder="Enter new password" required minlength="8">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-reset">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                            
                            <div class="auth-links">
                                <a href="index.php">Back to Login</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 