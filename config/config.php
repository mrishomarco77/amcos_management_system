<?php
// Database configuration
$host = 'localhost';
$dbname = 'amcos_management_system';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please check the configuration.");
}

// Session configuration
session_start();

// Define base URL
define('BASE_URL', 'http://localhost/amcos_management_system');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); 