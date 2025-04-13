<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Initialize login check
checkLogin();

// Set session timeout to 30 minutes
$session_timeout = 1800; // 30 minutes in seconds

// Check if last activity was set
if (isset($_SESSION['last_activity'])) {
    // Calculate time difference
    $inactive_time = time() - $_SESSION['last_activity'];
    
    // Check if user has been inactive longer than the timeout period
    if ($inactive_time >= $session_timeout) {
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
?> 