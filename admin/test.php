<?php 
session_start();
include('includes/config.php');

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    // Update Farmer Details
    if (isset($_POST['submit'])) {
        $farmer_id = intval($_GET['fid']);
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $registered_agency = $_POST['registered_agency'];
        $phone_number = $_POST['phone_number'];

        $query = mysqli_query($con, "UPDATE farmers 
            SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name', 
                address='$address', registered_agency='$registered_agency', phone_number='$phone_number' 
            WHERE farmer_id='$farmer_id'");

        if ($query) {
            echo "<script>alert('Farmer details updated successfully.');</script>";
            echo "<script type='text/javascript'> document.location = 'manage-farmers.php'; </script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.');</script>";
        }
    }

    // Fetch Farmer Data
    $farmer_id = intval($_GET['fid']);
    $query = mysqli_query($con, "SELECT * FROM farmers WHERE farmer_id='$farmer_id'");
    $result = mysqli_fetch_array($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Farmer</title>
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
                        <h1>Update Farmer</h1>
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
                                <h3 class="card-title">Farmer Details</h3>
                            </div>
                            <form method="post">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlentities($result['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" value="<?php echo htmlentities($result['middle_name']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlentities($result['last_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="address" value="<?php echo htmlentities($result['address']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Registered Agency</label>
                                        <input type="text" class="form-control" name="registered_agency" value="<?php echo htmlentities($result['registered_agency']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" class="form-control" name="phone_number" value="<?php echo htmlentities($result['phone_number']); ?>" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" name="submit">Update</button>
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
<?php } ?>
