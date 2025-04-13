<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Add new farmer
if (isset($_POST['add_farmer'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $location = $_POST['location'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = 'active';

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: farmers.php");
        exit();
    }
    
    // Insert new farmer
    $stmt = $conn->prepare("INSERT INTO farmers (first_name, last_name, phone, email, location, password, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $last_name, $phone, $email, $location, $password, $status);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer added successfully";
    } else {
        $_SESSION['error'] = "Error adding farmer";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// Edit farmer
if (isset($_POST['edit_farmer'])) {
    $farmer_id = $_POST['farmer_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    // Check if email exists for other farmers
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: farmers.php");
        exit();
    }

    // Update farmer
    $stmt = $conn->prepare("UPDATE farmers SET first_name = ?, last_name = ?, phone = ?, email = ?, location = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $email, $location, $status, $farmer_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer updated successfully";
    } else {
        $_SESSION['error'] = "Error updating farmer";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// If no valid action
header("Location: farmers.php");
exit();
?> 