<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Function to validate payment data
function validatePaymentData($data) {
    $errors = [];
    
    if ($data['payment_type'] == 'farmer' && empty($data['purchase_id'])) {
        $errors[] = "Purchase reference is required";
    }
    
    if ($data['payment_type'] == 'buyer' && empty($data['sale_id'])) {
        $errors[] = "Sale reference is required";
    }
    
    if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
        $errors[] = "Amount must be a positive number";
    }
    
    if (empty($data['payment_method'])) {
        $errors[] = "Payment method is required";
    }
    
    if (empty($data['payment_date'])) {
        $errors[] = "Payment date is required";
    }
    
    if (in_array($data['payment_method'], ['bank_transfer', 'mobile_money']) && empty($data['reference_number'])) {
        $errors[] = "Reference number is required for " . str_replace('_', ' ', $data['payment_method']);
    }
    
    return $errors;
}

// Handle Add Payment
if (isset($_POST['add_payment'])) {
    $data = [
        'payment_type' => $_POST['payment_type'],
        'purchase_id' => isset($_POST['purchase_id']) ? intval($_POST['purchase_id']) : null,
        'sale_id' => isset($_POST['sale_id']) ? intval($_POST['sale_id']) : null,
        'amount' => floatval($_POST['amount']),
        'payment_method' => trim($_POST['payment_method']),
        'payment_date' => $_POST['payment_date'],
        'reference_number' => trim($_POST['reference_number']),
        'notes' => trim($_POST['notes']),
        'status' => 'pending'
    ];
    
    // Validate payment data
    $errors = validatePaymentData($data);
    
    if (empty($errors)) {
        try {
            // Start transaction
            $con->begin_transaction();
            
            // Get related entity details
            if ($data['payment_type'] == 'farmer') {
                $check_stmt = $con->prepare("SELECT farmer_id, total_amount FROM purchases WHERE id = ?");
                $check_stmt->bind_param("i", $data['purchase_id']);
                $entity_id_field = 'farmer_id';
                $transaction_table = 'purchases';
            } else {
                $check_stmt = $con->prepare("SELECT buyer_id, total_amount FROM sales WHERE id = ?");
                $check_stmt->bind_param("i", $data['sale_id']);
                $entity_id_field = 'buyer_id';
                $transaction_table = 'sales';
            }
            
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $transaction = $result->fetch_assoc();
            
            if (!$transaction) {
                throw new Exception("Invalid transaction reference");
            }
            
            // Insert payment record
            $stmt = $con->prepare("INSERT INTO payments (payment_type, purchase_id, sale_id, farmer_id, buyer_id, amount, payment_method, payment_date, reference_number, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $farmer_id = $data['payment_type'] == 'farmer' ? $transaction['farmer_id'] : null;
            $buyer_id = $data['payment_type'] == 'buyer' ? $transaction['buyer_id'] : null;
            $purchase_id = $data['payment_type'] == 'farmer' ? $data['purchase_id'] : null;
            $sale_id = $data['payment_type'] == 'buyer' ? $data['sale_id'] : null;
            
            $stmt->bind_param("siiiidsssss", 
                $data['payment_type'],
                $purchase_id,
                $sale_id,
                $farmer_id,
                $buyer_id,
                $data['amount'],
                $data['payment_method'],
                $data['payment_date'],
                $data['reference_number'],
                $data['notes'],
                $data['status']
            );
            
            if ($stmt->execute()) {
                $payment_id = $stmt->insert_id;
                
                // Update transaction status if fully paid
                $sum_stmt = $con->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE " . ($data['payment_type'] == 'farmer' ? "purchase_id" : "sale_id") . " = ?");
                $sum_stmt->bind_param("i", $data['payment_type'] == 'farmer' ? $data['purchase_id'] : $data['sale_id']);
                $sum_stmt->execute();
                $sum_result = $sum_stmt->get_result();
                $payment_sum = $sum_result->fetch_assoc();
                
                if ($payment_sum['total_paid'] >= $transaction['total_amount']) {
                    $update_stmt = $con->prepare("UPDATE $transaction_table SET status = 'completed' WHERE id = ?");
                    $update_stmt->bind_param("i", $data['payment_type'] == 'farmer' ? $data['purchase_id'] : $data['sale_id']);
                    $update_stmt->execute();
                }
                
                // Commit transaction
                $con->commit();
                $_SESSION['success'] = "Payment recorded successfully";
            } else {
                throw new Exception("Failed to record payment");
            }
            
            $stmt->close();
            $check_stmt->close();
            if (isset($sum_stmt)) $sum_stmt->close();
            if (isset($update_stmt)) $update_stmt->close();
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $_SESSION['error'] = "Error recording payment: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: payments.php');
    exit();
}

// Handle Edit Payment
if (isset($_POST['edit_payment'])) {
    $id = intval($_POST['payment_id']);
    
    $data = [
        'payment_type' => $_POST['payment_type'],
        'purchase_id' => isset($_POST['purchase_id']) ? intval($_POST['purchase_id']) : null,
        'sale_id' => isset($_POST['sale_id']) ? intval($_POST['sale_id']) : null,
        'amount' => floatval($_POST['amount']),
        'payment_method' => trim($_POST['payment_method']),
        'payment_date' => $_POST['payment_date'],
        'reference_number' => trim($_POST['reference_number']),
        'notes' => trim($_POST['notes']),
        'status' => trim($_POST['status'])
    ];
    
    // Validate payment data
    $errors = validatePaymentData($data);
    
    if (empty($errors)) {
        try {
            // Start transaction
            $con->begin_transaction();
            
            // Update payment record
            $stmt = $con->prepare("UPDATE payments SET amount=?, payment_method=?, payment_date=?, reference_number=?, notes=?, status=?, updated_at=NOW() WHERE id=?");
            
            $stmt->bind_param("dsssssi", 
                $data['amount'],
                $data['payment_method'],
                $data['payment_date'],
                $data['reference_number'],
                $data['notes'],
                $data['status'],
                $id
            );
            
            if ($stmt->execute()) {
                // Update transaction status
                $transaction_id = $data['payment_type'] == 'farmer' ? $data['purchase_id'] : $data['sale_id'];
                $transaction_table = $data['payment_type'] == 'farmer' ? 'purchases' : 'sales';
                
                $sum_stmt = $con->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE " . ($data['payment_type'] == 'farmer' ? "purchase_id" : "sale_id") . " = ?");
                $sum_stmt->bind_param("i", $transaction_id);
                $sum_stmt->execute();
                $sum_result = $sum_stmt->get_result();
                $payment_sum = $sum_result->fetch_assoc();
                
                $total_stmt = $con->prepare("SELECT total_amount FROM $transaction_table WHERE id = ?");
                $total_stmt->bind_param("i", $transaction_id);
                $total_stmt->execute();
                $total_result = $total_stmt->get_result();
                $transaction = $total_result->fetch_assoc();
                
                $new_status = $payment_sum['total_paid'] >= $transaction['total_amount'] ? 'completed' : 'pending';
                
                $update_stmt = $con->prepare("UPDATE $transaction_table SET status = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_status, $transaction_id);
                $update_stmt->execute();
                
                // Commit transaction
                $con->commit();
                $_SESSION['success'] = "Payment updated successfully";
            } else {
                throw new Exception("Failed to update payment");
            }
            
            $stmt->close();
            $sum_stmt->close();
            $total_stmt->close();
            $update_stmt->close();
        } catch (Exception $e) {
            // Rollback transaction on error
            $con->rollback();
            $_SESSION['error'] = "Error updating payment: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    header('location: payments.php');
    exit();
}

// Handle Delete Payment
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    
    try {
        // Start transaction
        $con->begin_transaction();
        
        // Check if the payment can be deleted
        $check_stmt = $con->prepare("SELECT status, " . ($type == 'farmer' ? "purchase_id" : "sale_id") . " as transaction_id FROM payments WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $payment = $result->fetch_assoc();
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        if ($payment['status'] == 'completed') {
            throw new Exception("Cannot delete a completed payment");
        }
        
        // Delete payment record
        $stmt = $con->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Update transaction status
            $transaction_table = $type == 'farmer' ? 'purchases' : 'sales';
            $update_stmt = $con->prepare("UPDATE $transaction_table SET status = 'pending' WHERE id = ?");
            $update_stmt->bind_param("i", $payment['transaction_id']);
            $update_stmt->execute();
            
            // Commit transaction
            $con->commit();
            $_SESSION['success'] = "Payment deleted successfully";
        } else {
            throw new Exception("Failed to delete payment");
        }
        
        $stmt->close();
        $check_stmt->close();
        $update_stmt->close();
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        $_SESSION['error'] = "Error deleting payment: " . $e->getMessage();
    }
    
    header('location: payments.php');
    exit();
}

// If no valid action is specified, redirect back to the payments page
header('location: payments.php');
exit();
?> 