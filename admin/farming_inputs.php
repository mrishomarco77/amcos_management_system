<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Fetch all farming inputs
try {
    $inputs_query = "SELECT * FROM farming_inputs ORDER BY created_at DESC";
    $inputs_result = $con->query($inputs_query);
    $inputs = [];
    if ($inputs_result) {
        while ($row = $inputs_result->fetch_assoc()) {
            $inputs[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching inputs: " . $e->getMessage();
}

// Fetch all farmers for message recipients
try {
    $farmers_query = "SELECT id, first_name, last_name, location FROM farmers WHERE status = 'active'";
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

// Get unique farmer locations for filtering
$locations = array_unique(array_column($farmers, 'location'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farming Inputs Management</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        .input-card {
            transition: transform 0.3s;
        }
        .input-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
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
                        <h1>Farming Inputs Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addInputModal">
                                <i class="fas fa-plus"></i> Add New Input
                            </button>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#sendMessageModal">
                                <i class="fas fa-envelope"></i> Send Message
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

                <!-- Inputs Grid View -->
                <div class="row">
                    <?php foreach ($inputs as $input): ?>
                        <div class="col-md-4">
                            <div class="card input-card">
                                <div class="card-body">
                                    <span class="badge badge-<?php 
                                        echo $input['status'] == 'available' ? 'success' : 
                                            ($input['status'] == 'low_stock' ? 'warning' : 'danger'); 
                                    ?> status-badge">
                                        <?php echo ucfirst(str_replace('_', ' ', $input['status'])); ?>
                                    </span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($input['name']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php echo ucfirst($input['category']); ?>
                                    </h6>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($input['description']); ?>
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><strong>Quantity:</strong> <?php echo $input['quantity_available'] . ' ' . $input['unit']; ?></li>
                                        <li><strong>Price:</strong> TZS <?php echo number_format($input['price_per_unit'], 2); ?>/<?php echo $input['unit']; ?></li>
                                        <li><strong>Supplier:</strong> <?php echo htmlspecialchars($input['supplier']); ?></li>
                                    </ul>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="editInput(<?php echo $input['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" onclick="sendInputMessage(<?php echo $input['id']; ?>)">
                                            <i class="fas fa-envelope"></i> Notify Farmers
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteInput(<?php echo $input['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include_once('includes/footer.php'); ?>
</div>

<!-- Add Input Modal -->
<div class="modal fade" id="addInputModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Farming Input</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_input.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="category" required>
                            <option value="pesticide">Pesticide</option>
                            <option value="fertilizer">Fertilizer</option>
                            <option value="seed">Seed</option>
                            <option value="equipment">Equipment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quantity Available</label>
                                <input type="number" class="form-control" name="quantity_available" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit</label>
                                <input type="text" class="form-control" name="unit" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Price per Unit (TZS)</label>
                        <input type="number" class="form-control" name="price_per_unit" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" class="form-control" name="supplier">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_input">Add Input</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Message to Farmers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="process_message.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Message Type</label>
                        <select class="form-control" name="message_type" id="messageType" required>
                            <option value="input_availability">Input Availability</option>
                            <option value="reminder">Reminder</option>
                            <option value="announcement">Announcement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" id="inputSelection">
                        <label>Related Input</label>
                        <select class="form-control" name="input_id">
                            <option value="">Select Input</option>
                            <?php foreach ($inputs as $input): ?>
                                <option value="<?php echo $input['id']; ?>">
                                    <?php echo htmlspecialchars($input['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Send To</label>
                        <select class="form-control" name="sent_to" id="sendTo" required>
                            <option value="all">All Farmers</option>
                            <option value="specific">Specific Farmers</option>
                            <option value="by_location">By Location</option>
                        </select>
                    </div>
                    <div class="form-group" id="specificFarmers" style="display: none;">
                        <label>Select Farmers</label>
                        <select class="form-control select2" name="farmer_ids[]" multiple>
                            <?php foreach ($farmers as $farmer): ?>
                                <option value="<?php echo $farmer['id']; ?>">
                                    <?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="locationSelection" style="display: none;">
                        <label>Select Locations</label>
                        <select class="form-control select2" name="locations[]" multiple>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>">
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Schedule Message</label>
                        <input type="datetime-local" class="form-control" name="schedule_time">
                        <small class="text-muted">Leave empty to send immediately</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/select2/js/select2.full.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Handle message type change
    $('#messageType').change(function() {
        if ($(this).val() === 'input_availability') {
            $('#inputSelection').show();
        } else {
            $('#inputSelection').hide();
        }
    });

    // Handle send to change
    $('#sendTo').change(function() {
        if ($(this).val() === 'specific') {
            $('#specificFarmers').show();
            $('#locationSelection').hide();
        } else if ($(this).val() === 'by_location') {
            $('#specificFarmers').hide();
            $('#locationSelection').show();
        } else {
            $('#specificFarmers').hide();
            $('#locationSelection').hide();
        }
    });
});

function editInput(id) {
    // Implement edit functionality
    window.location.href = `edit_input.php?id=${id}`;
}

function sendInputMessage(id) {
    $('#messageType').val('input_availability').trigger('change');
    $('select[name="input_id"]').val(id);
    $('#sendMessageModal').modal('show');
}

function deleteInput(id) {
    if (confirm('Are you sure you want to delete this input?')) {
        window.location.href = `process_input.php?action=delete&id=${id}`;
    }
}
</script>
</body>
</html> 