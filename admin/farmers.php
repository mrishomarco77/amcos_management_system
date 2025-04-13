<?php
session_start();
include('includes/config.php');
include('includes/db_connect.php');

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// First, let's check if the required columns exist
$table_altered = true;
try {
    // Check if any required column is missing
    $required_columns = ['username', 'password', 'email', 'middle_name', 'location', 'registered_agency', 'status'];
    $missing_columns = [];
    
    foreach ($required_columns as $column) {
        $check_column = $con->query("SHOW COLUMNS FROM farmers LIKE '$column'");
        if ($check_column->num_rows == 0) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        // Some columns are missing, alter table
        $alter_queries = file_get_contents('database/alter_farmers_table.sql');
        
        // Split queries and execute them separately
        $queries = array_filter(explode(';', $alter_queries));
        
        foreach ($queries as $query) {
            if (trim($query) != '') {
                if (!$con->query(trim($query))) {
                    throw new Exception("Error executing query: " . $con->error);
                }
            }
        }
        
        $_SESSION['success'] = "Database structure updated successfully.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Database update failed: " . $e->getMessage();
    $table_altered = false;
}

// Handle Farmer Registration
if (isset($_POST['submit'])) {
    $first_name = mysqli_real_escape_string($con, trim($_POST['first_name']));
    $middle_name = mysqli_real_escape_string($con, trim($_POST['middle_name']));
    $last_name = mysqli_real_escape_string($con, trim($_POST['last_name']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $location = mysqli_real_escape_string($con, trim($_POST['location']));
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    $registered_agency = mysqli_real_escape_string($con, trim($_POST['registered_agency']));
    $password = mysqli_real_escape_string($con, trim($_POST['password']));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active';

    // Start transaction
    $con->begin_transaction();

    try {
        // First, check if username exists in users table
        $stmt = $con->prepare("SELECT id, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['role'] === 'admin') {
                throw new Exception("This username belongs to an administrator. Please choose a different username.");
            } else {
                throw new Exception("This username is already taken. Please choose a different username.");
            }
        }

        // Check if email exists in users table
        $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("This email address is already registered. Please use a different email.");
        }

        // Check if phone exists in farmers table
        $stmt = $con->prepare("SELECT id FROM farmers WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("This phone number is already registered. Please use a different number.");
        }

        // Insert into users table first
        $stmt = $con->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, 'farmer', ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $status);
        $stmt->execute();
        $user_id = $con->insert_id;

        // Now insert into farmers table
        $stmt = $con->prepare("INSERT INTO farmers (user_id, first_name, middle_name, last_name, phone, location, address, registered_agency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $first_name, $middle_name, $last_name, $phone, $location, $address, $registered_agency);
        $stmt->execute();

        // If we get here, commit the transaction
        $con->commit();
        $_SESSION['success'] = "Farmer registered successfully! Please use your username and password to login.";
        header("Location: farmers.php");
        exit();

    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        $con->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle farmer deletion
if (isset($_POST['delete_farmer'])) {
    $farmer_id = $_POST['farmer_id'];
    $stmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting farmer";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $farmer_id = $_POST['farmer_id'];
    $new_password = password_hash("farmer123", PASSWORD_DEFAULT); // Default password: farmer123
    
    $stmt = $conn->prepare("UPDATE farmers SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $farmer_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password reset successfully to: farmer123";
    } else {
        $_SESSION['error'] = "Error resetting password";
    }
    $stmt->close();
    header("Location: farmers.php");
    exit();
}

// Fetch farmers statistics
try {
    // First, check if the required tables exist
    $tables_exist = true;
    $required_tables = ['farmers', 'purchases', 'sales'];
    
    foreach ($required_tables as $table) {
        $check_table = $con->query("SHOW TABLES LIKE '$table'");
        if ($check_table->num_rows == 0) {
            $tables_exist = false;
            // Import the SQL file to create tables
            $sql = file_get_contents('sql/create_tables.sql');
            $con->multi_query($sql);
            while ($con->more_results() && $con->next_result()); // Clear out the results
            break;
        }
    }

    // Fetch statistics
    $stats_query = "
        SELECT 
            COUNT(DISTINCT f.id) as total_farmers,
            SUM(CASE WHEN f.status = 'active' THEN 1 ELSE 0 END) as active_farmers,
            SUM(CASE WHEN f.status = 'inactive' THEN 1 ELSE 0 END) as inactive_farmers,
            COUNT(DISTINCT f.location) as total_locations
        FROM farmers f
    ";
    $stats = $con->query($stats_query)->fetch_assoc();

    // Fetch all farmers with their details
    $farmers_query = "
        SELECT 
            f.*,
            COALESCE(p.total_purchases, 0) as total_purchases,
            COALESCE(s.total_sales, 0) as total_sales
        FROM farmers f
        LEFT JOIN (
            SELECT farmer_id, COUNT(*) as total_purchases 
            FROM purchases 
            GROUP BY farmer_id
        ) p ON f.id = p.farmer_id
        LEFT JOIN (
            SELECT farmer_id, COUNT(*) as total_sales 
            FROM sales 
            GROUP BY farmer_id
        ) s ON f.id = s.farmer_id
        ORDER BY f.created_at DESC
    ";
    $result = $con->query($farmers_query);
    $farmers = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $farmers[] = $row;
        }
    }

    // Initialize statistics if they're null
    $stats['total_farmers'] = $stats['total_farmers'] ?? 0;
    $stats['active_farmers'] = $stats['active_farmers'] ?? 0;
    $stats['inactive_farmers'] = $stats['inactive_farmers'] ?? 0;
    $stats['total_locations'] = $stats['total_locations'] ?? 0;

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching data: " . $e->getMessage();
    $stats = [
        'total_farmers' => 0,
        'active_farmers' => 0,
        'inactive_farmers' => 0,
        'total_locations' => 0
    ];
    $farmers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Management</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <style>
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .farmer-card {
            transition: all 0.3s;
        }
        .farmer-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .view-toggle .btn {
            padding: 0.375rem 1rem;
        }
        .view-toggle .btn i {
            margin-right: 5px;
        }
        .profile-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .card-view .farmer-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include_once("includes/navbar.php"); ?>
    <?php include_once("includes/sidebar.php"); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Farmers Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right">
                            <a href="register_farmer.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Farmer
                            </a>
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

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info stats-card">
                            <div class="inner">
                                <h3><?php echo $stats['total_farmers']; ?></h3>
                                <p>Total Farmers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success stats-card">
                            <div class="inner">
                                <h3><?php echo $stats['active_farmers']; ?></h3>
                                <p>Active Farmers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning stats-card">
                            <div class="inner">
                                <h3><?php echo $stats['inactive_farmers']; ?></h3>
                                <p>Inactive Farmers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger stats-card">
                            <div class="inner">
                                <h3><?php echo $stats['total_locations']; ?></h3>
                                <p>Total Locations</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Toggle and Export Buttons -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="btn-group view-toggle" role="group">
                            <button type="button" class="btn btn-default active" data-view="table">
                                <i class="fas fa-table"></i> Table View
                            </button>
                            <button type="button" class="btn btn-default" data-view="card">
                                <i class="fas fa-th-large"></i> Card View
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" id="exportExcel">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button type="button" class="btn btn-danger" id="exportPDF">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div class="row view-content" id="tableView">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="farmersTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Location</th>
                                            <th>Agency</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($farmers as $farmer): ?>
                                            <tr>
                                                <td><?php echo $farmer['id']; ?></td>
                                                <td><?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['middle_name'] . ' ' . $farmer['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($farmer['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($farmer['email']); ?></td>
                                                <td><?php echo htmlspecialchars($farmer['location']); ?></td>
                                                <td><?php echo htmlspecialchars($farmer['registered_agency']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $farmer['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($farmer['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-info btn-sm" onclick="viewFarmer(<?php echo $farmer['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="editFarmer(<?php echo $farmer['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteFarmer(<?php echo $farmer['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card View -->
                <div class="row view-content" id="cardView" style="display: none;">
                    <?php foreach ($farmers as $farmer): ?>
                        <div class="col-md-4">
                            <div class="card farmer-card">
                                <div class="card-body text-center">
                                    <?php if (!empty($farmer['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($farmer['profile_picture']); ?>" class="profile-image mb-3" alt="Profile">
                                    <?php else: ?>
                                        <div class="profile-image mb-3 bg-primary d-flex align-items-center justify-content-center">
                                            <span class="text-white" style="font-size: 40px;">
                                                <?php echo strtoupper(substr($farmer['first_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="card-title"><?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']); ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($farmer['phone']); ?><br>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($farmer['email']); ?><br>
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($farmer['location']); ?>
                                    </p>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewFarmer(<?php echo $farmer['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="editFarmer(<?php echo $farmer['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteFarmer(<?php echo $farmer['id']; ?>)">
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

<!-- View Farmer Modal -->
<div class="modal fade" id="viewFarmerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Farmer Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="farmerDetails">
                <!-- Farmer details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Farmer Modal -->
<div class="modal fade" id="editFarmerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Farmer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFarmerForm" action="process_farmer.php" method="POST">
                <input type="hidden" name="farmer_id" id="edit_farmer_id">
                <div class="modal-body">
                    <!-- Form fields will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="edit_farmer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFarmerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this farmer? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form action="process_farmer.php" method="POST">
                    <input type="hidden" name="farmer_id" id="delete_farmer_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" name="delete_farmer">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
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
    $('#farmersTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn-success',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn-danger',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn-info',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }
        ]
    });

    // Handle view toggle
    $('.view-toggle .btn').click(function() {
        $('.view-toggle .btn').removeClass('active');
        $(this).addClass('active');
        
        const view = $(this).data('view');
        $('.view-content').hide();
        $(`#${view}View`).show();
    });

    // Handle export buttons
    $('#exportExcel').click(function() {
        $('.dt-buttons .buttons-excel').click();
    });

    $('#exportPDF').click(function() {
        $('.dt-buttons .buttons-pdf').click();
    });
});

function viewFarmer(id) {
    $.get('get_farmer.php', {id: id}, function(data) {
        let html = `
            <div class="text-center mb-4">
                ${data.profile_picture 
                    ? `<img src="${data.profile_picture}" class="profile-image mb-3" alt="Profile">`
                    : `<div class="profile-image mb-3 bg-primary d-flex align-items-center justify-content-center">
                        <span class="text-white" style="font-size: 40px;">${data.first_name.charAt(0)}</span>
                       </div>`
                }
                <h4>${data.first_name} ${data.middle_name} ${data.last_name}</h4>
                <span class="badge badge-${data.status === 'active' ? 'success' : 'danger'}">${data.status.toUpperCase()}</span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Phone:</strong> ${data.phone}</p>
                    <p><strong>Location:</strong> ${data.location}</p>
                    <p><strong>Address:</strong> ${data.address}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Registered Agency:</strong> ${data.registered_agency}</p>
                    <p><strong>Total Purchases:</strong> ${data.total_purchases}</p>
                    <p><strong>Total Sales:</strong> ${data.total_sales}</p>
                    <p><strong>Member Since:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                </div>
            </div>
        `;
        $('#farmerDetails').html(html);
        $('#viewFarmerModal').modal('show');
    });
}

function editFarmer(id) {
    $.get('get_farmer.php', {id: id}, function(data) {
        $('#edit_farmer_id').val(data.id);
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" class="form-control" name="first_name" value="${data.first_name}" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" value="${data.middle_name}">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="${data.last_name}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" class="form-control" name="phone" value="${data.phone}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="${data.email}" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" class="form-control" name="location" value="${data.location}" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" name="address" value="${data.address}" required>
                    </div>
                    <div class="form-group">
                        <label>Registered Agency</label>
                        <input type="text" class="form-control" name="registered_agency" value="${data.registered_agency}" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" required>
                            <option value="active" ${data.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        $('#editFarmerModal .modal-body').html(html);
        $('#editFarmerModal').modal('show');
    });
}

function deleteFarmer(id) {
    $('#delete_farmer_id').val(id);
    $('#deleteFarmerModal').modal('show');
}
</script>
</body>
</html>
