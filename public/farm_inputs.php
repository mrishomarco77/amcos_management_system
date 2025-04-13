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

// Get farm inputs for the farmer
$stmt = $connection->prepare("
    SELECT 
        fi.*,
        i.name as input_name,
        i.unit,
        i.recommended_per_hectare
    FROM farmer_inputs fi
    JOIN farm_inputs i ON i.id = fi.input_id
    WHERE fi.farmer_id = ?
    ORDER BY fi.request_date DESC
");
$stmt->execute([$user['farmer_id']]);
$farmer_inputs = $stmt->fetchAll();

// Calculate statistics
$total_inputs = count($farmer_inputs);
$pending_inputs = 0;
$approved_inputs = 0;
$delivered_inputs = 0;

foreach ($farmer_inputs as $input) {
    switch ($input['status']) {
        case 'pending':
            $pending_inputs++;
            break;
        case 'approved':
            $approved_inputs++;
            break;
        case 'delivered':
            $delivered_inputs++;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Inputs - AMCOS Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
        .inputs-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .btn-request-input {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-request-input:hover {
            background-color: #218838;
            color: white;
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
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        @media (max-width: 576px) {
            .stats-card h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">Farm Inputs Management</h2>
                <a href="request_input.php" class="btn-request-input">
                    <i class="fas fa-plus"></i> Request New Input
                </a>
            </div>

            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-boxes"></i> Total Requests</h5>
                        <h3><?php echo $total_inputs; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-clock"></i> Pending</h5>
                        <h3><?php echo $pending_inputs; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-check"></i> Approved</h5>
                        <h3><?php echo $approved_inputs; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-truck"></i> Delivered</h5>
                        <h3><?php echo $delivered_inputs; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Inputs Table -->
            <div class="inputs-table">
                <table id="inputsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Input Name</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Delivery Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($farmer_inputs as $input): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($input['input_name']); ?></td>
                            <td><?php echo number_format($input['quantity'], 2) . ' ' . htmlspecialchars($input['unit']); ?></td>
                            <td><?php echo date('d M Y', strtotime($input['request_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo 
                                    $input['status'] == 'delivered' ? 'success' : 
                                    ($input['status'] == 'approved' ? 'info' : 
                                    ($input['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                                    <?php echo ucfirst($input['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $input['delivery_date'] ? date('d M Y', strtotime($input['delivery_date'])) : '-'; ?></td>
                            <td>
                                <a href="view_input.php?id=<?php echo $input['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($input['status'] == 'pending'): ?>
                                <a href="edit_input_request.php?id=<?php echo $input['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
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
    <script>
        $(document).ready(function() {
            $('#inputsTable').DataTable({
                order: [[2, 'desc']],
                pageLength: 10,
                responsive: true
            });
        });
    </script>
</body>
</html> 