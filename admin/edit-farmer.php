<?php session_start();
// Database Connection
include('includes/config.php');
//Validating Session
if(strlen($_SESSION['aid'])==0)
  { 
header('location:index.php');
}
else{
// Code for updating farmer details
if(isset($_POST['submit'])){
  //Getting Post Values  
  $fname=$_POST['first_name'];
  $mname=$_POST['middle_name'];
  $lname=$_POST['last_name'];
  $phone=$_POST['phone_number'];
  $nida=$_POST['nida'];
  $farm_size=$_POST['farm_size'];
  $crop_type=$_POST['crop_type'];
  $farmerid=intval($_GET['fid']);

  $query=mysqli_query($con,"UPDATE farmers SET first_name='$fname', middle_name='$mname', last_name='$lname', phone_number='$phone', nida='$nida', farm_size='$farm_size', crop_type='$crop_type' WHERE farmer_id='$farmerid'");
  
  if($query){
    echo "<script>alert('Farmer details updated successfully.');</script>";
    echo "<script type='text/javascript'> document.location = 'manage-farmers.php'; </script>";
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
  <title>AMCOS | Edit Farmer</title>
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <?php include_once("includes/navbar.php");?>
  <?php include_once("includes/sidebar.php");?>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Farmer</h1>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-8">
            <div class="card card-primary">
              <?php
              $farmerid=intval($_GET['fid']);
              $query=mysqli_query($con,"SELECT * FROM farmers WHERE farmer_id='$farmerid'");
              while($result=mysqli_fetch_array($query)){ ?>
              <div class="card-header">
                <h3 class="card-title">Personal Info</h3>
              </div>
              <form method="post">
                <div class="card-body">
                  <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo $result['first_name']?>" required>
                  </div>
                  <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" value="<?php echo $result['middle_name']?>">
                  </div>
                  <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo $result['last_name']?>" required>
                  </div>
                  <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" name="phone_number" value="<?php echo $result['phone_number']?>" pattern="[0-9]{10}" required>
                  </div>
                  <div class="form-group">
                    <label>NIDA</label>
                    <input type="text" class="form-control" name="nida" value="<?php echo $result['nida']?>" required>
                  </div>
                  <div class="form-group">
                    <label>Farm Size (in acres)</label>
                    <input type="text" class="form-control" name="farm_size" value="<?php echo $result['farm_size']?>">
                  </div>
                  <div class="form-group">
                    <label>Crop Type</label>
                    <input type="text" class="form-control" name="crop_type" value="<?php echo $result['crop_type']?>">
                  </div>
                  <div class="form-group">
                    <label>Profile Picture</label>
                    <img src="farmerpics/<?php echo $result['profile_picture']?>" width="120">
                    <a href="update-farmer-pic.php?fid=<?php echo $result['farmer_id'];?>">Update Profile Pic</a>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary" name="submit">Update</button>
                </div>
              </form>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php include_once('includes/footer.php');?>
</div>
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
</body>
</html>
<?php } ?>
