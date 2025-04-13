<?php
require_once '../config/config.php';
require_once '../config/check_admin_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$farmer_id = isset($_POST['farmer_id']) ? intval($_POST['farmer_id']) : 0;
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$age = isset($_POST['age']) ? intval($_POST['age']) : 0;
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$village = isset($_POST['village']) ? trim($_POST['village']) : '';
$ward = isset($_POST['ward']) ? trim($_POST['ward']) : '';
$district = isset($_POST['district']) ? trim($_POST['district']) : '';
$farm_size = isset($_POST['farm_size']) ? floatval($_POST['farm_size']) : 0;
$cotton_farm_size = isset($_POST['cotton_farm_size']) ? floatval($_POST['cotton_farm_size']) : 0;

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($phone_number)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Handle profile picture upload
$profile_picture = '';
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG files are allowed.']);
        exit;
    }

    $filename = uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
        $profile_picture = 'uploads/profiles/' . $filename;
    }
}

try {
    $sql = "UPDATE farmers SET 
            first_name = ?,
            last_name = ?,
            age = ?,
            phone_number = ?,
            village = ?,
            ward = ?,
            district = ?,
            farm_size = ?,
            cotton_farm_size = ?";
    
    $params = [
        $first_name,
        $last_name,
        $age,
        $phone_number,
        $village,
        $ward,
        $district,
        $farm_size,
        $cotton_farm_size
    ];

    if ($profile_picture) {
        $sql .= ", profile_picture = ?";
        $params[] = $profile_picture;
    }

    $sql .= " WHERE id = ?";
    $params[] = $farmer_id;

    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the profile']);
} 