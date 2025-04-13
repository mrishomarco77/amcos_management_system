<?php
include_once(__DIR__ . '/../admin/includes/config.php'); // Ensure the correct path to config.php

// Check if the database connection is successful
if (!isset($connection) || !$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch announcements from the database
$query = "SELECT a.title, a.description, a.created_at, 
                 IFNULL(u.username, 'Admin') AS posted_by 
          FROM announcements a 
          LEFT JOIN users u ON a.posted_by = u.user_id 
          ORDER BY a.created_at DESC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="apple-touch-icon" sizes="57x57" href="assets/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="assets/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="assets/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <title>AMCOS Management | Announcements</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>
    <!-- HEADER -->
    <header class="w-100 float-left header-con">
        <div class="wrapper">
            <nav class="navbar navbar-expand-lg navbar-light p-0">
                <a class="navbar-brand" href="index.php"><img src="assets/images/logo-icon.png" alt="logo-icon"></a>
                <button class="navbar-toggler collapsed" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    <span class="navbar-toggler-icon"></span>
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link p-0" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link p-0" href="about.php">About</a></li>
                        <li class="nav-item"><a class="nav-link p-0" href="contact.php">Contact</a></li>
                        <li class="nav-item active"><a class="nav-link p-0" href="announcements.php">Announcements</a></li>
                        <li class="nav-item"><a class="nav-link p-0" href="login.php">Login</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <!-- HEADER -->

    <!-- ANNOUNCEMENTS SECTION -->
    <section class="w-100 float-left announcements-con padding-top padding-bottom">
        <div class="container">
            <h2 class="text-center mb-4">Latest Announcements</h2>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="announcements-list">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="announcement-item mb-4 p-3 border rounded">
                            <h3 class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="announcement-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <small class="text-muted">
                                Posted by: <?php echo htmlspecialchars($row['posted_by']); ?> 
                                on <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No announcements available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>
    <!-- ANNOUNCEMENTS SECTION -->

    <!-- FOOTER -->
    <?php include_once(__DIR__ . '/test ya urban/footer.php'); ?>
    <!-- FOOTER -->

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>