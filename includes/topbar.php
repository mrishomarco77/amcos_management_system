<?php
// Get notifications count
$notifications_count = 0;
try {
    $stmt = $connection->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $notifications_count = $result['count'];
} catch(PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
}
?>

<nav class="topbar navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="../assets/img/amcos-logo.png" alt="AMCOS Logo" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="topbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="available_inputs.php">
                        <i class="fas fa-store"></i> Available Inputs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php">
                        <i class="fas fa-comment-alt"></i> Feedback
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($notifications_count > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $notifications_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div id="notificationsContent">
                            <!-- Notifications will be loaded here via AJAX -->
                            <div class="dropdown-item text-center">
                                <small>Loading notifications...</small>
                            </div>
                        </div>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/profiles/' . htmlspecialchars($user['profile_picture']) : '../assets/img/default-profile.jpg'; ?>" 
                             alt="Profile" class="rounded-circle" width="32" height="32">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="change_password.php">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.topbar {
    position: fixed;
    top: 0;
    right: 0;
    left: 250px; /* Same as sidebar width */
    z-index: 1030;
    height: 60px;
}

.main-content {
    margin-top: 60px; /* Same as topbar height */
}

.navbar-nav .nav-link {
    padding: 0.5rem 1rem;
    color: #2c3e50;
}

.navbar-nav .nav-link:hover {
    color: #28a745;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 10px;
}

#notificationsDropdown .badge {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(25%, -25%);
}
</style>

<script>
// Load notifications via AJAX
function loadNotifications() {
    $.get('api/get_notifications.php', function(response) {
        if (response.success) {
            let html = '';
            if (response.notifications.length > 0) {
                response.notifications.forEach(function(notification) {
                    html += `
                        <a class="dropdown-item" href="${notification.link || '#'}">
                            <small class="text-muted">${notification.created_at}</small>
                            <p class="mb-0">${notification.message}</p>
                        </a>
                    `;
                });
            } else {
                html = '<div class="dropdown-item text-center"><small>No new notifications</small></div>';
            }
            $('#notificationsContent').html(html);
        }
    });
}

// Load notifications when dropdown is opened
$('#notificationsDropdown').on('show.bs.dropdown', function () {
    loadNotifications();
});
</script> 