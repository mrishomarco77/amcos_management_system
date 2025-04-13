<?php
require_once '../includes/config.php';
session_start();

$error = '';
$success = '';

// Check if token is provided
if (!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Verify token and check if it's expired
$stmt = $connection->prepare("
    SELECT id, email 
    FROM users 
    WHERE reset_token = ? 
    AND reset_token_expires > NOW()
");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "Kitufe cha kubadilisha neno la siri kimepitwa na muda au si sahihi / Invalid or expired reset token";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate password
    if (empty($password) || empty($confirm_password)) {
        $error = "Tafadhali jaza neno la siri / Please enter password";
    } elseif ($password !== $confirm_password) {
        $error = "Neno la siri halioani / Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Neno la siri liwe na herufi 6 au zaidi / Password must be at least 6 characters";
    } else {
        try {
            // Update password and clear reset token
            $stmt = $connection->prepare("
                UPDATE users 
                SET password = ?, 
                    reset_token = NULL, 
                    reset_token_expires = NULL 
                WHERE id = ?
            ");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$hashed_password, $user['id']]);
            
            $success = "Neno la siri limebadilishwa. Tafadhali ingia / Password has been reset. Please login";
            
            // Redirect to login page after 3 seconds
            header("refresh:3;url=login.php");
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = "Kuna hitilafu. Tafadhali jaribu tena / An error occurred. Please try again";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-password-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        .reset-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-password-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        .btn-reset {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
        }
        .password-field {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-header">
            <img src="assets/img/amcos-logo.png" alt="AMCOS Logo">
            <h1 class="h3 mb-3">
                <i class="fas fa-key"></i> Reset Password
            </h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php elseif (empty($error)): ?>
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="password" class="form-label">
                        Neno la siri jipya / New Password
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="6">
                        <i class="fas fa-eye password-toggle" 
                           onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">
                        Thibitisha neno la siri / Confirm Password
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               minlength="6">
                        <i class="fas fa-eye password-toggle" 
                           onclick="togglePassword('confirm_password')"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-reset">
                    <i class="fas fa-save"></i> Save New Password
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 