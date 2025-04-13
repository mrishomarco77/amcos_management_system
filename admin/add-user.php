<?php 
session_start();
include('includes/config.php');

// Redirect if not logged in
if (!isset($_SESSION['aid']) || strlen($_SESSION['aid']) == 0) { 
    header('location:index.php');
    exit();
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $phone_number = mysqli_real_escape_string($con, $_POST['phone_number']);
    $status = "active";
    $image = "";

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false && in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                
            } else {
                echo "<script>alert('Error uploading image.');</script>";
            }
        } else {
            echo "<script>alert('Invalid image file. Only JPG, JPEG, PNG allowed.');</script>";
        }
    }

    // Check if username or email already exists
    $checkQuery = mysqli_query($con, "SELECT * FROM users WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($checkQuery) > 0) {
        echo "<script>alert('Username or Email already exists! Try another.');</script>";
    } else {
        $query = mysqli_query($con, "INSERT INTO users (username, email, password, role, phone_number, status, image) 
            VALUES ('$username', '$email', '$password', '$role', '$phone_number', '$status', '$image')");
        
        if ($query) {
            echo "<script>alert('User added successfully!'); document.location = 'manage-users.php';</script>";
        } else {
            echo "<script>alert('Error adding user. Try again!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add User</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
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
                        <h1>Add User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Add User</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">User Information</h3>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select class="form-control select2" name="role" required>
                                            <option value="admin">Admin</option>
                                            <option value="user">User</option>
                                            <option value="agent">Agent</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" class="form-control" name="phone_number" pattern="[0-9]{10}" title="10 numeric characters only">
                                    </div>
                                    <div class="form-group">
                                        <label>Profile Picture</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="submit">Add User</button>
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
<script src="../plugins/select2/js/select2.full.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
<script>
$(function () {
    $('.select2').select2();
});
</script>
</body>
</html>
