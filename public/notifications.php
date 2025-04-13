<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - AMCOS</title>
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

        .notification-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.2s;
        }

        .notification-card:hover {
            transform: translateX(5px);
        }

        .notification-card.unread {
            border-left: 4px solid #007bff;
        }

        .notification-card .notification-time {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .notification-card .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
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
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h2>Notifications</h2>
                        <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                            Mark All as Read
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <?php
                        require_once '../includes/config.php';
                        
                        // Get user's notifications
                        $user_id = $_SESSION['user_id'];
                        $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($notification = $result->fetch_assoc()) {
                                $unread_class = $notification['is_read'] ? '' : 'unread';
                                ?>
                                <div class="notification-card <?php echo $unread_class; ?> p-3" 
                                     data-id="<?php echo $notification['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="notification-title">
                                                <?php echo htmlspecialchars($notification['title']); ?>
                                            </div>
                                            <p class="mb-1">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <span class="notification-time">
                                                <?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>
                                            </span>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <button class="btn btn-sm btn-light" 
                                                    onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                Mark as Read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="alert alert-info">No notifications to display.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsRead(notificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notification = document.querySelector(`[data-id="${notificationId}"]`);
                    notification.classList.remove('unread');
                    const button = notification.querySelector('button');
                    if (button) button.remove();
                }
            });
        }

        function markAllAsRead() {
            fetch('mark_all_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-card').forEach(card => {
                        card.classList.remove('unread');
                        const button = card.querySelector('button');
                        if (button) button.remove();
                    });
                }
            });
        }
    </script>
</body>
</html> 