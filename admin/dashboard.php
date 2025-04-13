<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Initialize statistics
$stats = array(
    'total_farmers' => 0,
    'total_crops' => 0,
    'total_inputs' => 0,
    'pending_requests' => 0
);

try {
    // Get statistics
    $stats['total_farmers'] = $con->query("SELECT COUNT(*) FROM farmers")->fetch_row()[0];
    $stats['total_crops'] = $con->query("SELECT COUNT(*) FROM crops")->fetch_row()[0];
    $stats['total_inputs'] = $con->query("SELECT COUNT(*) FROM farming_inputs")->fetch_row()[0];
    $stats['pending_requests'] = $con->query("SELECT COUNT(*) FROM input_requests WHERE status = 'pending'")->fetch_row()[0];

    // Get recent activities
    $query = "SELECT 
                ir.created_at as date,
                CONCAT(f.first_name, ' ', f.last_name) as farmer_name,
                fi.input_name as item,
                ir.status
            FROM input_requests ir
            JOIN farmers f ON ir.farmer_id = f.id
            JOIN farming_inputs fi ON ir.input_id = fi.id
            ORDER BY ir.created_at DESC
            LIMIT 10";
    $activities = $con->query($query)->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching dashboard data: " . $e->getMessage();
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <!-- Total Farmers Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_farmers']); ?></h3>
                            <p>Total Farmers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="farmers.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Crops Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_crops']); ?></h3>
                            <p>Total Crops</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <a href="crops.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Farm Inputs Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_inputs']); ?></h3>
                            <p>Total Farm Inputs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <a href="farming_inputs.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Input Requests Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-gradient-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pending_requests']); ?></h3>
                            <p>Pending Input Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="input_requests.php" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Messaging System -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sms mr-2"></i>
                                Send Message to Farmers
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="smsForm" method="POST" action="send_sms.php">
                                <div class="form-group">
                                    <label>Recipients</label>
                                    <select class="form-control select2" name="recipients[]" multiple required>
                                        <option value="all">All Farmers</option>
                                        <?php
                                        $farmers = $con->query("SELECT id, first_name, last_name, phone_number FROM farmers ORDER BY first_name, last_name");
                                        while ($farmer = $farmers->fetch_assoc()) {
                                            echo '<option value="'.$farmer['id'].'">'.$farmer['first_name'].' '.$farmer['last_name'].' ('.$farmer['phone_number'].')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Message Template</label>
                                    <select class="form-control" name="template" id="messageTemplate">
                                        <option value="">Custom Message</option>
                                        <option value="meeting">Meeting Announcement</option>
                                        <option value="payment">Payment Reminder</option>
                                        <option value="input">Input Collection Notice</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea class="form-control" name="message" rows="4" required></textarea>
                                    <small class="text-muted">Maximum 160 characters per SMS</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i>
                                Recent Activities
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Farmer</th>
                                            <th>Activity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo date('Y-m-d', strtotime($activity['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($activity['farmer_name']); ?></td>
                                            <td>Requested <?php echo htmlspecialchars($activity['item']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $activity['status'] == 'approved' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($activity['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- Select2 -->
<link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
<script src="../plugins/select2/js/select2.full.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Message templates
    const templates = {
        meeting: "Dear farmer, there will be an AMCOS meeting on [DATE] at [TIME]. Your attendance is important. Thank you.",
        payment: "Dear farmer, your payment of TZS [AMOUNT] for [WEIGHT]kg of cotton has been processed. Thank you for your business.",
        input: "Dear farmer, your requested farming inputs are ready for collection at the AMCOS office. Please collect within 3 days."
    };

    // Handle template selection
    $('#messageTemplate').change(function() {
        const template = $(this).val();
        if (template && templates[template]) {
            $('textarea[name="message"]').val(templates[template]);
        } else {
            $('textarea[name="message"]').val('');
        }
    });

    // Handle form submission
    $('#smsForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                alert('Messages sent successfully!');
                $('#smsForm')[0].reset();
                $('.select2').val(null).trigger('change');
            },
            error: function() {
                alert('Error sending messages. Please try again.');
            }
        });
    });
});
</script>