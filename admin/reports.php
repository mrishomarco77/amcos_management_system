<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Initialize stats array
$stats = array();

// Get date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

try {
    // Get total farmers
    $query = "SELECT COUNT(*) as total FROM farmers";
    $result = $con->query($query);
    $stats['total_farmers'] = $result->fetch_assoc()['total'];

    // Get total buyers
    $query = "SELECT COUNT(*) as total FROM buyers";
    $result = $con->query($query);
    $stats['total_buyers'] = $result->fetch_assoc()['total'];

    // Get total purchases weight
    $query = "SELECT COALESCE(SUM(weight_kg), 0) as total FROM purchases WHERE created_at BETWEEN ? AND ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats['total_purchases'] = $stmt->get_result()->fetch_assoc()['total'];

    // Get total sales weight
    $query = "SELECT COALESCE(SUM(weight_kg), 0) as total FROM sales WHERE created_at BETWEEN ? AND ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats['total_sales'] = $stmt->get_result()->fetch_assoc()['total'];

    // Get total payments to farmers
    $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_type = 'farmer' AND created_at BETWEEN ? AND ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats['total_farmer_payments'] = $stmt->get_result()->fetch_assoc()['total'];

    // Get total payments from buyers
    $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_type = 'buyer' AND created_at BETWEEN ? AND ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats['total_buyer_payments'] = $stmt->get_result()->fetch_assoc()['total'];

    // Get top farmers
    $query = "SELECT 
                f.first_name,
                f.last_name,
                COUNT(p.id) as total_transactions,
                COALESCE(SUM(p.weight_kg), 0) as total_weight,
                COALESCE(SUM(p.total_amount), 0) as total_amount
            FROM farmers f
            LEFT JOIN purchases p ON f.id = p.farmer_id
            WHERE p.created_at BETWEEN ? AND ?
            GROUP BY f.id
            ORDER BY total_amount DESC
            LIMIT 5";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $top_farmers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get top buyers
    $query = "SELECT 
                b.company_name,
                COUNT(s.id) as total_transactions,
                COALESCE(SUM(s.weight_kg), 0) as total_weight,
                COALESCE(SUM(s.total_amount), 0) as total_amount
            FROM buyers b
            LEFT JOIN sales s ON b.id = s.buyer_id
            WHERE s.created_at BETWEEN ? AND ?
            GROUP BY b.id
            ORDER BY total_amount DESC
            LIMIT 5";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $top_buyers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error in reports: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching statistics. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../plugins/chart.js/Chart.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reports & Analytics</h1>
                    </div>
                    <div class="col-sm-6">
                        <form class="float-sm-right">
                            <div class="input-group">
                                <input type="text" class="form-control" id="dateRange" name="date_range">
                                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Summary Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo number_format($stats['total_farmers']); ?></h3>
                                <p>Total Farmers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo number_format($stats['total_buyers']); ?></h3>
                                <p>Total Buyers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo number_format($stats['total_purchases'], 2); ?> kg</h3>
                                <p>Total Purchases</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo number_format($stats['total_sales'], 2); ?> kg</h3>
                                <p>Total Sales</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Financial Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Total Payments to Farmers</th>
                                            <td>TZS <?php echo number_format($stats['total_farmer_payments'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Payments from Buyers</th>
                                            <td>TZS <?php echo number_format($stats['total_buyer_payments'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Net Balance</th>
                                            <td>TZS <?php echo number_format($stats['total_buyer_payments'] - $stats['total_farmer_payments'], 2); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="btn-group w-100 mb-2">
                                    <button type="button" class="btn btn-info" onclick="generateReport('purchases')">
                                        <i class="fas fa-download"></i> Purchase Report
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="generateReport('sales')">
                                        <i class="fas fa-download"></i> Sales Report
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="generateReport('payments')">
                                        <i class="fas fa-download"></i> Payment Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Monthly Purchase Trends</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="purchaseChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Monthly Sale Trends</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="saleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top Farmers</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Farmer Name</th>
                                            <th>Transactions</th>
                                            <th>Total Weight</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_farmers as $farmer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']); ?></td>
                                                <td><?php echo number_format($farmer['total_transactions']); ?></td>
                                                <td><?php echo number_format($farmer['total_weight'], 2); ?> kg</td>
                                                <td>TZS <?php echo number_format($farmer['total_amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top Buyers</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Company Name</th>
                                            <th>Transactions</th>
                                            <th>Total Weight</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_buyers as $buyer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($buyer['company_name']); ?></td>
                                                <td><?php echo number_format($buyer['total_transactions']); ?></td>
                                                <td><?php echo number_format($buyer['total_weight'], 2); ?> kg</td>
                                                <td>TZS <?php echo number_format($buyer['total_amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include_once('includes/footer.php'); ?>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../plugins/chart.js/Chart.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#dateRange').daterangepicker({
        startDate: moment('<?php echo $start_date; ?>'),
        endDate: moment('<?php echo $end_date; ?>'),
        locale: {
            format: 'YYYY-MM-DD'
        }
    }, function(start, end) {
        $('input[name="start_date"]').val(start.format('YYYY-MM-DD'));
        $('input[name="end_date"]').val(end.format('YYYY-MM-DD'));
    });

    // Purchase trends chart
    var purchaseCtx = document.getElementById('purchaseChart').getContext('2d');
    new Chart(purchaseCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($purchase_trends)); ?>,
            datasets: [{
                label: 'Weight (kg)',
                data: <?php echo json_encode(array_column($purchase_trends, 'total_weight')); ?>,
                borderColor: 'rgba(60,141,188,0.8)',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Sales trends chart
    var saleCtx = document.getElementById('saleChart').getContext('2d');
    new Chart(saleCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($sale_trends)); ?>,
            datasets: [{
                label: 'Weight (kg)',
                data: <?php echo json_encode(array_column($sale_trends, 'total_weight')); ?>,
                borderColor: 'rgba(40,167,69,0.8)',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
});

function generateReport(type) {
    const startDate = $('input[name="start_date"]').val();
    const endDate = $('input[name="end_date"]').val();
    window.location.href = `generate_report.php?type=${type}&start_date=${startDate}&end_date=${endDate}`;
}
</script>
</body>
</html> 