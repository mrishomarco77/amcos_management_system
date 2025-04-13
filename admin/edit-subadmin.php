<?php 
session_start();
include('includes/config.php');

// Redirect if not logged in
if (!isset($_SESSION['aid']) || strlen($_SESSION['aid']) == 0) { 
    header('location:index.php');
    exit();
}

// Validate and get Sub-Admin ID
if (!isset($_GET['said']) || !is_numeric($_GET['said'])) {
    echo "<script>alert('Invalid request.'); document.location = 'manage-subadmins.php';</script>";
    exit();
}

$said = intval($_GET['said']); // Ensure ID is an integer

// Fetch Sub-Admin Data
$query = mysqli_query($con, "SELECT * FROM tbladmin WHERE UserType=0 AND ID=$said");
if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Invalid Sub-Admin ID or record not found.'); document.location = 'manage-subadmins.php';</script>";
    exit();
}

$result = mysqli_fetch_array($query);

// Update Sub-Admin Details
if (isset($_POST['update'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fullname']);
    $email = mysqli_real_escape_string($con, $_POST['emailid']);
    $mobileno = mysqli_real_escape_string($con, $_POST['mobilenumber']);

    $updateQuery = mysqli_query($con, "UPDATE tbladmin SET AdminName='$fname', MobileNumber='$mobileno', Email='$email' WHERE UserType=0 AND ID=$said");
    
    if ($updateQuery) {
        echo "<script>alert('Sub-admin details updated successfully.'); document.location = 'manage-subadmins.php';</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Sub-Admin</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
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
                        <h1>Edit Sub-Admin Details</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Edit Sub-Admin</li>
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
                                <h3 class="card-title">Update Information</h3>
                            </div>
                            <form method="post">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Username (used for login)</label>
                                        <input type="text" name="sadminusername" class="form-control" value="<?php echo htmlspecialchars($result['AdminUserName']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($result['AdminName']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" name="emailid" value="<?php echo htmlspecialchars($result['Email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile Number</label>
                                        <input type="text" class="form-control" name="mobilenumber" pattern="[0-9]{10}" title="10 numeric characters only" value="<?php echo htmlspecialchars($result['MobileNumber']); ?>" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="update">Update</button>
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
</body>
</html>
