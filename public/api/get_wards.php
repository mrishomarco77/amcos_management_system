<?php
require_once '../../includes/config.php';
require_once '../../includes/tabora_data.php';

header('Content-Type: application/json');

// Check if district is provided
if (!isset($_GET['district'])) {
    echo json_encode([
        'success' => false,
        'message' => 'District parameter is required'
    ]);
    exit;
}

$district = $_GET['district'];

// Check if district exists in our data
if (!isset($tabora_districts[$district])) {
    echo json_encode([
        'success' => false,
        'message' => 'District not found'
    ]);
    exit;
}

// Return wards for the district
echo json_encode([
    'success' => true,
    'wards' => $tabora_districts[$district]
]);
?> 