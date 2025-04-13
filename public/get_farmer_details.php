<?php
require_once '../config/config.php';
require_once '../config/check_admin_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$farmer_id = isset($_GET['farmer_id']) ? intval($_GET['farmer_id']) : 0;

if (!$farmer_id) {
    echo json_encode(['success' => false, 'message' => 'Farmer ID is required']);
    exit;
}

try {
    // Get farmer details
    $sql = "SELECT * FROM farmers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$farmer_id]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$farmer) {
        echo json_encode(['success' => false, 'message' => 'Farmer not found']);
        exit;
    }

    // Get allocated inputs for the current season
    $current_year = date('Y');
    $sql = "SELECT fi.*, f.name as input_name, f.unit 
            FROM farmer_inputs fi 
            JOIN farm_inputs f ON fi.input_id = f.id 
            WHERE fi.farmer_id = ? AND fi.season_year = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$farmer_id, $current_year]);
    $allocated_inputs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate recommended inputs based on cotton farm size
    $sql = "SELECT id, name, unit, recommended_per_acre 
            FROM farm_inputs";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $all_inputs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $recommended_inputs = array_map(function($input) use ($farmer) {
        $input['recommended_quantity'] = $input['recommended_per_acre'] * $farmer['cotton_farm_size'];
        return $input;
    }, $all_inputs);

    $response = [
        'success' => true,
        'farmer' => $farmer,
        'allocated_inputs' => $allocated_inputs,
        'recommended_inputs' => $recommended_inputs
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching farmer details']);
} 