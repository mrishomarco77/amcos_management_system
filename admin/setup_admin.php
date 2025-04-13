<?php
require_once '../includes/config.php';

try {
    // Create default admin user
    $username = 'admin@amcos.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $email = 'admin@amcos.com';
    $role = 'admin';
    $status = 'active';

    // Check if admin already exists
    $stmt = $connection->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if (!$stmt->fetch()) {
        // Create admin user
        $stmt = $connection->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $role, $status]);
        echo "Default admin user created successfully!<br>";
        echo "Username: admin@amcos.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 