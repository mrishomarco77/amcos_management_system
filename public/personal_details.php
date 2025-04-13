<?php
require_once '../includes/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's details
try {
    $stmt = $connection->prepare("
        SELECT f.*, u.email 
        FROM farmers f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $farmer = $stmt->fetch();

    // Calculate profile completeness
    $total_fields = 9;
    $filled_fields = 0;
    $fields_to_check = ['first_name', 'last_name', 'age', 'phone_number', 'village', 'ward', 'district', 'farm_size', 'cotton_farm_size'];
    
    foreach ($fields_to_check as $field) {
        if (!empty($farmer[$field])) {
            $filled_fields++;
        }
    }
    
    $profile_completeness = ($filled_fields / $total_fields) * 100;

} catch (PDOException $e) {
    error_log("Error fetching farmer details: " . $e->getMessage());
    $error = "System error. Please try again. / Kuna hitilafu. Tafadhali jaribu tena.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Details - AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .progress {
            height: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="profile-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Personal Information</h4>
                    <a href="edit_profile.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Details
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['first_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['last_name'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Age</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['age'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['phone_number'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Village</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['village'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ward</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['ward'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">District</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['district'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total Farm Size (Hectares)</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['farm_size'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cotton Farm Size (Hectares)</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer['cotton_farm_size'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 