<?php
$con = mysqli_connect("localhost", "root", "", "amcos_management_system");

if (!$con) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
echo "Database Connected Successfully!";
?>
