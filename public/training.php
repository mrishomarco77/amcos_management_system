<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get user and farmer information
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("
    SELECT 
        u.*,
        f.id as farmer_id,
        f.first_name,
        f.last_name,
        f.profile_picture
    FROM users u 
    LEFT JOIN farmers f ON f.user_id = u.id 
    WHERE u.id = ?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get upcoming training sessions (example data - replace with actual database query)
$upcoming_trainings = [
    [
        'title' => 'Modern Cotton Farming Techniques',
        'date' => '2024-04-15',
        'time' => '09:00 AM',
        'location' => 'AMCOS Training Center',
        'trainer' => 'Dr. John Smith',
        'status' => 'upcoming'
    ],
    [
        'title' => 'Pest Management in Cotton',
        'date' => '2024-04-20',
        'time' => '10:00 AM',
        'location' => 'Field Demo Site',
        'trainer' => 'Ms. Sarah Johnson',
        'status' => 'upcoming'
    ]
];

// Get completed training sessions (example data - replace with actual database query)
$completed_trainings = [
    [
        'title' => 'Soil Management',
        'date' => '2024-03-10',
        'certificate' => true,
        'score' => 95,
        'status' => 'completed'
    ],
    [
        'title' => 'Organic Farming Methods',
        'date' => '2024-02-25',
        'certificate' => true,
        'score' => 88,
        'status' => 'completed'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Center - AMCOS Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        .page-title {
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }
        .stats-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .training-card {
            background: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .training-card .card-header {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            padding: 15px 20px;
        }
        .training-card .card-body {
            padding: 20px;
        }
        .training-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .training-info i {
            width: 20px;
            margin-right: 10px;
            color: #1e88e5;
        }
        .badge-upcoming {
            background-color: #2196f3;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .badge-completed {
            background-color: #4caf50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #e9ecef;
            position: relative;
        }
        .progress-circle-inner {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .btn-enroll {
            background-color: #2196f3;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-enroll:hover {
            background-color: #1976d2;
            color: white;
        }
        .certificate-icon {
            color: #ffc107;
            font-size: 24px;
            margin-right: 10px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="page-title">Training Center</h2>

            <!-- Training Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-calendar-check"></i> Completed Trainings</h5>
                        <h3><?php echo count($completed_trainings); ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-calendar"></i> Upcoming Sessions</h5>
                        <h3><?php echo count($upcoming_trainings); ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-certificate"></i> Certificates Earned</h5>
                        <h3>2</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h5><i class="fas fa-star"></i> Average Score</h5>
                        <h3>91.5%</h3>
                    </div>
                </div>
            </div>

            <!-- Upcoming Training Sessions -->
            <h4 class="mb-4">Upcoming Training Sessions</h4>
            <div class="row">
                <?php foreach ($upcoming_trainings as $training): ?>
                <div class="col-md-6">
                    <div class="training-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($training['title']); ?></h5>
                                <span class="badge-upcoming">Upcoming</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="training-info">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo date('d M Y', strtotime($training['date'])); ?></span>
                            </div>
                            <div class="training-info">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($training['time']); ?></span>
                            </div>
                            <div class="training-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($training['location']); ?></span>
                            </div>
                            <div class="training-info">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($training['trainer']); ?></span>
                            </div>
                            <div class="mt-3">
                                <a href="enroll_training.php?id=1" class="btn-enroll">
                                    <i class="fas fa-sign-in-alt"></i> Enroll Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Completed Training Sessions -->
            <h4 class="mb-4 mt-5">Completed Training Sessions</h4>
            <div class="row">
                <?php foreach ($completed_trainings as $training): ?>
                <div class="col-md-6">
                    <div class="training-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($training['title']); ?></h5>
                                <span class="badge-completed">Completed</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="training-info">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Completed on <?php echo date('d M Y', strtotime($training['date'])); ?></span>
                                    </div>
                                    <div class="training-info">
                                        <i class="fas fa-star"></i>
                                        <span>Score: <?php echo $training['score']; ?>%</span>
                                    </div>
                                    <?php if ($training['certificate']): ?>
                                    <div class="training-info">
                                        <i class="fas fa-certificate certificate-icon"></i>
                                        <span>Certificate Earned</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center">
                                    <a href="view_certificate.php?id=1" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> Certificate
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 