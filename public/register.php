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
        'first_name' => mysqli_real_escape_string($con, trim($_POST['first_name'])),
        'middle_name' => mysqli_real_escape_string($con, trim($_POST['middle_name'])),
        'last_name' => mysqli_real_escape_string($con, trim($_POST['last_name'])),
        'username' => mysqli_real_escape_string($con, trim($_POST['username'])),
        'email' => mysqli_real_escape_string($con, trim($_POST['email'])),
        'phone' => mysqli_real_escape_string($con, trim($_POST['phone'])),
        'location' => mysqli_real_escape_string($con, trim($_POST['location'])),
        'address' => mysqli_real_escape_string($con, trim($_POST['address'])),
        'registered_agency' => mysqli_real_escape_string($con, trim($_POST['registered_agency']))
    ];
    
    $password = mysqli_real_escape_string($con, trim($_POST['password']));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active';

    // Start transaction
    $con->begin_transaction();

    try {
        // Check if username exists
        $stmt = $con->prepare("SELECT id, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $formData['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Username already exists. Please choose a different username.");
        }

        // Check if email exists
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Email address already registered. Please use a different email.");
        }

        // Check if phone exists
        $stmt = $con->prepare("SELECT id FROM farmers WHERE phone = ?");
        $stmt->bind_param("s", $formData['phone']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Phone number already registered. Please use a different number.");
        }

        // Insert into users table
        $stmt = $con->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'farmer', ?)");
        $stmt->bind_param("ssss", $formData['username'], $hashed_password, $formData['email'], $status);
        $stmt->execute();
        $user_id = $con->insert_id;

        // Insert into farmers table
        $stmt = $con->prepare("INSERT INTO farmers (user_id, first_name, middle_name, last_name, phone, location, address, registered_agency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", 
            $user_id, 
            $formData['first_name'], 
            $formData['middle_name'], 
            $formData['last_name'], 
            $formData['phone'], 
            $formData['location'], 
            $formData['address'], 
            $formData['registered_agency']
        );
        $stmt->execute();

        // Commit transaction
        $con->commit();
        $_SESSION['success'] = "Registration successful! You can now login with your username and password.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
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
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .register-box {
            margin-top: 20px;
        }
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
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
    </style>
</head>
<body class="hold-transition">
<div class="container">
    <div class="register-box mx-auto" style="max-width: 700px;">
        <div class="card">
            <div class="card-header text-center">
                <h1 class="h1">AMCOS</h1>
                <h4>Farmer Registration</h4>
            </div>
            <div class="card-body">
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }
                ?>
                <form method="post" onsubmit="return validateForm()">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" required value="<?php echo htmlspecialchars($formData['first_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($formData['middle_name']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" required value="<?php echo htmlspecialchars($formData['last_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
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
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" name="password" required minlength="6" id="password">
                                <small class="form-text text-muted">Password must be at least 6 characters long</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required minlength="6" id="confirm_password">
                                <small class="form-text text-muted" id="password_match"></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" class="form-control" name="phone" required pattern="\d{10,15}" 
                                       title="Enter a valid phone number (10-15 digits)"
                                       value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?php echo htmlspecialchars($formData['email']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" class="form-control" name="location" required
                               value="<?php echo htmlspecialchars($formData['location']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" name="address" required
                               value="<?php echo htmlspecialchars($formData['address']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Registered Agency</label>
                        <input type="text" class="form-control" name="registered_agency" required
                               value="<?php echo htmlspecialchars($formData['registered_agency']); ?>">
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <a href="login.php" class="text-center">I already have an account</a>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block" name="register">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
function validateForm() {
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("confirm_password").value;
    
    if (password != confirm_password) {
        document.getElementById("password_match").innerHTML = "Passwords do not match!";
        document.getElementById("password_match").style.color = "red";
        return false;
    }
    return true;
}

// Real-time password match checking
document.getElementById("confirm_password").onkeyup = function() {
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("confirm_password").value;
    
    if (password === confirm_password) {
        document.getElementById("password_match").innerHTML = "Passwords match!";
        document.getElementById("password_match").style.color = "green";
    } else {
        document.getElementById("password_match").innerHTML = "Passwords do not match!";
        document.getElementById("password_match").style.color = "red";
    }
}
</script>
</body>
</html>
