<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get latest market prices
try {
    $market_prices_query = "SELECT mp.*, c.name as crop_name 
                           FROM market_prices mp 
                           JOIN crops c ON mp.crop_id = c.id 
                           ORDER BY mp.date DESC LIMIT 5";
    $market_prices = $connection->query($market_prices_query);
} catch(PDOException $e) {
    error_log("Error fetching market prices: " . $e->getMessage());
    $market_prices = null;
}

// Get farmer's crops if the user is a farmer
$farmer_crops = [];
if ($user['role'] === 'farmer') {
    $crops_query = "SELECT fc.*, c.name as crop_name 
                   FROM farmer_crops fc 
                   JOIN crops c ON fc.crop_id = c.id 
                   WHERE fc.farmer_id = ?";
    $stmt = $connection->prepare($crops_query);
    $stmt->execute([$user_id]);
    $farmer_crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch farmer's data and statistics
try {
    // Get farmer's details and statistics
    $stmt = $connection->prepare("
        SELECT 
            f.*,
            u.email,
            u.role,
            COUNT(DISTINCT cr.id) as total_records,
            SUM(cr.quantity) as total_cotton,
            SUM(cr.quantity * cr.price_per_kg) as total_earnings,
            COUNT(DISTINCT fi.id) as total_inputs
        FROM farmers f 
        JOIN users u ON f.user_id = u.id 
        LEFT JOIN cotton_records cr ON f.id = cr.farmer_id
        LEFT JOIN farmer_inputs fi ON f.id = fi.farmer_id
        WHERE f.user_id = ?
        GROUP BY f.id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent cotton records
    $stmt = $connection->prepare("
        SELECT * FROM cotton_records 
        WHERE farmer_id = ? 
        ORDER BY harvest_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$farmer['id']]);
    $recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pending farm inputs
    $stmt = $connection->prepare("
        SELECT fi.*, i.name as input_name 
        FROM farmer_inputs fi
        JOIN farm_inputs i ON fi.input_id = i.id
        WHERE fi.farmer_id = ? AND fi.status = 'pending'
        ORDER BY fi.request_date DESC
        LIMIT 5
    ");
    $stmt->execute([$farmer['id']]);
    $pending_inputs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get upcoming trainings
    $stmt = $connection->prepare("
        SELECT * FROM trainings 
        WHERE date >= CURRENT_DATE()
        ORDER BY date ASC 
        LIMIT 3
    ");
    $stmt->execute();
    $upcoming_trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error in dashboard: " . $e->getMessage());
    $error = "System error. Please try again. / Kuna hitilafu. Tafadhali jaribu tena.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

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

        .bg-cotton { background: #28a745; }
        .bg-money { background: #17a2b8; }
        .bg-inputs { background: #ffc107; }
        .bg-farm { background: #dc3545; }

        .stats-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stats-label {
            color: #6c757d;
            font-size: 14px;
        }

        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h4>Welcome back, <?php echo htmlspecialchars($farmer['first_name']); ?>! 👋</h4>
                <p class="mb-0">Here's what's happening with your farming activities</p>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon bg-cotton">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="stats-value">
                            <?php echo number_format($farmer['total_cotton'] ?? 0); ?> kg
                        </div>
                        <div class="stats-label">Total Cotton Harvested</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon bg-money">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stats-value">
                            <?php echo number_format($farmer['total_earnings'] ?? 0); ?> TZS
                        </div>
                        <div class="stats-label">Total Earnings</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon bg-inputs">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="stats-value">
                            <?php echo number_format($farmer['total_inputs'] ?? 0); ?>
                        </div>
                        <div class="stats-label">Farm Inputs Received</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon bg-farm">
                            <i class="fas fa-chart-area"></i>
                        </div>
                        <div class="stats-value">
                            <?php echo number_format($farmer['farm_size'] ?? 0, 1); ?> ha
                        </div>
                        <div class="stats-label">Total Farm Size</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Cotton Records -->
                <div class="col-md-6">
                    <div class="recent-activity">
                        <h5 class="mb-4">Recent Cotton Records</h5>
                        <?php if (!empty($recent_records)): ?>
                            <?php foreach ($recent_records as $record): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo number_format($record['quantity']); ?> kg</strong>
                                            <div class="text-muted">
                                                <?php echo date('d M Y', strtotime($record['harvest_date'])); ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <strong><?php echo number_format($record['quantity'] * $record['price_per_kg']); ?> TZS</strong>
                                            <div class="text-muted">
                                                @ <?php echo number_format($record['price_per_kg']); ?> TZS/kg
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent cotton records</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Farm Inputs -->
                <div class="col-md-6">
                    <div class="recent-activity">
                        <h5 class="mb-4">Pending Farm Inputs</h5>
                        <?php if (!empty($pending_inputs)): ?>
                            <?php foreach ($pending_inputs as $input): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($input['input_name']); ?></strong>
                                            <div class="text-muted">
                                                Quantity: <?php echo number_format($input['quantity']); ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-warning">Pending</span>
                                            <div class="text-muted">
                                                <?php echo date('d M Y', strtotime($input['request_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No pending farm inputs</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Trainings -->
        <div class="row">
            <div class="col-12">
                <div class="recent-activity">
                    <h5 class="mb-4">Upcoming Trainings & Workshops</h5>
                    <?php if (!empty($upcoming_trainings)): ?>
                        <div class="row">
                            <?php foreach ($upcoming_trainings as $training): ?>
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($training['title']); ?></h6>
                                            <p class="card-text">
                                                <i class="far fa-calendar-alt"></i> 
                                                <?php echo date('d M Y', strtotime($training['date'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('h:i A', strtotime($training['time'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($training['location']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No upcoming trainings scheduled</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>