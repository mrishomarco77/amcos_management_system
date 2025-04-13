<?php
require_once 'session.php';
require_once 'db_connect.php';

// Get admin details
$admin_id = $_SESSION['admin_id'];
try {
    $stmt = $connection->prepare("SELECT fullname, profile_pic FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    
    if ($admin && !empty($admin['profile_pic']) && file_exists("profileimages/".$admin['profile_pic'])) {
        $admin_name = $admin['fullname'];
    } else {
        $admin_name = 'Admin';
    }
} catch (PDOException $e) {
    error_log("Error fetching admin details: " . $e->getMessage());
    $admin_name = 'Admin';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        .user-profile-img {
            width: 35px;
            height: 35px;
            overflow: hidden;
            border-radius: 50%;
        }

        .user-profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .dropdown-item i {
            width: 20px;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="profile-dropdown">
                <button class="btn dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-profile-img me-2">
                        <?php
                        if (isset($_SESSION['admin_id'])) {
                            $admin_id = $_SESSION['admin_id'];
                            try {
                                $stmt = $connection->prepare("SELECT fullname, profile_pic FROM admin WHERE id = ?");
                                $stmt->execute([$admin_id]);
                                $admin = $stmt->fetch();
                                
                                if ($admin && !empty($admin['profile_pic']) && file_exists("profileimages/".$admin['profile_pic'])) {
                                    echo '<img src="profileimages/'.$admin['profile_pic'].'" alt="Profile">';
                                } else {
                                    echo '<img src="../dist/img/default-avatar.png" alt="Default Profile">';
                                }
                                
                                $admin_name = $admin ? $admin['fullname'] : 'Admin';
                            } catch (PDOException $e) {
                                error_log("Error fetching admin details: " . $e->getMessage());
                                $admin_name = 'Admin';
                            }
                        }
                        ?>
                    </div>
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($admin_name); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="change-password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <script>
    function toggleSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        window.dispatchEvent(new Event('sidebarToggle'));
    }

    // Initialize Bootstrap dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
    </script>
</body>
</html> 