<?php
require_once 'session.php';
require_once 'db_connect.php';

// Get admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT fullname FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            height: 70px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 999;
            transition: all 0.3s ease;
            padding: 0 1rem;
        }

        body.sidebar-collapsed .header {
            left: 70px;
        }

        .main-content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 70px;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: #333;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-toggle:hover {
            color: #000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-dropdown .dropdown-menu {
            margin-top: 0.5rem;
            right: 0;
            left: auto;
        }

        @media (max-width: 768px) {
            .header {
                left: 0;
            }
            body.sidebar-collapsed .header {
                left: 250px;
            }
            .main-content {
                margin-left: 0;
            }
            body.sidebar-collapsed .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="profile-dropdown dropdown">
                <button class="btn dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_data['fullname']); ?>&background=random" 
                         alt="Profile" class="rounded-circle" width="32" height="32">
                    <span class="ms-2"><?php echo htmlspecialchars($admin_data['fullname']); ?></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <script>
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        // Dispatch event for other components to react
        window.dispatchEvent(new Event('sidebarToggle'));
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 