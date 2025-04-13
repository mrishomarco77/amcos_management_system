<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
require_once 'config.php';
$user_id = $_SESSION['user_id'];

try {
    $stmt = $connection->prepare("
        SELECT f.*, u.email, u.role
        FROM farmers f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }

    // Calculate profile completeness
    $total_fields = 8;
    $filled_fields = 0;
    $profile_fields = ['first_name', 'last_name', 'profile_picture', 'phone_number', 'village', 'ward', 'district', 'farm_size'];
    
    foreach ($profile_fields as $field) {
        if (!empty($user[$field])) {
            $filled_fields++;
        }
    }
    $profile_completeness = ($filled_fields / $total_fields) * 100;

} catch(PDOException $e) {
    error_log("Error in sidebar: " . $e->getMessage());
    $user = [];
    $profile_completeness = 0;
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="profile-section">
        <div class="profile-upload">
            <div class="profile-picture-container">
                <img src="<?php echo !empty($user['profile_picture']) ? 'uploads/profiles/' . htmlspecialchars($user['profile_picture']) : 'assets/img/default-profile.jpg'; ?>" 
                     alt="Profile Picture" 
                     class="profile-picture"
                     id="sidebar-profile-preview">
                <?php if (empty($user['profile_picture'])): ?>
                <form id="profile-picture-form" action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <label for="sidebar-profile-picture" title="Upload Profile Picture">
                        <i class="fas fa-camera camera-icon"></i>
                    </label>
                    <input type="file" 
                           id="sidebar-profile-picture" 
                           name="profile_picture" 
                           accept="image/*"
                           onchange="updateProfilePicture(this)">
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-info">
            <h5><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
            <p><?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
        </div>
        
        <div class="progress">
            <div class="progress-bar" 
                 role="progressbar" 
                 style="width: <?php echo $profile_completeness ?? 0; ?>%" 
                 aria-valuenow="<?php echo $profile_completeness ?? 0; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <div class="completeness-text">
            Profile Completeness: <?php echo round($profile_completeness ?? 0); ?>%
        </div>
    </div>

    <div class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="personal_details.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'personal_details.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> Personal Details
        </a>
        <a href="cotton_records.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'cotton_records.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i> Cotton Records
        </a>
        <a href="farm_inputs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'farm_inputs.php' ? 'active' : ''; ?>">
            <i class="fas fa-seedling"></i> Farm Inputs
        </a>
        <a href="training.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'training.php' ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i> Training & Workshop
        </a>
        <a href="documents.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'documents.php' ? 'active' : ''; ?>">
            <i class="fas fa-folder"></i> Documents
        </a>
        <a href="logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<style>
.sidebar {
    height: 100%;
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #28a745;
    padding-top: 20px;
    color: white;
    overflow-y: auto;
}

.sidebar .profile-section {
    text-align: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.sidebar .profile-picture-container {
    width: 150px;
    height: 150px;
    margin: 0 auto 15px;
    position: relative;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.2);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    background-color: #fff;
}

.sidebar .profile-picture {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.sidebar .profile-info {
    margin-bottom: 15px;
}

.sidebar .profile-info h5 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: white;
}

.sidebar .profile-info p {
    margin: 5px 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.sidebar .progress {
    height: 6px;
    margin: 10px auto;
    width: 80%;
    background-color: rgba(255,255,255,0.1);
}

.sidebar .progress-bar {
    background-color: #fff;
}

.sidebar .nav-link {
    color: white;
    padding: 12px 20px;
    opacity: 0.85;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.sidebar .nav-link:hover {
    opacity: 1;
    background-color: rgba(255,255,255,0.1);
}

.sidebar .nav-link.active {
    background-color: rgba(255,255,255,0.2);
    opacity: 1;
}

.sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.profile-upload {
    position: relative;
    display: inline-block;
}

.profile-upload input[type="file"] {
    display: none;
}

.profile-upload label {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: rgba(40, 167, 69, 0.9);
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid white;
}

.profile-upload label:hover {
    background: #28a745;
    transform: scale(1.1);
}

.profile-upload .camera-icon {
    color: white;
    font-size: 14px;
}

.completeness-text {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.9);
    margin-top: 5px;
}
</style>

<script>
function updateProfilePicture(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('sidebar-profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
        
        // Automatically submit the form when a file is selected
        document.getElementById('profile-picture-form').submit();
    }
}
</script> 