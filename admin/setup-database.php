<?php
require_once('../includes/config.php');

$sql_files = [
    // Admin table
    "CREATE TABLE IF NOT EXISTS `admin` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `fullname` varchar(255) NOT NULL,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `bio` text DEFAULT NULL,
        `profile_picture` varchar(255) DEFAULT NULL,
        `role` enum('admin','subadmin') NOT NULL DEFAULT 'admin',
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Teachers table
    "CREATE TABLE IF NOT EXISTS `teachers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `fullname` varchar(255) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `subject` varchar(100) DEFAULT NULL,
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Classes table
    "CREATE TABLE IF NOT EXISTS `classes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `class_name` varchar(100) NOT NULL,
        `class_teacher_id` int(11) DEFAULT NULL,
        `capacity` int(11) DEFAULT 30,
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`class_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Enrollments table
    "CREATE TABLE IF NOT EXISTS `enrollments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `student_name` varchar(255) NOT NULL,
        `parent_name` varchar(255) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `class_id` int(11) NOT NULL,
        `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
        `enrollment_date` date NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Visitors table
    "CREATE TABLE IF NOT EXISTS `visitors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `fullname` varchar(255) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `purpose` text NOT NULL,
        `status` enum('new','visited','not_visited') NOT NULL DEFAULT 'new',
        `visit_date` date NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

$success = true;
$messages = [];

foreach ($sql_files as $sql) {
    try {
        if (mysqli_query($con, $sql)) {
            $messages[] = "Table created successfully";
        } else {
            $success = false;
            $messages[] = "Error creating table: " . mysqli_error($con);
        }
    } catch (Exception $e) {
        $success = false;
        $messages[] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - AMCOS Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Database Setup Results</h3>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h4>Setup Completed Successfully!</h4>
                        <p>All database tables have been created.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h4>Setup Encountered Some Issues</h4>
                        <p>Please check the error messages below:</p>
                    </div>
                <?php endif; ?>

                <div class="mt-3">
                    <h5>Setup Messages:</h5>
                    <ul>
                        <?php foreach ($messages as $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Go to Login Page</a>
                    <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 