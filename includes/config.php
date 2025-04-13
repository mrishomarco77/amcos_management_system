<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only set session parameters if session hasn't started
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration
    ini_set('session.gc_maxlifetime', 3600);
    session_set_cookie_params(3600);
    session_start();
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'amcos_management_system');

// Establish database connection
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($con, "utf8");

// Security headers
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

// Function to clean input data
function clean_input($data) {
    global $con;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($con, $data);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['aid']) && !empty($_SESSION['aid']);
}

// Function to check admin role
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to get user display name
function get_user_name() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}

// Set timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Define base URL
define('BASE_URL', 'http://localhost/amcos_management_system');

// Set maximum execution time
ini_set('max_execution_time', 30);
?>
