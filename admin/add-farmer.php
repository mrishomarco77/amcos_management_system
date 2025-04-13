<?php 
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {

if (isset($_POST['submit'])) {
    $fname = $_POST['first_name'];
    $mname = $_POST['middle_name'];
    $lname = $_POST['last_name'];
    $nida = $_POST['nida'];
    $phone = $_POST['phone_number'];
    $farm_size = $_POST['farm_size'];
    $crop_type = $_POST['crop_type'];
    $registration_date = date("Y-m-d");

    $profile_pic = $_FILES["profile_picture"]["name"];
    $extension = pathinfo($profile_pic, PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $extension;
    $target_dir = "../farmerspic/";

    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $query = mysqli_query($con, "INSERT INTO farmers (first_name, middle_name, last_name, nida, phone_number, farm_size, crop_type, profile_picture, registration_date) 
        VALUES ('$fname', '$mname', '$lname', '$nida', '$phone', '$farm_size', '$crop_type', '$new_filename', '$registration_date')");

        if ($query) {
            echo "<script>alert('Farmer added successfully.');</script>";
            echo "<script>window.location.href='manage-farmers.php';</script>";
        } else {
            echo "<script>alert('Error adding farmer. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Failed to upload profile picture. Please check file permissions.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>AMCOS | Add Farmer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include_once("includes/navbar.php"); ?>
    <?php include_once("includes/sidebar.php"); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>Add New Farmer</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header"><h3 class="card-title">Farmer Information</h3></div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>NIDA Number</label>
                                <input type="text" name="nida" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Farm Size (in acres)</label>
                                <input type="number" name="farm_size" step="0.01" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Crop Type</label>
                                <input type="text" name="crop_type" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control" accept="image/*" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Add Farmer</button>
                            <a href="manage-farmers.php" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include_once("includes/footer.php"); ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
<?php } ?>
