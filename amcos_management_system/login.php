<?php
session_start();
include('includes/config.php'); // Path to database connection

// Redirect if already logged in
if (isset($_SESSION['uid'])) {
    if ($_SESSION['urole'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Login logic
if (isset($_POST['login'])) {
    $uname = $_POST['username'];
    $password = $_POST['inputpwd'];

    // Use prepared statement to fetch user data
    $stmt = $con->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password and redirect based on role
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['uid'] = $user['user_id'];
        $_SESSION['uname'] = $user['username'];
        $_SESSION['urole'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = "Jina la Mtumiaji au Nenosiri si Sahihi!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMCOS Management System | Ingia</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background: #f2f6fb;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            padding: 25px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        .header span {
            font-size: 14px;
            color: #666;
        }
        .form-control {
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 10px;
        }
        .btn-login {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
        .btn-login:hover {
            background: #218838;
        }
        .alert-danger {
            font-size: 14px;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="header">
            <h1>AMCOS Management System</h1>
            <span>Ingia kwenye Akaunti Yako</span>
        </div>

        <!-- Error Message -->
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <!-- Login Form -->
        <form method="post">
            <input type="text" class="form-control" name="username" placeholder="Jina la Mtumiaji" required>
            <input type="password" class="form-control" name="inputpwd" placeholder="Nenosiri" required>
            <button type="submit" name="login" class="btn-login">Ingia</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>