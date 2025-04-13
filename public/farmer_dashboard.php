<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';
session_start();

// Debug information
echo "Current script path: " . __FILE__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user and farmer information
$user_id = $_SESSION['user_id'];

try {
    $stmt = $connection->prepare("
        SELECT 
            u.*,
            f.id as farmer_id,
            f.first_name,
            f.last_name,
            f.profile_picture,
            f.farm_size,
            f.cotton_farm_size,
            (SELECT COUNT(*) FROM cotton_records WHERE farmer_id = f.id) as total_records,
            (SELECT SUM(quantity) FROM cotton_records WHERE farmer_id = f.id) as total_cotton,
            (SELECT COUNT(*) FROM farmer_inputs WHERE farmer_id = f.id) as total_inputs
        FROM users u 
        LEFT JOIN farmers f ON f.user_id = u.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }

    // Get recent activities
    $stmt = $connection->prepare("
        (SELECT 
            'cotton_record' as type,
            created_at,
            quantity as value,
            'kg' as unit
        FROM cotton_records 
        WHERE farmer_id = ?
        ORDER BY created_at DESC
        LIMIT 5)
        UNION ALL
        (SELECT 
            'farm_input' as type,
            request_date as created_at,
            quantity as value,
            unit
        FROM farmer_inputs fi
        JOIN farm_inputs i ON fi.input_id = i.id
        WHERE farmer_id = ?
        ORDER BY request_date DESC
        LIMIT 5)
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['farmer_id'], $user['farmer_id']]);
    $recent_activities = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Error in farmer dashboard: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AMCOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
            color: white;
        }

        .stats-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #2c3e50;
        }

        .stats-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .activity-item {
            padding: 15px;
            border-left: 3px solid #28a745;
            margin-bottom: 10px;
            background: white;
            border-radius: 0 5px 5px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .activity-item:hover {
            background: #f8f9fa;
        }

        .quick-action {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 15px;
            border-radius: 10px;
            background: white;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: inherit;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 60px;
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>

        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #3498db;">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($user['total_cotton'] ?? 0); ?> kg</div>
                    <div class="stats-label">Total Cotton Produced</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #2ecc71;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($user['total_records'] ?? 0); ?></div>
                    <div class="stats-label">Cotton Records</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #e74c3c;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($user['total_inputs'] ?? 0); ?></div>
                    <div class="stats-label">Farm Inputs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #9b59b6;">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <div class="stats-value"><?php echo number_format($user['farm_size'] ?? 0, 1); ?> ha</div>
                    <div class="stats-label">Total Farm Size</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted text-center">No recent activities</p>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($activity['type'] == 'cotton_record'): ?>
                                                <i class="fas fa-leaf text-success me-2"></i>
                                                <strong>Added Cotton Record</strong>
                                                <p class="mb-0 text-muted">
                                                    Recorded <?php echo number_format($activity['value']); ?> <?php echo $activity['unit']; ?> of cotton
                                                </p>
                                            <?php else: ?>
                                                <i class="fas fa-tools text-primary me-2"></i>
                                                <strong>Requested Farm Input</strong>
                                                <p class="mb-0 text-muted">
                                                    Requested <?php echo number_format($activity['value']); ?> <?php echo $activity['unit']; ?> of inputs
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="add_cotton_record.php" class="quick-action">
                            <i class="fas fa-plus-circle text-primary me-2"></i>
                            Add Cotton Record
                        </a>
                        <a href="request_farm_inputs.php" class="quick-action">
                            <i class="fas fa-shopping-cart text-success me-2"></i>
                            Request Farm Inputs
                        </a>
                        <a href="training.php" class="quick-action">
                            <i class="fas fa-calendar-alt text-warning me-2"></i>
                            View Training Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 