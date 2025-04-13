<?php
include_once(__DIR__ . '/admin/includes/config.php'); // Ensure the correct path to config.php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $registered_agency = trim($_POST['registered_agency']);
    $phone_number = trim($_POST['phone_number']);
    $national_id = trim($_POST['national_id']);
    $nida = trim($_POST['nida']);
    $farm_size = trim($_POST['farm_size']);
    $crop_type = trim($_POST['crop_type']);
    $profile_picture = null;

    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $file_name;
        } else {
            echo "<script>alert('Failed to upload profile picture.');</script>";
        }
    }

    // Insert farmer details into the database
    $query = "INSERT INTO farmers (first_name, middle_name, last_name, address, registered_agency, phone_number, national_id, nida, farm_size, crop_type, profile_picture) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param(
        "sssssssssss",
        $first_name,
        $middle_name,
        $last_name,
        $address,
        $registered_agency,
        $phone_number,
        $national_id,
        $nida,
        $farm_size,
        $crop_type,
        $profile_picture
    );

    if ($stmt->execute()) {
        echo "<script>alert('Farmer registered successfully!'); window.location.href='manage-farmers.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Farmer</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Register Farmer</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>
            <div class="form-group">
                <label for="registered_agency">Registered Agency</label>
                <input type="text" class="form-control" id="registered_agency" name="registered_agency">
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number">
            </div>
            <div class="form-group">
                <label for="national_id">National ID</label>
                <input type="text" class="form-control" id="national_id" name="national_id">
            </div>
            <div class="form-group">
                <label for="nida">NIDA</label>
                <input type="text" class="form-control" id="nida" name="nida">
            </div>
            <div class="form-group">
                <label for="farm_size">Farm Size (in acres)</label>
                <input type="number" step="0.01" class="form-control" id="farm_size" name="farm_size">
            </div>
            <div class="form-group">
                <label for="crop_type">Crop Type</label>
                <input type="text" class="form-control" id="crop_type" name="crop_type">
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
            </div>
            <button type="submit" class="btn btn-primary">Register Farmer</button>
        </form>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
