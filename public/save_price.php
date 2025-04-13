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
if (!isset($_POST['crop_id']) || !isset($_POST['price']) || !isset($_POST['market_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$cropId = (int)$_POST['crop_id'];
$price = (float)$_POST['price'];
$marketName = trim($_POST['market_name']);
$editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

try {
    if ($editId) {
        // Update existing price
        $stmt = $connection->prepare("UPDATE market_prices SET crop_id = ?, price = ?, market_name = ? WHERE id = ?");
        $stmt->bind_param("idsi", $cropId, $price, $marketName, $editId);
    } else {
        // Create new price
        $stmt = $connection->prepare("INSERT INTO market_prices (crop_id, price, market_name) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $cropId, $price, $marketName);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Market price saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving market price']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 