<?php 
session_start();
// Database Connection
include('includes/config.php');

// Validating Session
if(strlen($_SESSION['aid']) == 0) { 
    header('location:index.php');
} else {
    // Code for Add New Farmer
    if(isset($_POST['submit'])) {
        // Getting Post Values  
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $registered_agency = $_POST['registered_agency'];
        $phone_number = $_POST['phone_number'];
        $national_id = $_POST['national_id'];
        $nida = $_POST['nida'];
        $farm_size = $_POST['farm_size'];
        $crop_type = $_POST['crop_type'];
        $added_by = $_SESSION['uname'];

        // Handle Profile Picture Upload
        $profile_pic = $_FILES["profile_pic"]["name"];
        // Get the image extension
        $extension = substr($profile_pic, strlen($profile_pic) - 4, strlen($profile_pic));
        // Allowed extensions
        $allowed_extensions = array(".jpg", ".jpeg", ".png", ".gif");

        // Validation for allowed extensions
        if(!in_array($extension, $allowed_extensions)) {
            echo "<script>alert('Invalid format. Only jpg / jpeg / png / gif format allowed');</script>";
        } else {
            // Rename the image file
            $new_profile_pic = md5($profile_pic) . time() . $extension;
            // Code for move image into directory
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "farmerspic/" . $new_profile_pic);

            // Insert into the database
            $query = mysqli_query($con, "INSERT INTO farmers(first_name, middle_name, last_name, address, registered_agency, phone_number, national_id, nida, farm_size, crop_type, registration_date, farmerPic, AddedBy) 
                VALUES('$first_name', '$middle_name', '$last_name', '$address', '$registered_agency', '$phone_number', '$national_id', '$nida', '$farm_size', '$crop_type', NOW(), '$new_profile_pic', '$added_by')");

            if($query) {
                echo "<script>alert('Farmer added successfully.');</script>";
                echo "<script type='text/javascript'> document.location = 'add-farmer.php'; </script>";
            } else {
                echo "<script>alert('Something went wrong. Please try again.');</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AMCOS Management System | Add Farmer</title>

  <!-- Required CSS and JS -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="../plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <link rel="stylesheet" href="../plugins/bs-stepper/css/bs-stepper.min.css">
  <link rel="stylesheet" href="../plugins/dropzone/min/dropzone.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <?php include_once("includes/navbar.php");?>
  <!-- Sidebar -->
  <?php include_once("includes/sidebar.php");?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add Farmer</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
              <li class="breadcrumb-item active">Add Farmer</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- Left column -->
          <div class="col-md-8">
            <!-- Form Elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Farmer Info</h3>
              </div>
              <!-- Form start -->
              <form name="addfarmer" method="post" enctype="multipart/form-data">
                <div class="card-body">
                  <!-- Full Name -->
                  <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter Farmer's First Name" required>
                  </div>

                  <!-- Middle Name -->
                  <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Enter Farmer's Middle Name">
                  </div>

                  <!-- Last Name -->
                  <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Farmer's Last Name" required>
                  </div>

                  <!-- Address -->
                  <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter Farmer's Address">
                  </div>

                  <!-- Registered Agency -->
                  <div class="form-group">
                    <label for="registered_agency">Registered Agency</label>
                    <input type="text" class="form-control" id="registered_agency" name="registered_agency" placeholder="Enter Registered Agency">
                  </div>

                  <!-- Phone Number -->
                  <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter Phone Number" pattern="[0-9]{10}" title="10 numeric characters only" required>
                  </div>

                  <!-- National ID -->
                  <div class="form-group">
                    <label for="national_id">National ID</label>
                    <input type="text" class="form-control" id="national_id" name="national_id" placeholder="Enter National ID">
                  </div>

                  <!-- NIDA -->
                  <div class="form-group">
                    <label for="nida">NIDA</label>
                    <input type="text" class="form-control" id="nida" name="nida" placeholder="Enter NIDA" required>
                  </div>

                  <!-- Farm Size -->
                  <div class="form-group">
                    <label for="farm_size">Farm Size (in hectares)</label>
                    <input type="number" class="form-control" id="farm_size" name="farm_size" step="0.01" placeholder="Enter Farm Size" required>
                  </div>

                  <!-- Crop Type -->
                  <div class="form-group">
                    <label for="crop_type">Crop Type</label>
                    <input type="text" class="form-control" id="crop_type" name="crop_type" placeholder="Enter Crop Type" required>
                  </div>

                  <!-- Profile Picture -->
                  <div class="form-group">
                    <label for="profile_pic">Profile Picture <span style="font-size:12px;color:red;">(Only jpg / jpeg / png /gif format allowed)</span></label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="profile_pic" name="profile_pic" required>
                        <label class="custom-file-label" for="profile_pic">Choose file</label>
                      </div>
                    </div>
                  </div>

                  <div class="card-footer">
                    <button type="submit" class="btn btn-primary" name="submit" id="submit">Submit</button>
                  </div>
                </div>
                <!-- /.card-body -->
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- Footer -->
  <?php include_once('includes/footer.php');?>
</div>

<!-- JS scripts -->
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
<?php } ?>
