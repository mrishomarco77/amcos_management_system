<?php
$connection = mysqli_connect("localhost", "root", "", "amcos_management_system");

if ($connection) {
    echo "Database connected successfully!";
} else {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
