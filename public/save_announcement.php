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
if (!isset($_POST['title']) || !isset($_POST['content']) || !isset($_POST['priority'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$title = trim($_POST['title']);
$content = trim($_POST['content']);
$priority = trim($_POST['priority']);
$editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

// Validate priority
if (!in_array($priority, ['normal', 'high', 'urgent'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid priority level']);
    exit;
}

try {
    if ($editId) {
        // Update existing announcement
        $stmt = $connection->prepare("UPDATE announcements SET title = ?, content = ?, priority = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $content, $priority, $editId, $_SESSION['user_id']);
    } else {
        // Create new announcement
        $stmt = $connection->prepare("INSERT INTO announcements (user_id, title, content, priority) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $title, $content, $priority);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Announcement saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving announcement']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 