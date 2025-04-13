<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Ensure user is logged in
requireLogin();

header('Content-Type: application/json');

try {
    if (!isset($_FILES['profile_picture'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['profile_picture'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, JPEG and PNG are allowed.');
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Update database
    $user_id = $_SESSION['user_id'];
    
    // First check if the farmer record exists
    $stmt = $connection->prepare("SELECT id FROM farmers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $farmer = $stmt->fetch();

    if ($farmer) {
        // Update existing farmer record
        $stmt = $connection->prepare("UPDATE farmers SET profile_picture = ? WHERE user_id = ?");
    } else {
        // Create new farmer record
        $stmt = $connection->prepare("INSERT INTO farmers (user_id, profile_picture) VALUES (?, ?)");
    }

    $stmt->execute([$filepath, $user_id]);

    // Delete old profile picture if it exists
    if (isset($_POST['old_picture']) && file_exists($_POST['old_picture'])) {
        unlink($_POST['old_picture']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'file_path' => $filepath
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 