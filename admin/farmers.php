<?php
session_start();
include('includes/config.php');
include('includes/db_connect.php');

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// First, let's check if the required columns exist
$table_altered = true;
try {
    // Check if any required column is missing
    $required_columns = ['username', 'password', 'email', 'middle_name', 'location', 'registered_agency', 'status'];
    $missing_columns = [];
    
    foreach ($required_columns as $column) {
        $check_column = $con->query("SHOW COLUMNS FROM farmers LIKE '$column'");
        if ($check_column->num_rows == 0) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        // Some columns are missing, alter table
        $alter_queries = file_get_contents('database/alter_farmers_table.sql');
        
        // Split queries and execute them separately
        $queries = array_filter(explode(';', $alter_queries));
        
        foreach ($queries as $query) {
            if (trim($query) != '') {
                if (!$con->query(trim($query))) {
                    throw new Exception("Error executing query: " . $con->error);
                }
            }
        }
        
        $_SESSION['success'] = "Database structure updated successfully.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Database update failed: " . $e->getMessage();
    $table_altered = false;
}

// Handle Farmer Registration
if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($con, trim($_POST['first_name']));
    $middle_name = mysqli_real_escape_string($con, trim($_POST['middle_name']));
    $last_name = mysqli_real_escape_string($con, trim($_POST['last_name']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $location = mysqli_real_escape_string($con, trim($_POST['location']));
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $registered_agency = mysqli_real_escape_string($con, trim($_POST['registered_agency']));
    $password = mysqli_real_escape_string($con, trim($_POST['password']));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active';

    // Start transaction
    $con->begin_transaction();

    try {
        // First, check if username exists in users table
        $stmt = $con->prepare("SELECT id, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['role'] === 'admin') {
                throw new Exception("This username belongs to an administrator. Please choose a different username.");
            } else {
                throw new Exception("This username is already taken. Please choose a different username.");
            }
        }

        // Check if email exists in users table
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("This email address is already registered. Please use a different email.");
        }

        // Check if phone exists in farmers table
        $stmt = $con->prepare("SELECT id FROM farmers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("This phone number is already registered. Please use a different number.");
        }

        // Insert into users table first
        $stmt = $con->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'farmer', ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $status);
        $stmt->execute();
        $user_id = $con->insert_id;

        // Now insert into farmers table
        $stmt = $con->prepare("INSERT INTO farmers (user_id, first_name, middle_name, last_name, phone, location, address, registered_agency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $first_name, $middle_name, $last_name, $phone, $location, $address, $registered_agency);
        $stmt->execute();

        // If we get here, commit the transaction
        $con->commit();
        $_SESSION['success'] = "Farmer registered successfully! Please use your username and password to login.";
        header("Location: farmers.php");
        exit();

    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle farmer deletion
if (isset($_POST['delete_farmer'])) {
    $farmer_id = $_POST['farmer_id'];
    $stmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting farmer";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $farmer_id = $_POST['farmer_id'];
    $new_password = password_hash("farmer123", PASSWORD_DEFAULT); // Default password: farmer123
    
    $stmt = $conn->prepare("UPDATE farmers SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $farmer_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password reset successfully to: farmer123";
    } else {
        $_SESSION['error'] = "Error resetting password";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Farmer</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <style>
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
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include_once("includes/navbar.php"); ?>
    <?php include_once("includes/sidebar.php"); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Register Farmer</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }
                if (!$table_altered) {
                    echo '<div class="alert alert-warning">System upgrade in progress. Please wait a few minutes before registering new farmers.</div>';
                }
                ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Farmer Details</h3>
                            </div>
                            <form method="post" onsubmit="return validateForm()">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" class="form-control username-input" name="username" required 
                                               minlength="4" pattern="[a-zA-Z][a-zA-Z0-9_]*" 
                                               title="Username must start with a letter and can only contain letters, numbers and underscore">
                                        <div class="username-requirements">
                                            <p class="mb-1"><small>Username requirements:</small></p>
                                            <ul class="pl-3 mb-0">
                                                <li><small>Must start with a letter</small></li>
                                                <li><small>Can contain letters, numbers, and underscore</small></li>
                                                <li><small>Must be at least 4 characters long</small></li>
                                                <li><small>Cannot be the same as admin usernames</small></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password" required minlength="6" id="password">
                                        <small class="form-text text-muted">Password must be at least 6 characters long</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required minlength="6" id="confirm_password">
                                        <small class="form-text text-muted" id="password_match"></small>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" required pattern="\d{10,15}" title="Enter a valid phone number (10-15 digits)">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" class="form-control" name="location" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="address" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Registered Agency</label>
                                        <input type="text" class="form-control" name="registered_agency" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="submit" <?php echo !$table_altered ? 'disabled' : ''; ?>>Register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include_once('includes/footer.php'); ?>
</div>
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
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
