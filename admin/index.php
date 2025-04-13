<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

// Check if already logged in
if(isset($_SESSION['aid'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$username = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // Check user credentials
        $query = "SELECT id, username, password, role, status FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($row = mysqli_fetch_assoc($result)) {
            if($row['status'] == 'inactive') {
                $error = "Your account is inactive. Please contact the administrator.";
            } elseif(password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['aid'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AMCOS Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #28a745;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        .logo-text {
            color: #28a745;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        .form-control {
            border-radius: 5px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-login {
            background-color: #28a745;
            border-color: #28a745;
            padding: 0.75rem;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .auth-links {
            text-align: center;
            margin-top: 1rem;
        }
        .auth-links a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .auth-links a:hover {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="../assets/images/logo.png" alt="AMCOS Logo" onerror="this.src='https://via.placeholder.com/150'">
            <div class="logo-text">AMCOS Admin Portal</div>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-login text-white w-100">Login</button>
        </form>

        <div class="auth-links">
            <a href="register.php">Register</a> | <a href="forgot-password.php">Forgot Password?</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>