<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Farmers</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM farmers";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Crops</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM crops";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-seedling fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Farm Inputs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM farm_inputs";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tools fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending Input Requests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM farmer_inputs WHERE status = 'pending'";
                                    $result = $conn->query($query);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Farmer</th>
                                        <th>Activity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT fi.*, f.first_name, f.last_name, farm.name as input_name 
                                             FROM farmer_inputs fi 
                                             JOIN farmers f ON fi.farmer_id = f.id 
                                             JOIN farm_inputs farm ON fi.input_id = farm.id 
                                             ORDER BY fi.created_at DESC LIMIT 5";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                        echo "<td>Requested " . htmlspecialchars($row['input_name']) . "</td>";
                                        echo "<td><span class='badge bg-" . 
                                             ($row['status'] == 'pending' ? 'warning' : 
                                             ($row['status'] == 'approved' ? 'success' : 'danger')) . 
                                             "'>" . ucfirst($row['status']) . "</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    margin-bottom: 1.5rem;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.badge {
    padding: 0.5em 1em;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>