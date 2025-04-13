<?php
require_once '../includes/config.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get farmer ID
    $stmt = $connection->prepare("SELECT id FROM farmers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $farmer = $stmt->fetch();
    $farmer_id = $farmer ? $farmer['id'] : null;

    // Start transaction
    $connection->beginTransaction();

    if ($farmer_id) {
        // Update existing farmer
        $stmt = $connection->prepare("
            UPDATE farmers 
            SET first_name = ?,
                last_name = ?,
                age = ?,
                phone_number = ?,
                village = ?,
                ward = ?,
                district = ?,
                farm_size = ?,
                cotton_farm_size = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['age'] ?: null,
            $_POST['phone_number'],
            $_POST['village'],
            $_POST['ward'],
            $_POST['district'],
            $_POST['farm_size'] ?: null,
            $_POST['cotton_farm_size'] ?: null,
            $farmer_id
        ]);
    } else {
        // Insert new farmer
        $stmt = $connection->prepare("
            INSERT INTO farmers (
                user_id, first_name, last_name, age, phone_number,
                village, ward, district, farm_size, cotton_farm_size
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['age'] ?: null,
            $_POST['phone_number'],
            $_POST['village'],
            $_POST['ward'],
            $_POST['district'],
            $_POST['farm_size'] ?: null,
            $_POST['cotton_farm_size'] ?: null
        ]);
    }

    // Commit transaction
    $connection->commit();
    
    // Return updated user data
    $stmt = $connection->prepare("
        SELECT 
            f.first_name,
            f.last_name,
            f.age,
            f.phone_number,
            f.village,
            f.ward,
            f.district,
            f.farm_size,
            f.cotton_farm_size,
            f.profile_picture
        FROM farmers f
        WHERE f.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Personal details updated successfully!',
        'user' => $updated_user
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    
    error_log("Error updating personal details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating personal details. Please try again.'
    ]);
} 