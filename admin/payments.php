<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Initialize variables
$payment_stats = array(
    'total_farmer_payments' => 0,
    'total_buyer_payments' => 0,
    'farmers_paid' => 0,
    'buyers_paid' => 0
);

$farmer_payments = array();
$buyer_payments = array();

try {
    // Get payment statistics
    $query = "SELECT 
                COALESCE(SUM(CASE WHEN payment_type = 'farmer' THEN amount ELSE 0 END), 0) as total_farmer_payments,
                COALESCE(SUM(CASE WHEN payment_type = 'buyer' THEN amount ELSE 0 END), 0) as total_buyer_payments,
                COUNT(DISTINCT CASE WHEN payment_type = 'farmer' THEN farmer_id END) as farmers_paid,
                COUNT(DISTINCT CASE WHEN payment_type = 'buyer' THEN buyer_id END) as buyers_paid
            FROM payments
            WHERE status = 'completed'";
    
    $result = $con->query($query);
    if ($result) {
        $payment_stats = $result->fetch_assoc();
    }

    // Get farmer payments
    $query = "SELECT 
                p.*,
                CONCAT(f.first_name, ' ', f.last_name) as farmer_name,
                pu.weight_kg as purchase_weight,
                pu.price_per_kg
            FROM payments p
            LEFT JOIN farmers f ON p.farmer_id = f.id
            LEFT JOIN purchases pu ON p.purchase_id = pu.id
            WHERE p.payment_type = 'farmer'
            ORDER BY p.payment_date DESC";
    
    $result = $con->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $farmer_payments[] = $row;
        }
    }

    // Get buyer payments
    $query = "SELECT 
                p.*,
                b.company_name as buyer_name,
                s.weight_kg as sale_weight,
                s.price_per_kg
            FROM payments p
            LEFT JOIN buyers b ON p.buyer_id = b.id
            LEFT JOIN sales s ON p.sale_id = s.id
            WHERE p.payment_type = 'buyer'
            ORDER BY p.payment_date DESC";
    
    $result = $con->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $buyer_payments[] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Error calculating payment statistics: " . $e->getMessage());
    $_SESSION['error'] = "Error calculating payment statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Management</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
                        <h1>Payments Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addFarmerPaymentModal">
                                <i class="fas fa-plus"></i> New Farmer Payment
                            </button>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBuyerPaymentModal">
                                <i class="fas fa-plus"></i> New Buyer Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

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

                <!-- Payment Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>TZS <?php echo number_format($payment_stats['total_farmer_payments'], 2); ?></h3>
                                <p>Total Farmer Payments</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>TZS <?php echo number_format($payment_stats['total_buyer_payments'], 2); ?></h3>
                                <p>Total Buyer Payments</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $payment_stats['farmers_paid']; ?></h3>
                                <p>Farmers Paid</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $payment_stats['buyers_paid']; ?></h3>
                                <p>Buyers Paid</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Farmer Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Farmer Payments</h3>
                    </div>
                    <div class="card-body">
                        <table id="farmerPaymentsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Farmer Name</th>
                                    <th>Purchase Weight</th>
                                    <th>Price/kg</th>
                                    <th>Amount Paid</th>
                                    <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($farmer_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['farmer_name']); ?></td>
                                        <td><?php echo number_format($payment['purchase_weight'], 2); ?> kg</td>
                                        <td>TZS <?php echo number_format($payment['price_per_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewPayment(<?php echo $payment['id']; ?>, 'farmer')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($payment['status'] != 'completed'): ?>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editPayment(<?php echo $payment['id']; ?>, 'farmer')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deletePayment(<?php echo $payment['id']; ?>, 'farmer')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Buyer Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Buyer Payments</h3>
                    </div>
                    <div class="card-body">
                        <table id="buyerPaymentsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Buyer Name</th>
                                    <th>Sale Weight</th>
                                    <th>Price/kg</th>
                                    <th>Amount Paid</th>
                                    <th>Payment Date</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($buyer_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['buyer_name']); ?></td>
                                        <td><?php echo number_format($payment['sale_weight'], 2); ?> kg</td>
                                        <td>TZS <?php echo number_format($payment['price_per_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $payment['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewPayment(<?php echo $payment['id']; ?>, 'buyer')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($payment['status'] != 'completed'): ?>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editPayment(<?php echo $payment['id']; ?>, 'buyer')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deletePayment(<?php echo $payment['id']; ?>, 'buyer')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include_once('includes/footer.php'); ?>
</div>

<!-- Add Farmer Payment Modal -->
<div class="modal fade" id="addFarmerPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Farmer Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_payment.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="payment_type" value="farmer">
                    <div class="form-group">
                        <label>Purchase Reference</label>
                        <select class="form-control select2" name="purchase_id" required>
                            <option value="">Select Purchase</option>
                            <!-- Add PHP code to populate purchases -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (TZS)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" required>
                    </div>
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" class="form-control" name="reference_number">
                        <small class="text-muted">Required for bank transfer and mobile money payments</small>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_payment">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Buyer Payment Modal -->
<div class="modal fade" id="addBuyerPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Buyer Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_payment.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="payment_type" value="buyer">
                    <div class="form-group">
                        <label>Sale Reference</label>
                        <select class="form-control select2" name="sale_id" required>
                            <option value="">Select Sale</option>
                            <!-- Add PHP code to populate sales -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (TZS)</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" required>
                    </div>
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" class="form-control" name="reference_number">
                        <small class="text-muted">Required for bank transfer and mobile money payments</small>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_payment">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../plugins/jszip/jszip.min.js"></script>
<script src="../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#farmerPaymentsTable, #buyerPaymentsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#farmerPaymentsTable_wrapper .col-md-6:eq(0)');

    // Show/hide reference number field based on payment method
    $('select[name="payment_method"]').change(function() {
        const method = $(this).val();
        const refField = $(this).closest('form').find('input[name="reference_number"]');
        
        if (method === 'cash') {
            refField.prop('required', false).closest('.form-group').hide();
        } else {
            refField.prop('required', true).closest('.form-group').show();
        }
    });
});

function viewPayment(id, type) {
    window.location.href = `view_payment.php?id=${id}&type=${type}`;
}

function editPayment(id, type) {
    window.location.href = `edit_payment.php?id=${id}&type=${type}`;
}

function deletePayment(id, type) {
    if (confirm('Are you sure you want to delete this payment?')) {
        window.location.href = `process_payment.php?action=delete&id=${id}&type=${type}`;
    }
}
</script>
</body>
</html> 