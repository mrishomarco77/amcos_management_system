<?php
require_once '../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to request inputs']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$input_id = $data['input_id'] ?? null;

if (!$input_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid input ID']);
    exit;
}

// Get farmer ID from session
$user_id = $_SESSION['user_id'];
$query = "SELECT farmer_id FROM farmers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();

if (!$farmer) {
    echo json_encode(['success' => false, 'message' => 'Farmer profile not found']);
    exit;
}

$farmer_id = $farmer['farmer_id'];
$status = 'pending';
$request_date = date('Y-m-d H:i:s');
$season_year = date('Y');

// Insert the request into farmer_inputs table
$query = "INSERT INTO farmer_inputs (farmer_id, input_id, status, request_date, season_year) 
          VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iisss", $farmer_id, $input_id, $status, $request_date, $season_year);

if ($stmt->execute()) {
    // Create a notification for the admin
    $notification_title = "New Input Request";
    $notification_message = "A new input request has been submitted by Farmer ID: " . $farmer_id;
    
    $query = "INSERT INTO notifications (user_id, title, message, created_at) 
              SELECT user_id, ?, ?, NOW() FROM users WHERE role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $notification_title, $notification_message);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Input request submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting request']);
} 