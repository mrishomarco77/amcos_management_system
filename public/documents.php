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

// Example document categories and their contents
$document_categories = [
    'registration' => [
        'title' => 'Registration Documents',
        'icon' => 'fa-file-contract',
        'documents' => [
            [
                'name' => 'AMCOS Membership Form',
                'type' => 'PDF',
                'size' => '245 KB',
                'date' => '2024-03-15',
                'status' => 'required'
            ],
            [
                'name' => 'Farmer Registration Certificate',
                'type' => 'PDF',
                'size' => '180 KB',
                'date' => '2024-03-10',
                'status' => 'submitted'
            ]
        ]
    ],
    'cotton_farming' => [
        'title' => 'Cotton Farming Documents',
        'icon' => 'fa-seedling',
        'documents' => [
            [
                'name' => 'Cotton Farming Guidelines',
                'type' => 'PDF',
                'size' => '1.2 MB',
                'date' => '2024-02-20',
                'status' => 'available'
            ],
            [
                'name' => 'Pest Management Manual',
                'type' => 'PDF',
                'size' => '850 KB',
                'date' => '2024-02-15',
                'status' => 'available'
            ]
        ]
    ],
    'certificates' => [
        'title' => 'Certificates & Licenses',
        'icon' => 'fa-certificate',
        'documents' => [
            [
                'name' => 'Cotton Quality Certificate',
                'type' => 'PDF',
                'size' => '320 KB',
                'date' => '2024-03-01',
                'status' => 'verified'
            ],
            [
                'name' => 'Training Completion Certificate',
                'type' => 'PDF',
                'size' => '290 KB',
                'date' => '2024-02-28',
                'status' => 'verified'
            ]
        ]
    ],
    'reports' => [
        'title' => 'Reports & Records',
        'icon' => 'fa-chart-bar',
        'documents' => [
            [
                'name' => 'Cotton Production Report 2024',
                'type' => 'PDF',
                'size' => '750 KB',
                'date' => '2024-03-20',
                'status' => 'new'
            ],
            [
                'name' => 'Farm Input Usage Report',
                'type' => 'PDF',
                'size' => '420 KB',
                'date' => '2024-03-18',
                'status' => 'new'
            ]
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - AMCOS Management System</title>
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
        .category-card {
            background: #fff;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .category-header {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        .category-header i {
            font-size: 24px;
            margin-right: 15px;
        }
        .category-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }
        .document-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .document-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.3s ease;
        }
        .document-item:last-child {
            border-bottom: none;
        }
        .document-item:hover {
            background-color: #f8f9fa;
        }
        .document-info {
            display: flex;
            align-items: center;
        }
        .document-icon {
            width: 40px;
            height: 40px;
            background: #e3f2fd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .document-icon i {
            color: #1e88e5;
            font-size: 18px;
        }
        .document-details h5 {
            margin: 0;
            font-size: 1rem;
            color: #2c3e50;
        }
        .document-details p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        .document-actions {
            display: flex;
            gap: 10px;
        }
        .btn-document {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-required {
            background-color: #ff5722;
            color: white;
        }
        .status-submitted {
            background-color: #4caf50;
            color: white;
        }
        .status-verified {
            background-color: #2196f3;
            color: white;
        }
        .status-available {
            background-color: #009688;
            color: white;
        }
        .status-new {
            background-color: #9c27b0;
            color: white;
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
            <h2 class="page-title">Document Center</h2>

            <?php foreach ($document_categories as $category => $data): ?>
            <div class="category-card">
                <div class="category-header">
                    <i class="fas <?php echo $data['icon']; ?>"></i>
                    <h4><?php echo $data['title']; ?></h4>
                </div>
                <ul class="document-list">
                    <?php foreach ($data['documents'] as $doc): ?>
                    <li class="document-item">
                        <div class="document-info">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-details">
                                <h5><?php echo htmlspecialchars($doc['name']); ?></h5>
                                <p>
                                    <span class="text-muted"><?php echo $doc['type']; ?> • <?php echo $doc['size']; ?> • 
                                    Updated: <?php echo date('d M Y', strtotime($doc['date'])); ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="document-actions">
                            <span class="status-badge status-<?php echo $doc['status']; ?>">
                                <?php echo ucfirst($doc['status']); ?>
                            </span>
                            <?php if ($doc['status'] == 'required'): ?>
                            <a href="upload_document.php?type=<?php echo $category; ?>" class="btn-document btn btn-primary">
                                <i class="fas fa-upload"></i> Upload
                            </a>
                            <?php else: ?>
                            <a href="view_document.php?type=<?php echo $category; ?>" class="btn-document btn btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="download_document.php?type=<?php echo $category; ?>" class="btn-document btn btn-success">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 