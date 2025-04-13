<?php
require_once '../includes/error_reporting.php';
session_start();
require_once '../includes/config.php';
require_once '../admin/includes/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'farmer/dashboard.php';
    header("Location: " . $redirect_url);
    exit();
}

$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Tafadhali jaza taarifa zote / Please fill in all fields";
    } else {
        try {
            // Optimize query to select only necessary fields
            $stmt = $connection->prepare("
                SELECT u.id, u.email, u.password, u.role, u.status,
                       f.first_name, f.last_name
                FROM users u
                LEFT JOIN farmers f ON f.user_id = u.id
                WHERE u.email = ?
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = "Akaunti yako haijawashwa. Tafadhali wasiliana na msimamizi / Your account is not active. Please contact administrator";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'] ?? 'User';
                    $_SESSION['last_name'] = $user['last_name'] ?? '';

                    $redirect_url = $user['role'] === 'admin' ? 'admin/dashboard.php' : 'farmer/dashboard.php';
                    header("Location: " . $redirect_url);
                    exit();
                }
            } else {
                $error = "Barua pepe au neno la siri si sahihi / Invalid email or password";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Kuna hitilafu. Tafadhali jaribu tena / System error. Please try again";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMCOS Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/img/amcos-logo.png" alt="AMCOS Logo" loading="lazy">
            <h1>AMCOS Management System</h1>
            <p>Login to Your Account</p>
        </div>

        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email address" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group mb-3">
                <div class="input-group">
                    <input type="password" class="form-control password-input" name="password" 
                           id="password" placeholder="Password" required>
                    <span class="input-group-text">
                        <i class="fas fa-eye-slash" id="togglePassword"></i>
                    </span>
                </div>
            </div>

            <div class="text-end mb-3">
                <a href="forgot_password.php" class="forgot-link">
                    <i class="fas fa-key"></i> Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn btn-login btn-success w-100">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="register-section text-center mt-4">
                <p class="mb-0">Don't have an account?</p>
                <a href="register.php" class="btn btn-outline-primary mt-2">
                    <i class="fas fa-user-plus"></i> Register Now
                </a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('togglePassword').addEventListener('click', function() {
                const password = document.getElementById('password');
                const icon = this;
                
                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    password.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>

