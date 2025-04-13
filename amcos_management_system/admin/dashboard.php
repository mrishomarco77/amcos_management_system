<?php
session_start();
include('includes/config.php');

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['aid']) || strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

/**
 * Retrieves the total count of records from a specified table with an optional condition
 * @param mysqli $con Database connection
 * @param string $table Table name
 * @param string|null $condition_field Column name for the condition
 * @param string|null $condition_value Value to match in the condition
 * @return int Total count of records
 */
function getTotalCount($con, $table, $condition_field = null, $condition_value = null) {
    if ($condition_field && $condition_value) {
        $query = "SELECT COUNT(*) AS count FROM `$table` WHERE `$condition_field` = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $con->error);
        }
        $stmt->bind_param("s", $condition_value);
    } else {
        $query = "SELECT COUNT(*) AS count FROM `$table`";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $con->error);
        }
    }
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        die("Get result failed: " . $stmt->error);
    }
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

/**
 * Fetches the 5 most recent feedback entries
 * @param mysqli $con Database connection
 * @return array Array of feedback records
 */
function getRecentFeedbacks($con) {
    $query = "SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $feedbacks = [];
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
    $stmt->close();
    return $feedbacks;
}

/**
 * Fetches the 5 latest announcements
 * @param mysqli $con Database connection
 * @return array Array of announcement records
 */
function getLatestAnnouncements($con) {
    $query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();
    return $announcements;
}

/**
 * Retrieves all records from a specified table
 * @param mysqli $con Database connection
 * @param string $table Table name
 * @return array Array of records
 */
function getAllRecords($con, $table) {
    $query = "SELECT * FROM `$table`";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
    return $records;
}

// Fetch dashboard statistics
$registered_farmers_count = getTotalCount($con, 'farmers');
$registered_suppliers = getTotalCount($con, 'suppliers');
// Removed reference to sub_admins table
$total_orders = getTotalCount($con, 'orders');
$feedback_count = getTotalCount($con, 'feedback');
$new_orders = getTotalCount($con, 'orders', 'delivery_status', 'pending');
$recent_feedbacks = getRecentFeedbacks($con);
$latest_announcements = getLatestAnnouncements($con);

// Fetch data for farmers and sub admins
$registered_farmers = getAllRecords($con, 'farmers');
$registered_sub_admins = getAllRecords($con, 'tbladmin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMCOS Management System | Dashboard</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Include navigation bar and sidebar -->
    <?php include_once('includes/navbar.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Statistics Boxes -->
                <div class="row">
                    <?php 
                    $stats = [
                        ['bg' => 'primary', 'icon' => 'user', 'count' => $registered_farmers_count, 'text' => 'Registered Farmers', 'link' => 'manage-farmers.php'],
                        ['bg' => 'success', 'icon' => 'truck', 'count' => $registered_suppliers, 'text' => 'Registered Suppliers', 'link' => 'manage-suppliers.php'],
                        // Removed sub_admins box
                        ['bg' => 'warning', 'icon' => 'shopping-cart', 'count' => $total_orders, 'text' => 'Total Orders', 'link' => 'manage-orders.php'],
                        ['bg' => 'danger', 'icon' => 'comments', 'count' => $feedback_count, 'text' => 'Feedback Messages', 'link' => 'manage-feedback.php'],
                        ['bg' => 'info', 'icon' => 'cart-plus', 'count' => $new_orders, 'text' => 'New Orders Placed', 'link' => 'new-orders.php'],
                    ];
                    foreach ($stats as $stat) { ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-<?= $stat['bg'] ?>">
                                <div class="inner">
                                    <h3><?= $stat['count'] ?></h3>
                                    <p><?= $stat['text'] ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-<?= $stat['icon'] ?>"></i>
                                </div>
                                <a href="<?= $stat['link'] ?>" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <!-- Recent Feedbacks -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h4>Recent Feedbacks</h4>
                        <div class="list-group">
                            <?php foreach ($recent_feedbacks as $feedback) { ?>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <?= htmlspecialchars($feedback['feedback_text']) ?>
                                    <small class="text-muted d-block"><?= $feedback['created_at'] ?></small>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <!-- Latest Announcements -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h4>Latest Announcements</h4>
                        <div class="list-group">
                            <?php foreach ($latest_announcements as $announcement) { ?>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <?= htmlspecialchars($announcement['announcement_text']) ?>
                                    <small class="text-muted d-block"><?= $announcement['created_at'] ?></small>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Registered Farmers -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h4>Registered Farmers</h4>
                        <a href="export.php?type=farmers&format=excel" class="btn btn-success btn-sm">Export to Excel</a>
                        <a href="export.php?type=farmers&format=pdf" class="btn btn-danger btn-sm">Export to PDF</a>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Phone Number</th>
                                        <th>Crop Type</th>
                                        <th>Farm Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registered_farmers as $index => $farmer): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($farmer['first_name']) ?></td>
                                            <td><?= htmlspecialchars($farmer['last_name']) ?></td>
                                            <td><?= htmlspecialchars($farmer['phone_number']) ?></td>
                                            <td><?= htmlspecialchars($farmer['crop_type']) ?></td>
                                            <td><?= htmlspecialchars($farmer['farm_size']) ?> acres</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Registered Sub Admins -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h4>Registered Sub Admins</h4>
                        <a href="export.php?type=sub_admins&format=excel" class="btn btn-success btn-sm">Export to Excel</a>
                        <a href="export.php?type=sub_admins&format=pdf" class="btn btn-danger btn-sm">Export to PDF</a>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Admin Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Mobile Number</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registered_sub_admins as $index => $admin): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($admin['AdminName']) ?></td>
                                            <td><?= htmlspecialchars($admin['AdminUserName']) ?></td>
                                            <td><?= htmlspecialchars($admin['Email']) ?></td>
                                            <td><?= htmlspecialchars($admin['MobileNumber']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Include footer -->
    <?php include_once('includes/footer.php'); ?>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>
</body>
</html>