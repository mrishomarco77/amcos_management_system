<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get user and farmer information
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("
    SELECT 
        u.*,
        f.id as farmer_id,
        f.first_name,
        f.last_name,
        f.cotton_farm_size,
        f.profile_picture
    FROM users u 
    LEFT JOIN farmers f ON f.user_id = u.id 
    WHERE u.id = ?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get cotton records for the farmer
$stmt = $connection->prepare("
    SELECT 
        cr.*,
        YEAR(cr.harvest_date) as season_year
    FROM cotton_records cr
    WHERE cr.farmer_id = ?
    ORDER BY cr.harvest_date DESC
");
$stmt->execute([$user['farmer_id']]);
$cotton_records = $stmt->fetchAll();

// Calculate totals by season
$season_totals = [];
foreach ($cotton_records as $record) {
    $year = $record['season_year'];
    if (!isset($season_totals[$year])) {
        $season_totals[$year] = [
            'total_quantity' => 0,
            'total_income' => 0,
            'records_count' => 0
        ];
    }
    $season_totals[$year]['total_quantity'] += $record['quantity'];
    $season_totals[$year]['total_income'] += $record['quantity'] * $record['price_per_kg'];
    $season_totals[$year]['records_count']++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotton Records - AMCOS Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }
        .stats-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card h5 {
            color: #34495e;
            margin-bottom: 15px;
        }
        .stats-card h3 {
            color: #2c3e50;
            margin-bottom: 0;
        }
        .records-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .btn-add-record {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-add-record:hover {
            background-color: #218838;
            color: white;
        }
        .season-card {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .season-card h4 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .season-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .season-stat-item {
            text-align: center;
        }
        .season-stat-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .season-stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
            }
        }
        .search-filters {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            height: 300px;
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        @media (max-width: 576px) {
            .stats-card h3 {
                font-size: 1.5rem;
            }
            .season-stat-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">Cotton Records</h2>
                <a href="add_cotton_record.php" class="btn-add-record">
                    <i class="fas fa-plus"></i> Add New Record
                </a>
            </div>

            <!-- Current Season Overview -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <h5><i class="fas fa-chart-area"></i> Total Farm Size</h5>
                        <h3><?php echo number_format($user['cotton_farm_size'] ?? 0, 2); ?> Hectares</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h5><i class="fas fa-seedling"></i> Expected Production</h5>
                        <h3><?php echo number_format(($user['cotton_farm_size'] ?? 0) * 2.5, 2); ?> Tons</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <h5><i class="fas fa-file-alt"></i> Current Season Records</h5>
                        <h3><?php echo count($cotton_records); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Production Chart -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-container">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Season Summary Cards -->
            <div class="row mb-4">
                <?php foreach ($season_totals as $year => $totals): ?>
                <div class="col-md-6">
                    <div class="season-card">
                        <h4><i class="fas fa-calendar-alt"></i> Season <?php echo $year; ?></h4>
                        <div class="season-stats">
                            <div class="season-stat-item">
                                <div class="season-stat-value"><?php echo number_format($totals['total_quantity'], 2); ?> kg</div>
                                <div class="season-stat-label">Total Quantity</div>
                            </div>
                            <div class="season-stat-item">
                                <div class="season-stat-value">TZS <?php echo number_format($totals['total_income'], 0); ?></div>
                                <div class="season-stat-label">Total Income</div>
                            </div>
                            <div class="season-stat-item">
                                <div class="season-stat-value"><?php echo $totals['records_count']; ?></div>
                                <div class="season-stat-label">Records</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Records Table -->
            <div class="records-table">
                <table id="cottonRecordsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Harvest Date</th>
                            <th>Quantity (kg)</th>
                            <th>Price/kg</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cotton_records as $record): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($record['harvest_date'])); ?></td>
                            <td><?php echo number_format($record['quantity'], 2); ?></td>
                            <td>TZS <?php echo number_format($record['price_per_kg'], 2); ?></td>
                            <td>TZS <?php echo number_format($record['quantity'] * $record['price_per_kg'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $record['status'] == 'approved' ? 'success' : 
                                    ($record['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_record.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#cottonRecordsTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true
            });

            // Production Chart
            const ctx = document.getElementById('productionChart').getContext('2d');
            const seasons = <?php echo json_encode(array_keys($season_totals)); ?>;
            const quantities = <?php echo json_encode(array_map(function($total) { 
                return $total['total_quantity']; 
            }, $season_totals)); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: seasons,
                    datasets: [{
                        label: 'Cotton Production (kg)',
                        data: quantities,
                        borderColor: '#2196f3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Cotton Production by Season'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity (kg)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Season'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 