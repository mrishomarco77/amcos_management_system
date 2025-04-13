<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['price_id'])) {
    echo json_encode(['success' => false, 'message' => 'Price ID is required']);
    exit;
}

$priceId = (int)$_POST['price_id'];

try {
    $stmt = $connection->prepare("DELETE FROM market_prices WHERE id = ?");
    $stmt->bind_param("i", $priceId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Market price deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Market price not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting market price']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 