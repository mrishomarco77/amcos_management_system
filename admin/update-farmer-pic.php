<?php 
session_start();
// Database Connection
include('includes/config.php');

// Validating Session
if (strlen($_SESSION['aid']) == 0) { 
    header('location:index.php');
} else {
    // Code for updating farmer image
    if (isset($_POST['submit'])) {
        // Ensure farmer ID is set in the URL
        if (isset($_GET['fid'])) {
            $farmerid = intval($_GET['fid']);
        } else {
            echo "<script>alert('Farmer ID is missing.'); window.location = 'manage-farmers.php';</script>";
            exit();
        }

        // Getting Post Values  
        $currentpic = $_POST['currentprofilepic'];
        $oldprofilepic = "farmerspic/" . $currentpic;
        $profilepic = $_FILES["profilepic"]["name"];
        
        // Get the image extension
        $extension = substr($profilepic, strlen($profilepic) - 4, strlen($profilepic));
        
        // Allowed extensions
        $allowed_extensions = array(".jpg", "jpeg", ".png", ".gif");
        
        // Validation for allowed extensions
        if (!in_array($extension, $allowed_extensions)) {
            echo "<script>alert('Invalid format. Only jpg / jpeg / png / gif formats allowed');</script>";
        } else {
            // Rename the image file
            $newprofilepic = md5($profilepic) . time() . $extension;
            
            // Move image into directory
            move_uploaded_file($_FILES["profilepic"]["tmp_name"], "farmerspic/" . $newprofilepic);

            // Update database with new profile pic
            $query = mysqli_query($con, "UPDATE farmers SET profile_picture = '$newprofilepic' WHERE farmer_id = '$farmerid'");
            if ($query) {
                // Remove the old profile picture
                if ($oldprofilepic && file_exists($oldprofilepic)) {
                    unlink($oldprofilepic);  
                }
                echo "<script>alert('Farmer pic updated successfully.');</script>";
                echo "<script type='text/javascript'> document.location = 'manage-farmers.php'; </script>";
            } else {
                echo "<script>alert('Something went wrong. Please try again.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agricultural Management | Update Farmer Profile Pic</title>

    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
    <link rel="stylesheet" href="../plugins/bs-stepper/css/bs-stepper.min.css">
    <link rel="stylesheet" href="../plugins/dropzone/min/dropzone.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <?php include_once("includes/navbar.php"); ?>
    <!-- Main Sidebar Container -->
    <?php include_once("includes/sidebar.php"); ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Update Farmer Profile Pic</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Update Farmer Profile Pic</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Left column -->
                    <div class="col-md-8">
                        <!-- General form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Profile Picture</h3>
                            </div>
                            <!-- Form start -->
                            <form name="updateFarmerPic" method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                    <?php 
                                    // Get the farmer ID from the URL
                                    if (isset($_GET['fid'])) {
                                        $farmerid = intval($_GET['fid']);
                                    } else {
                                        echo "<script>alert('Farmer ID is missing.'); window.location = 'manage-farmers.php';</script>";
                                        exit();
                                    }

                                    // Get the current profile picture
                                    $query = mysqli_query($con, "SELECT profile_picture FROM farmers WHERE farmer_id = '$farmerid'");
                                    $result = mysqli_fetch_array($query);
                                    ?>
                                    <!-- Current Profile Pic -->
                                    <div class="form-group">
                                        <label for="currentPic">Current Profile Pic</label>
                                        <img src="farmerspic/<?php echo $result['profile_picture']; ?>" width="200">
                                    </div>

                                    <!-- Profile Pic -->
                                    <div class="form-group">
                                        <label for="newProfilePic">New Profile Pic 
                                            <span style="font-size:12px;color:red;">
                                                (Only jpg / jpeg / png / gif format allowed)
                                            </span>
                                        </label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="hidden" name="currentprofilepic" value="<?php echo $result['profile_picture']; ?>">
                                                <input type="file" class="custom-file-input" id="profilepic" name="profilepic" required="true">
                                                <label class="custom-file-label" for="profilepic">Choose file</label>
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Upload</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer" align="center">
                                        <button type="submit" class="btn btn-primary" name="submit" id="submit">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- End left column -->
                </div>
            </div>
        </section>
    </div>
    <!-- Footer -->
    <?php include_once('includes/footer.php'); ?>
</div>

<!-- Scripts -->
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
<script>
$(function () {
    bsCustomFileInput.init();
});
</script>
</body>
</html>
