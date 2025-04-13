<?php
include_once(__DIR__ . '/../admin/includes/config.php'); // Ensure the correct path to config.php

// Check if the database connection is successful
if (!isset($connection) || !$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch farmers from the database
$query = "SELECT farmer_id, first_name, middle_name, last_name, address, phone_number, registered_agency, profile_picture, nida, farm_size, crop_type, registration_date 
          FROM farmers";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AMCOS Management | Farmers</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
    <!-- HEADER -->
    <?php include('test ya urban/header.php'); ?>
    <!-- HEADER -->

    <!-- FARMERS SECTION -->
    <section class="w-100 float-left farmers-con padding-top padding-bottom">
        <div class="container">
            <h2 class="text-center mb-4">Registered Farmers</h2>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Last Name</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Registered Agency</th>
                                <th>NIDA</th>
                                <th>Farm Size</th>
                                <th>Crop Type</th>
                                <th>Registration Date</th>
                                <th>Profile Picture</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['farmer_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['registered_agency']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nida']); ?></td>
                                    <td><?php echo htmlspecialchars($row['farm_size'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['crop_type'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($row['registration_date'])); ?></td>
                                    <td>
                                        <?php if ($row['profile_picture']): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" width="50">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No farmers registered yet.</p>
            <?php endif; ?>
        </div>
    </section>
    <!-- FARMERS SECTION -->

    <!-- FOOTER -->
    <?php include('test ya urban/footer.php'); ?>
    <!-- FOOTER -->

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
