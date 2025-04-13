<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('../admin/includes/db_connect.php');

// Initialize variables for form values
$formData = [
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'location' => '',
    'address' => '',
    'registered_agency' => ''
];

// Handle form submission
if (isset($_POST['register'])) {
    // Get form data
    $formData = [
        'first_name' => trim($_POST['first_name']),
        'middle_name' => trim($_POST['middle_name']),
        'last_name' => trim($_POST['last_name']),
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'location' => trim($_POST['location']),
        'address' => trim($_POST['address']),
        'registered_agency' => trim($_POST['registered_agency'])
    ];
    
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active';

    try {
        // Begin transaction
        $connection->beginTransaction();

        // Check if username exists
        $stmt = $connection->prepare("SELECT id, role FROM users WHERE username = ?");
        $stmt->execute([$formData['username']]);
        if ($stmt->fetch()) {
            throw new Exception("Username already exists. Please choose a different username.");
        }

        // Check if email exists
        $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$formData['email']]);
        if ($stmt->fetch()) {
            throw new Exception("Email address already registered. Please use a different email.");
        }

        // Check if phone exists
        $stmt = $connection->prepare("SELECT id FROM farmers WHERE phone = ?");
        $stmt->execute([$formData['phone']]);
        if ($stmt->fetch()) {
            throw new Exception("Phone number already registered. Please use a different number.");
        }

        // Insert into users table
        $stmt = $connection->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'farmer', ?)");
        $stmt->execute([$formData['username'], $hashed_password, $formData['email'], $status]);
        $user_id = $connection->lastInsertId();

        // Insert into farmers table
        $stmt = $connection->prepare("INSERT INTO farmers (user_id, first_name, middle_name, last_name, phone, location, address, registered_agency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $formData['first_name'],
            $formData['middle_name'],
            $formData['last_name'],
            $formData['phone'],
            $formData['location'],
            $formData['address'],
            $formData['registered_agency']
        ]);

        // Commit transaction
        $connection->commit();
        $_SESSION['success'] = "Registration successful! You can now login with your username and password.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $connection->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Registration - AMCOS</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .register-box {
            margin: 20px auto;
            max-width: 700px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: none;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .username-requirements {
            display: none;
            margin-top: 5px;
            padding: 10px;
            border-left: 3px solid #007bff;
            background-color: #f8f9fa;
        }
        .username-input:focus + .username-requirements {
            display: block;
        }
        .form-control {
            height: 45px;
            border-radius: 5px;
        }
        .btn-register {
            height: 45px;
            font-size: 16px;
        }
    </style>
</head>
<body class="hold-transition">
<div class="container">
    <div class="register-box">
        <div class="card-header text-center">
            <h1 class="h1">AMCOS</h1>
            <h4>Farmer Registration</h4>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" onsubmit="return validateForm()">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="first_name" required 
                                   value="<?php echo htmlspecialchars($formData['first_name']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" 
                                   value="<?php echo htmlspecialchars($formData['middle_name']); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="last_name" required 
                                   value="<?php echo htmlspecialchars($formData['last_name']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Username</label>
                            <input type="text" class="form-control username-input" name="username" required 
                                   minlength="4" pattern="[a-zA-Z][a-zA-Z0-9_]*" 
                                   title="Username must start with a letter and can only contain letters, numbers and underscore"
                                   value="<?php echo htmlspecialchars($formData['username']); ?>">
                            <div class="username-requirements">
                                <p class="mb-1"><small>Username requirements:</small></p>
                                <ul class="pl-3 mb-0">
                                    <li><small>Must start with a letter</small></li>
                                    <li><small>Can contain letters, numbers, and underscore</small></li>
                                    <li><small>Must be at least 4 characters long</small></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required minlength="6" id="password">
                            <small class="form-text text-muted">Password must be at least 6 characters long</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6" id="confirm_password">
                            <small class="form-text text-muted" id="password_match"></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Phone Number</label>
                            <input type="tel" class="form-control" name="phone" required pattern="\d{10,15}" 
                                   title="Enter a valid phone number (10-15 digits)"
                                   value="<?php echo htmlspecialchars($formData['phone']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo htmlspecialchars($formData['email']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label>Location</label>
                    <input type="text" class="form-control" name="location" required
                           value="<?php echo htmlspecialchars($formData['location']); ?>">
                </div>

                <div class="form-group mb-3">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" required
                           value="<?php echo htmlspecialchars($formData['address']); ?>">
                </div>

                <div class="form-group mb-3">
                    <label>Registered Agency</label>
                    <input type="text" class="form-control" name="registered_agency" required
                           value="<?php echo htmlspecialchars($formData['registered_agency']); ?>">
                </div>

                <div class="row mt-4">
                    <div class="col-8">
                        <a href="login.php" class="text-center">I already have an account</a>
                    </div>
                    <div class="col-4">
                        <button type="submit" name="register" class="btn btn-success w-100 btn-register">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validateForm() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    
    if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return false;
    }
    return true;
}

// Real-time password match checking
document.getElementById('confirm_password').addEventListener('input', function() {
    var password = document.getElementById('password').value;
    var confirmPassword = this.value;
    var matchText = document.getElementById('password_match');
    
    if (password === confirmPassword) {
        matchText.style.color = 'green';
        matchText.textContent = 'Passwords match!';
    } else {
        matchText.style.color = 'red';
        matchText.textContent = 'Passwords do not match!';
    }
});
</script>

</body>
</html>
