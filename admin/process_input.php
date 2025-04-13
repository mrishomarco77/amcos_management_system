<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Function to validate input data
function validateInputData($data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors[] = "Input name is required";
    }
    
    if (empty($data['category'])) {
        $errors[] = "Category is required";
    }
    
    if (!is_numeric($data['quantity_available']) || $data['quantity_available'] < 0) {
        $errors[] = "Quantity must be a positive number";
    }
    
    if (empty($data['unit'])) {
        $errors[] = "Unit is required";
    }
    
    if (!is_numeric($data['price_per_unit']) || $data['price_per_unit'] < 0) {
        $errors[] = "Price must be a positive number";
    }
    
    return $errors;
}

// Handle Add Input
if (isset($_POST['add_input'])) {
    $data = [
        'name' => trim($_POST['name']),
        'category' => trim($_POST['category']),
        'description' => trim($_POST['description']),
        'quantity_available' => floatval($_POST['quantity_available']),
        'unit' => trim($_POST['unit']),
        'price_per_unit' => floatval($_POST['price_per_unit']),
        'supplier' => trim($_POST['supplier']),
        'status' => 'available'
    ];
    
    // Validate input data
    $errors = validateInputData($data);
    
    if (empty($errors)) {
        try {
            $stmt = $con->prepare("INSERT INTO farming_inputs (name, category, description, quantity_available, unit, price_per_unit, supplier, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            $stmt->bind_param("sssdssss", 
                $data['name'],
                $data['category'],
                $data['description'],
                $data['quantity_available'],
                $data['unit'],
                $data['price_per_unit'],
                $data['supplier'],
                $data['status']
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Farming input added successfully";
            } else {
                $_SESSION['error'] = "Error adding farming input";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: farming_inputs.php');
    exit();
}

// Handle Edit Input
if (isset($_POST['edit_input'])) {
    $id = intval($_POST['input_id']);
    
    $data = [
        'name' => trim($_POST['name']),
        'category' => trim($_POST['category']),
        'description' => trim($_POST['description']),
        'quantity_available' => floatval($_POST['quantity_available']),
        'unit' => trim($_POST['unit']),
        'price_per_unit' => floatval($_POST['price_per_unit']),
        'supplier' => trim($_POST['supplier'])
    ];
    
    // Set status based on quantity
    $data['status'] = $data['quantity_available'] > 0 ? 
        ($data['quantity_available'] <= 10 ? 'low_stock' : 'available') : 
        'out_of_stock';
    
    // Validate input data
    $errors = validateInputData($data);
    
    if (empty($errors)) {
        try {
            $stmt = $con->prepare("UPDATE farming_inputs SET name=?, category=?, description=?, quantity_available=?, unit=?, price_per_unit=?, supplier=?, status=?, updated_at=NOW() WHERE id=?");
            
            $stmt->bind_param("sssdssssi", 
                $data['name'],
                $data['category'],
                $data['description'],
                $data['quantity_available'],
                $data['unit'],
                $data['price_per_unit'],
                $data['supplier'],
                $data['status'],
                $id
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Farming input updated successfully";
            } else {
                $_SESSION['error'] = "Error updating farming input";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: farming_inputs.php');
    exit();
}

// Handle Delete Input
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // First check if the input exists and is not referenced in any messages
        $check_stmt = $con->prepare("SELECT COUNT(*) as msg_count FROM farmer_messages WHERE input_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['msg_count'] > 0) {
            $_SESSION['error'] = "Cannot delete this input as it is referenced in messages";
        } else {
            $stmt = $con->prepare("DELETE FROM farming_inputs WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Farming input deleted successfully";
            } else {
                $_SESSION['error'] = "Error deleting farming input";
            }
            
            $stmt->close();
        }
        
        $check_stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    
    header('location: farming_inputs.php');
    exit();
}

// If no valid action is specified, redirect back to the inputs page
header('location: farming_inputs.php');
exit();
?> 