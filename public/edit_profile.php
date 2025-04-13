<?php
require_once '../includes/config.php';
require_once '../includes/tabora_data.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare update statement for farmers table
        $stmt = $connection->prepare("
            UPDATE farmers 
            SET first_name = ?, 
                last_name = ?, 
                age = ?, 
                phone_number = ?, 
                village = ?, 
                ward = ?, 
                district = ?, 
                farm_size = ?, 
                cotton_farm_size = ?
            WHERE user_id = ?
        ");

        // Execute update with form data
        $result = $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['age'],
            $_POST['phone'],
            $_POST['village'],
            $_POST['ward'],
            $_POST['district'],
            $_POST['total_farm_size'],
            $_POST['cotton_farm_size'],
            $_SESSION['user_id']
        ]);

        if ($result) {
            // Handle profile picture upload if provided and if no profile picture exists
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK && empty($farmer['profile_picture'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                $file_type = $_FILES['profile_picture']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = 'uploads/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid('profile_') . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                        // Update profile picture in database
                        $stmt = $connection->prepare("UPDATE farmers SET profile_picture = ? WHERE user_id = ?");
                        $stmt->execute([$new_filename, $_SESSION['user_id']]);
                    }
                }
            }
            
            $_SESSION['success_message'] = "Profile updated successfully! / Taarifa zako zimehifadhiwa!";
            header("Location: personal_details.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error updating farmer details: " . $e->getMessage());
        $error = "An error occurred while updating your profile. Please try again. / Kuna hitilafu. Tafadhali jaribu tena.";
    }
}

// Fetch current user's details
try {
    $stmt = $connection->prepare("
        SELECT 
            f.id,
            f.first_name,
            f.last_name,
            f.age,
            f.phone_number,
            f.village,
            f.ward,
            f.district,
            f.farm_size,
            f.cotton_farm_size,
            f.profile_picture,
            u.email,
            u.role
        FROM farmers f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$farmer) {
        throw new Exception("Farmer not found");
    }

    // Map database fields to form fields
    $farmer['phone'] = $farmer['phone_number'];
    $farmer['total_farm_size'] = $farmer['farm_size'];

} catch (PDOException $e) {
    error_log("Error fetching farmer details: " . $e->getMessage());
    $error = "System error. Please try again. / Kuna hitilafu. Tafadhali jaribu tena.";
}

// Initialize $tabora_districts if not already set
if (!isset($tabora_districts)) {
    $tabora_districts = [
        'Igunga',
        'Kaliua',
        'Nzega',
        'Sikonge',
        'Tabora Municipal',
        'Urambo',
        'Uyui'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - AMCOS Management System</title>
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

        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-section">
                <h4 class="mb-4">Edit Profile / Hariri Taarifa Zako</h4>
                
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required-field">First Name / Jina la Kwanza</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($farmer['first_name'] ?? ''); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Last Name / Jina la Mwisho</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($farmer['last_name'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Age / Umri</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="age" 
                                           value="<?php echo htmlspecialchars($farmer['age'] ?? ''); ?>"
                                           min="18"
                                           max="120">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Phone / Namba ya Simu</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($farmer['phone'] ?? ''); ?>"
                                           pattern="[0-9]{10}"
                                           title="Please enter a valid 10-digit phone number"
                                           required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label required-field">District / Wilaya</label>
                                    <select class="form-select" name="district" id="district" required onchange="updateWards()">
                                        <option value="">Select District / Chagua Wilaya</option>
                                        <?php foreach ($tabora_districts as $district): ?>
                                            <option value="<?php echo htmlspecialchars($district); ?>" 
                                                    <?php echo ($farmer['district'] === $district) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($district); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required-field">Ward / Kata</label>
                                    <select class="form-select" name="ward" id="ward" required onchange="updateVillages()">
                                        <option value="">Select Ward / Chagua Kata</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required-field">Village / Kijiji</label>
                                    <select class="form-select" name="village" id="village" required>
                                        <option value="">Select Village / Chagua Kijiji</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required-field">Total Farm Size (Hectares) / Ukubwa wa Shamba (Hekta)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="total_farm_size" 
                                           value="<?php echo htmlspecialchars($farmer['total_farm_size'] ?? ''); ?>"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">Cotton Farm Size (Hectares) / Ukubwa wa Shamba la Pamba (Hekta)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="cotton_farm_size" 
                                           value="<?php echo htmlspecialchars($farmer['cotton_farm_size'] ?? ''); ?>"
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes / Hifadhi Mabadiliko
                                    </button>
                                    <a href="personal_details.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel / Ghairi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tabora region data
        const taboraData = <?php echo json_encode($tabora_data ?? []); ?>;
        
        function updateWards() {
            const districtSelect = document.getElementById('district');
            const wardSelect = document.getElementById('ward');
            const selectedDistrict = districtSelect.value;
            
            // Clear current options
            wardSelect.innerHTML = '<option value="">Select Ward / Chagua Kata</option>';
            
            if (selectedDistrict && taboraData[selectedDistrict]) {
                const wards = Object.keys(taboraData[selectedDistrict]);
                wards.forEach(ward => {
                    const option = new Option(ward, ward);
                    if (ward === '<?php echo $farmer['ward'] ?? ''; ?>') {
                        option.selected = true;
                    }
                    wardSelect.add(option);
                });
            }
            
            // Update villages when ward changes
            updateVillages();
        }
        
        function updateVillages() {
            const districtSelect = document.getElementById('district');
            const wardSelect = document.getElementById('ward');
            const villageSelect = document.getElementById('village');
            const selectedDistrict = districtSelect.value;
            const selectedWard = wardSelect.value;
            
            // Clear current options
            villageSelect.innerHTML = '<option value="">Select Village / Chagua Kijiji</option>';
            
            if (selectedDistrict && selectedWard && 
                taboraData[selectedDistrict] && 
                taboraData[selectedDistrict][selectedWard]) {
                const villages = taboraData[selectedDistrict][selectedWard];
                villages.forEach(village => {
                    const option = new Option(village, village);
                    if (village === '<?php echo $farmer['village'] ?? ''; ?>') {
                        option.selected = true;
                    }
                    villageSelect.add(option);
                });
            }
        }
        
        // Initialize dropdowns with current values
        window.onload = function() {
            updateWards();
        };
    </script>
</body>
</html> 