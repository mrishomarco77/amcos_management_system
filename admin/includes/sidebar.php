<?php
require_once 'session.php';
require_once 'db_connect.php';

// Get admin details from session
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT fullname FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();
?>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="app-brand">
            <span class="brand-name">AMCOS</span>
            <span class="brand-icon">AM</span>
        </div>
        <div class="profile-section text-center py-3">
            <div class="profile-info">
                <div class="avatar mb-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_data['fullname']); ?>&background=random" 
                         alt="Profile" class="rounded-circle" width="80" height="80">
                </div>
                <h6 class="mb-1 text-truncate"><?php echo htmlspecialchars($admin_data['fullname']); ?></h6>
                <small class="text-muted">Administrator</small>
            </div>
        </div>
    </div>

    <div class="sidebar-body">
        <ul class="nav-list">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'farmers.php' ? 'active' : ''; ?>">
                <a href="farmers.php" title="Farmers">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Farmers</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'crops.php' ? 'active' : ''; ?>">
                <a href="crops.php" title="Crops">
                    <i class="fas fa-seedling"></i>
                    <span class="nav-text">Crops</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'purchases.php' ? 'active' : ''; ?>">
                <a href="purchases.php" title="Purchases">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-text">Purchases</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                <a href="sales.php" title="Sales">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Sales</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <a href="payments.php" title="Payments">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="nav-text">Payments</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php" title="Reports">
                    <i class="fas fa-file-alt"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php" title="Settings">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 250px;
    background: #2c3e50;
    color: #fff;
    z-index: 1000;
    padding-top: 20px;
    transition: all 0.3s ease;
    overflow-x: hidden;
}

body.sidebar-collapsed .sidebar {
    width: 70px;
}

.brand-name {
    display: block;
}

.brand-icon {
    display: none;
}

body.sidebar-collapsed .brand-name {
    display: none;
}

body.sidebar-collapsed .brand-icon {
    display: block;
}

body.sidebar-collapsed .profile-section,
body.sidebar-collapsed .nav-text {
    opacity: 0;
    visibility: hidden;
}

body.sidebar-collapsed .nav-item i {
    margin-right: 0;
}

.sidebar-header {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.app-brand {
    font-size: 1.5rem;
    font-weight: bold;
    color: #fff;
    text-decoration: none;
    text-align: center;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.profile-section {
    text-align: center;
    padding: 1rem 0;
    transition: all 0.3s ease;
}

.profile-info .avatar img {
    border: 3px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}

.sidebar-body {
    padding: 1rem 0;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 0.5rem;
}

.nav-item a {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    position: relative;
}

.nav-item a:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

.nav-item.active a {
    color: #fff;
    background: rgba(255,255,255,0.1);
}

.nav-item i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

body.sidebar-collapsed .nav-item a {
    justify-content: center;
    padding: 0.75rem;
}

body.sidebar-collapsed .nav-item a:hover::after {
    content: attr(title);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background: #2c3e50;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    margin-left: 10px;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1001;
}

@media (max-width: 768px) {
    .sidebar {
        margin-left: -250px;
    }
    body.sidebar-collapsed .sidebar {
        margin-left: 0;
        width: 250px;
    }
    body.sidebar-collapsed .brand-name {
        display: block;
    }
    body.sidebar-collapsed .brand-icon {
        display: none;
    }
    body.sidebar-collapsed .profile-section,
    body.sidebar-collapsed .nav-text {
        opacity: 1;
        visibility: visible;
    }
    body.sidebar-collapsed .nav-item i {
        margin-right: 10px;
    }
    body.sidebar-collapsed .nav-item a {
        justify-content: flex-start;
        padding: 0.75rem 1.5rem;
    }
}
</style>

<script>
// Listen for sidebar toggle event
window.addEventListener('sidebarToggle', function() {
    const sidebar = document.querySelector('.sidebar');
    // Add smooth transition class
    sidebar.style.transition = 'all 0.3s ease';
});
</script>

<!-- Add Bootstrap 5 JS and Font Awesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">