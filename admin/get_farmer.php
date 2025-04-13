<?php
require_once 'includes/db_connect.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $connection->prepare("
            SELECT 
                f.*, 
                COUNT(DISTINCT p.id) as total_purchases,
                COUNT(DISTINCT s.id) as total_sales
            FROM farmers f
            LEFT JOIN purchases p ON f.id = p.farmer_id
            LEFT JOIN sales s ON f.id = s.farmer_id
            WHERE f.id = ?
            GROUP BY f.id
        ");
        $stmt->execute([$_GET['id']]);
        $farmer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($farmer) {
            // Convert timestamps to ISO format for proper JSON encoding
            $farmer['created_at'] = date('c', strtotime($farmer['created_at']));
            $farmer['updated_at'] = date('c', strtotime($farmer['updated_at']));
            
            // Remove sensitive information
            unset($farmer['password']);
            
            header('Content-Type: application/json');
            echo json_encode($farmer);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Farmer not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No farmer ID provided']);
} 