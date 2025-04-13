<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Fetch all purchases with farmer details
try {
    $purchases_query = "SELECT p.*, f.first_name, f.last_name, f.phone_number 
                       FROM purchases p 
                       JOIN farmers f ON p.farmer_id = f.id 
                       ORDER BY p.purchase_date DESC";
    $purchases_result = $con->query($purchases_query);
    $purchases = [];
    if ($purchases_result) {
        while ($row = $purchases_result->fetch_assoc()) {
            $purchases[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching purchases: " . $e->getMessage();
}

// Fetch active farmers for the add purchase form
try {
    $farmers_query = "SELECT id, first_name, last_name FROM farmers WHERE status = 'active'";
    $farmers_result = $con->query($farmers_query);
    $farmers = [];
    if ($farmers_result) {
        while ($row = $farmers_result->fetch_assoc()) {
            $farmers[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching farmers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotton Purchases Management</title>
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
                        <h1>Cotton Purchases Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addPurchaseModal">
                            <i class="fas fa-plus"></i> New Purchase
                        </button>
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

                <div class="card">
                    <div class="card-body">
                        <table id="purchasesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Purchase ID</th>
                                    <th>Farmer Name</th>
                                    <th>Phone Number</th>
                                    <th>Weight (kg)</th>
                                    <th>Price/kg</th>
                                    <th>Total Amount</th>
                                    <th>Purchase Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchases as $purchase): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($purchase['id']); ?></td>
                                        <td><?php echo htmlspecialchars($purchase['first_name'] . ' ' . $purchase['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($purchase['phone_number']); ?></td>
                                        <td><?php echo number_format($purchase['weight_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($purchase['price_per_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($purchase['total_amount'], 2); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($purchase['purchase_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $purchase['status'] == 'completed' ? 'success' : 
                                                    ($purchase['status'] == 'pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($purchase['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewPurchase(<?php echo $purchase['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($purchase['status'] != 'completed'): ?>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editPurchase(<?php echo $purchase['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deletePurchase(<?php echo $purchase['id']; ?>)">
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

<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Purchase</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_purchase.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Farmer</label>
                        <select class="form-control select2" name="farmer_id" required>
                            <option value="">Select Farmer</option>
                            <?php foreach ($farmers as $farmer): ?>
                                <option value="<?php echo $farmer['id']; ?>">
                                    <?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Weight (kg)</label>
                        <input type="number" class="form-control" name="weight_kg" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Price per kg (TZS)</label>
                        <input type="number" class="form-control" name="price_per_kg" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" class="form-control" name="purchase_date" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_purchase">Add Purchase</button>
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
    // Initialize DataTable with export buttons
    $('#purchasesTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#purchasesTable_wrapper .col-md-6:eq(0)');

    // Calculate total amount automatically
    $('input[name="weight_kg"], input[name="price_per_kg"]').on('input', function() {
        const weight = parseFloat($('input[name="weight_kg"]').val()) || 0;
        const price = parseFloat($('input[name="price_per_kg"]').val()) || 0;
        const total = weight * price;
        $('#totalAmount').text('TZS ' + total.toFixed(2));
    });
});

function viewPurchase(id) {
    // Implement view functionality
    window.location.href = `view_purchase.php?id=${id}`;
}

function editPurchase(id) {
    // Implement edit functionality
    window.location.href = `edit_purchase.php?id=${id}`;
}

function deletePurchase(id) {
    if (confirm('Are you sure you want to delete this purchase?')) {
        window.location.href = `process_purchase.php?action=delete&id=${id}`;
    }
}
</script>
</body>
</html> 