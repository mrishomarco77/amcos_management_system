<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Delete farmer
if (isset($_POST['delete_farmer'])) {
    $farmer_id = $_POST['farmer_id'];
    
    // Check if farmer exists
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error'] = "Farmer not found";
        header("Location: farmers.php");
        exit();
    }
    
    // Delete farmer
    $stmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting farmer";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// Reset password
if (isset($_POST['reset_password'])) {
    $farmer_id = $_POST['farmer_id'];
    $default_password = "farmer123"; // Default password
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    // Check if farmer exists
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error'] = "Farmer not found";
        header("Location: farmers.php");
        exit();
    }
    
    // Update password
    $stmt = $conn->prepare("UPDATE farmers SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $farmer_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password reset successfully to: " . $default_password;
    } else {
        $_SESSION['error'] = "Error resetting password";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// If no valid action
header("Location: farmers.php");
exit();
?> 