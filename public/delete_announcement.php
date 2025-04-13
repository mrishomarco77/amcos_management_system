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
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing announcement ID']);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Delete announcement (only if user is the owner or admin)
    $stmt = $connection->prepare("DELETE FROM announcements WHERE id = ? AND (user_id = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))");
    $stmt->bind_param("iii", $id, $_SESSION['user_id'], $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Announcement not found or unauthorized']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting announcement']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 