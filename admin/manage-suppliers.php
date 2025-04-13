<?php
session_start();
include('includes/config.php');

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Handle Supplier Registration
if (isset($_POST['submit'])) {
    $supplier_name = mysqli_real_escape_string($con, trim($_POST['supplier_name']));
    $contact_info = mysqli_real_escape_string($con, trim($_POST['contact_info']));
    $phone_number = mysqli_real_escape_string($con, trim($_POST['phone_number']));

    // Check if Phone Number Already Exists
    $check_phone = mysqli_query($con, "SELECT phone_number FROM suppliers WHERE phone_number = '$phone_number'");
    if (mysqli_num_rows($check_phone) > 0) {
        echo "<script>alert('Phone number already exists!');</script>";
    } else {
        // Insert Supplier into Database
        $query = mysqli_query($con, "INSERT INTO suppliers (supplier_name, contact_info, phone_number) 
                                    VALUES ('$supplier_name', '$contact_info', '$phone_number')");

        if ($query) {
            echo "<script>alert('Supplier registered successfully.');</script>";
            echo "<script type='text/javascript'> document.location = 'manage-suppliers.php'; </script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Supplier</title>
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
                        <h1>Register Supplier</h1>
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
                                <h3 class="card-title">Supplier Details</h3>
                            </div>
                            <form method="post">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Supplier Name</label>
                                        <input type="text" class="form-control" name="supplier_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact Info</label>
                                        <input type="text" class="form-control" name="contact_info">
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" class="form-control" name="phone_number" required pattern="\d{10,15}" title="Enter a valid phone number (10-15 digits)">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="submit">Register</button>
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
