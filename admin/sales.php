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
$stock_data = array(
    'available_stock' => 0,
    'total_sales' => 0,
    'total_purchases' => 0
);

try {
    // Calculate stock data
    $query = "SELECT 
                COALESCE(SUM(p.weight_kg), 0) as total_purchases,
                COALESCE(SUM(s.weight_kg), 0) as total_sales,
                COALESCE(SUM(p.weight_kg), 0) - COALESCE(SUM(s.weight_kg), 0) as available_stock
            FROM 
                (SELECT SUM(weight_kg) as weight_kg FROM purchases WHERE status = 'completed') p,
                (SELECT SUM(weight_kg) as weight_kg FROM sales WHERE status = 'completed') s";
    
    $result = $con->query($query);
    if ($result) {
        $stock_data = $result->fetch_assoc();
    }

    // Fetch all sales with buyer details
    $query = "SELECT 
                s.*,
                b.company_name,
                b.contact_person,
                b.phone_number
            FROM sales s
            LEFT JOIN buyers b ON s.buyer_id = b.id
            ORDER BY s.created_at DESC";
    
    $result = $con->query($query);
    $sales = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Error calculating stock: " . $e->getMessage());
    $_SESSION['error'] = "Error calculating stock: " . $e->getMessage();
}

// Fetch active buyers for the add sale form
try {
    $buyers_query = "SELECT id, company_name, contact_person FROM buyers WHERE status = 'active'";
    $buyers_result = $con->query($buyers_query);
    $buyers = [];
    if ($buyers_result) {
        while ($row = $buyers_result->fetch_assoc()) {
            $buyers[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching buyers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotton Sales Management</title>
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
                        <h1>Cotton Sales Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addSaleModal">
                            <i class="fas fa-plus"></i> New Sale
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

                <!-- Stock Summary Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-warehouse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Available Stock</span>
                                        <span class="info-box-number"><?php echo number_format($stock_data['available_stock'], 2); ?> kg</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-shopping-cart"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Sales</span>
                                        <span class="info-box-number"><?php echo number_format($stock_data['total_sales'], 2); ?> kg</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-shopping-basket"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Purchases</span>
                                        <span class="info-box-number"><?php echo number_format($stock_data['total_purchases'], 2); ?> kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Table -->
                <div class="card">
                    <div class="card-body">
                        <table id="salesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Buyer</th>
                                    <th>Contact Person</th>
                                    <th>Weight (kg)</th>
                                    <th>Price/kg</th>
                                    <th>Total Amount</th>
                                    <th>Sale Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sale['id']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['company_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['contact_person']); ?></td>
                                        <td><?php echo number_format($sale['weight_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($sale['price_per_kg'], 2); ?></td>
                                        <td>TZS <?php echo number_format($sale['total_amount'], 2); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($sale['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $sale['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($sale['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewSale(<?php echo $sale['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($sale['status'] != 'completed'): ?>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="editSale(<?php echo $sale['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSale(<?php echo $sale['id']; ?>)">
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

<!-- Add Sale Modal -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Sale</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_sale.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Buyer</label>
                        <select class="form-control select2" name="buyer_id" required>
                            <option value="">Select Buyer</option>
                            <?php foreach ($buyers as $buyer): ?>
                                <option value="<?php echo $buyer['id']; ?>">
                                    <?php echo htmlspecialchars($buyer['company_name'] . ' (' . $buyer['contact_person'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Weight (kg)</label>
                        <input type="number" class="form-control" name="weight_kg" step="0.01" max="<?php echo $stock_data['available_stock']; ?>" required>
                        <small class="text-muted">Available stock: <?php echo number_format($stock_data['available_stock'], 2); ?> kg</small>
                    </div>
                    <div class="form-group">
                        <label>Price per kg (TZS)</label>
                        <input type="number" class="form-control" name="price_per_kg" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Sale Date</label>
                        <input type="date" class="form-control" name="sale_date" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_sale">Add Sale</button>
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
    $('#salesTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#salesTable_wrapper .col-md-6:eq(0)');

    // Calculate total amount automatically
    $('input[name="weight_kg"], input[name="price_per_kg"]').on('input', function() {
        const weight = parseFloat($('input[name="weight_kg"]').val()) || 0;
        const price = parseFloat($('input[name="price_per_kg"]').val()) || 0;
        const total = weight * price;
        $('#totalAmount').text('TZS ' + total.toFixed(2));
    });

    // Validate weight against available stock
    $('input[name="weight_kg"]').on('input', function() {
        const weight = parseFloat($(this).val()) || 0;
        const maxStock = parseFloat($(this).attr('max')) || 0;
        
        if (weight > maxStock) {
            alert('Weight cannot exceed available stock of ' + maxStock + ' kg');
            $(this).val(maxStock);
        }
    });
});

function viewSale(id) {
    // Implement view functionality
    window.location.href = `view_sale.php?id=${id}`;
}

function editSale(id) {
    // Implement edit functionality
    window.location.href = `edit_sale.php?id=${id}`;
}

function deleteSale(id) {
    if (confirm('Are you sure you want to delete this sale?')) {
        window.location.href = `process_sale.php?action=delete&id=${id}`;
    }
}
</script>
</body>
</html> 