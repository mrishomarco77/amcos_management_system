<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Function to validate purchase data
function validatePurchaseData($data) {
    $errors = [];
    
    if (empty($data['farmer_id'])) {
        $errors[] = "Farmer selection is required";
    }
    
    if (!is_numeric($data['weight_kg']) || $data['weight_kg'] <= 0) {
        $errors[] = "Weight must be a positive number";
    }
    
    if (!is_numeric($data['price_per_kg']) || $data['price_per_kg'] <= 0) {
        $errors[] = "Price per kg must be a positive number";
    }
    
    if (empty($data['purchase_date'])) {
        $errors[] = "Purchase date is required";
    }
    
    return $errors;
}

// Handle Add Purchase
if (isset($_POST['add_purchase'])) {
    $data = [
        'farmer_id' => intval($_POST['farmer_id']),
        'weight_kg' => floatval($_POST['weight_kg']),
        'price_per_kg' => floatval($_POST['price_per_kg']),
        'purchase_date' => $_POST['purchase_date'],
        'notes' => trim($_POST['notes']),
        'status' => 'pending'
    ];
    
    // Calculate total amount
    $data['total_amount'] = $data['weight_kg'] * $data['price_per_kg'];
    
    // Validate purchase data
    $errors = validatePurchaseData($data);
    
    if (empty($errors)) {
        try {
            // Start transaction
            $con->begin_transaction();
            
            // Insert purchase record
            $stmt = $con->prepare("INSERT INTO purchases (farmer_id, weight_kg, price_per_kg, total_amount, purchase_date, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->bind_param("idddsss", 
                $data['farmer_id'],
                $data['weight_kg'],
                $data['price_per_kg'],
                $data['total_amount'],
                $data['purchase_date'],
                $data['notes'],
                $data['status']
            );
            
            if ($stmt->execute()) {
                $purchase_id = $stmt->insert_id;
                
                // Create a payment record if needed
                // You can add payment processing logic here
                
                // Commit transaction
                $con->commit();
                $_SESSION['success'] = "Purchase recorded successfully";
            } else {
                throw new Exception("Failed to record purchase");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $_SESSION['error'] = "Error recording purchase: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: purchases.php');
    exit();
}

// Handle Edit Purchase
if (isset($_POST['edit_purchase'])) {
    $id = intval($_POST['purchase_id']);
    
    $data = [
        'farmer_id' => intval($_POST['farmer_id']),
        'weight_kg' => floatval($_POST['weight_kg']),
        'price_per_kg' => floatval($_POST['price_per_kg']),
        'purchase_date' => $_POST['purchase_date'],
        'notes' => trim($_POST['notes']),
        'status' => trim($_POST['status'])
    ];
    
    // Calculate total amount
    $data['total_amount'] = $data['weight_kg'] * $data['price_per_kg'];
    
    // Validate purchase data
    $errors = validatePurchaseData($data);
    
    if (empty($errors)) {
        try {
            // Start transaction
            $con->begin_transaction();
            
            // Update purchase record
            $stmt = $con->prepare("UPDATE purchases SET farmer_id=?, weight_kg=?, price_per_kg=?, total_amount=?, purchase_date=?, notes=?, status=?, updated_at=NOW() WHERE id=?");
            
            $stmt->bind_param("idddsssi", 
                $data['farmer_id'],
                $data['weight_kg'],
                $data['price_per_kg'],
                $data['total_amount'],
                $data['purchase_date'],
                $data['notes'],
                $data['status'],
                $id
            );
            
            if ($stmt->execute()) {
                // Update related records if needed
                // You can add payment update logic here
                
                // Commit transaction
                $con->commit();
                $_SESSION['success'] = "Purchase updated successfully";
            } else {
                throw new Exception("Failed to update purchase");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $_SESSION['error'] = "Error updating purchase: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: purchases.php');
    exit();
}

// Handle Delete Purchase
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Start transaction
        $con->begin_transaction();
        
        // Check if the purchase can be deleted
        $check_stmt = $con->prepare("SELECT status FROM purchases WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $purchase = $result->fetch_assoc();
        
        if (!$purchase) {
            throw new Exception("Purchase not found");
        }
        
        if ($purchase['status'] == 'completed') {
            throw new Exception("Cannot delete a completed purchase");
        }
        
        // Delete purchase record
        $stmt = $con->prepare("DELETE FROM purchases WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete related records if needed
            // You can add payment deletion logic here
            
            // Commit transaction
            $con->commit();
            $_SESSION['success'] = "Purchase deleted successfully";
        } else {
            throw new Exception("Failed to delete purchase");
        }
        
        $stmt->close();
        $check_stmt->close();
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        $_SESSION['error'] = "Error deleting purchase: " . $e->getMessage();
    }
    
    header('location: purchases.php');
    exit();
}

// If no valid action is specified, redirect back to the purchases page
header('location: purchases.php');
exit();
?> 