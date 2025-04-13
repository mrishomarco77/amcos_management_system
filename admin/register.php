<?php
session_start();
include('includes/config.php');

$msg = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username already exists
        $check_username = mysqli_query($con, "SELECT username FROM admin WHERE username='$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username already exists";
        } else {
            // Check if email already exists
            $check_email = mysqli_query($con, "SELECT email FROM admin WHERE email='$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin
                $query = "INSERT INTO admin (fullname, username, email, password, role) VALUES (?, ?, ?, ?, 'admin')";
                $stmt = mysqli_prepare($con, $query);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $fullname, $username, $email, $hashed_password);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $msg = "Admin registered successfully. You can now login.";
                        // Redirect to login page after 3 seconds
                        echo "<script>
                            alert('Registration successful! Redirecting to login page...');
                            window.location.href = 'index.php';
                        </script>";
                        exit();
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error = "System error. Please try again later.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
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
        .registration-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .form-title {
            color: #28a745;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
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
        .btn-register {
            background-color: #28a745;
            border-color: #28a745;
            padding: 0.75rem;
            font-weight: bold;
        }
        .btn-register:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .alert {
            margin-bottom: 1rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-card">
            <h2 class="form-title">Admin Registration</h2>
            
            <?php if($error) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>
            
            <?php if($msg) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <form method="POST" action="" onsubmit="return validateForm()">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Full Name" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email Address" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                </div>

                <button type="submit" class="btn btn-register w-100 text-white">Register</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            
            if (password !== confirm_password) {
                alert("Passwords do not match!");
                return false;
            }
            
            if (password.length < 6) {
                alert("Password must be at least 6 characters long!");
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>