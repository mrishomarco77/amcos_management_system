<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - AMCOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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

        .top-nav {
            background: #2c3e50;
            padding: 10px 20px;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            height: 60px;
        }

        @media (max-width: 768px) {
            .top-nav {
                left: 0;
            }
        }

        .top-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: flex-end;
        }

        .top-nav .nav-links a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .top-nav .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .content-wrapper {
            margin-top: 80px;
        }

        .password-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .password-requirements ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Top Navigation Bar -->
    <div class="top-nav">
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="available_inputs.php">Available Inputs</a>
            <a href="feedback.php">Feedback</a>
            <a href="notifications.php">Notifications</a>
            <a href="change_password.php">Change Password</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="password-card">
                            <h2 class="mb-4">Change Password</h2>

                            <?php if (isset($_SESSION['password_success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php 
                                    echo $_SESSION['password_success'];
                                    unset($_SESSION['password_success']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['password_error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php 
                                    echo $_SESSION['password_error'];
                                    unset($_SESSION['password_error']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="update_password.php" method="POST" id="changePasswordForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>

                                <div class="password-requirements mb-4">
                                    <p class="mb-2">Password Requirements:</p>
                                    <ul>
                                        <li>At least 8 characters long</li>
                                        <li>Contains at least one uppercase letter</li>
                                        <li>Contains at least one lowercase letter</li>
                                        <li>Contains at least one number</li>
                                        <li>Contains at least one special character</li>
                                    </ul>
                                </div>

                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirm password do not match!');
                return;
            }

            // Password validation regex
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            
            if (!passwordRegex.test(newPassword)) {
                e.preventDefault();
                alert('Password does not meet the requirements!');
                return;
            }
        });
    </script>
</body>
</html> 