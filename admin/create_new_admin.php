<?php
require_once __DIR__ . '/../includes/config.php';

// New admin credentials
$username = "admin2023";
$email = "admin2023@amcos.com";
$password = "Admin@2023"; // This will be the initial password
$role = "admin";
$status = "active";

try {
    // Check if admin already exists
    $stmt = $connection->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if($stmt->rowCount() > 0) {
        die("An admin with this username or email already exists.");
    }
    
    // Create new admin account
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $connection->prepare("
        INSERT INTO users (username, email, password, role, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([$username, $email, $hashed_password, $role, $status]);
    
    echo "New admin account created successfully!\n";
    echo "Username: " . $username . "\n";
    echo "Email: " . $email . "\n";
    echo "Password: " . $password . "\n";
    echo "\nPlease save these credentials and delete this file after use for security reasons.";
    
} catch(PDOException $e) {
    die("Error creating admin account: " . $e->getMessage());
}
?> 