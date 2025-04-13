<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("
    SELECT u.*, f.id as farmer_id, f.profile_picture 
    FROM users u 
    LEFT JOIN farmers f ON f.user_id = u.id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate file type and size
        if (!in_array($file['type'], $allowedTypes)) {
            $message = "Only JPG, PNG and GIF files are allowed.";
            $message_type = "danger";
        } elseif ($file['size'] > $maxSize) {
            $message = "File size must be less than 5MB.";
            $message_type = "danger";
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = '../uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update database
                $stmt = $connection->prepare("
                    UPDATE farmers 
                    SET profile_picture = ?, 
                        first_login = 0 
                    WHERE user_id = ?
                ");
                
                if ($stmt->execute([$filename, $user_id])) {
                    $message = "Profile picture uploaded successfully!";
                    $message_type = "success";
                    
                    // Redirect to dashboard after 2 seconds
                    header("refresh:2;url=farmer_dashboard.php");
                } else {
                    $message = "Failed to update database.";
                    $message_type = "danger";
                }
            } else {
                $message = "Failed to upload file.";
                $message_type = "danger";
            }
        }
    } else {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Photo - AMCOS Management System</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .upload-container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .card-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        .card-body {
            padding: 30px;
        }
        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 3px solid #1e88e5;
            position: relative;
        }
        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-preview .default-avatar {
            width: 100%;
            height: 100%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-preview .default-avatar i {
            font-size: 64px;
            color: #adb5bd;
        }
        .custom-file-upload {
            border: 2px dashed #1e88e5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .custom-file-upload:hover {
            border-color: #1565c0;
            background-color: #f8f9fa;
        }
        .custom-file-upload i {
            font-size: 24px;
            color: #1e88e5;
            margin-bottom: 10px;
        }
        .btn-upload {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            border: none;
            height: 46px;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-upload:hover {
            background: linear-gradient(135deg, #1976d2 0%, #1156a4 100%);
            color: white;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        #imagePreview {
            max-width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-circle me-2"></i>Upload Profile Photo</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="profile-preview" id="profilePreview">
                    <div class="default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <label for="profile_picture" class="custom-file-upload">
                        <i class="fas fa-cloud-upload-alt d-block"></i>
                        <span>Click to select or drag and drop your photo</span>
                        <input type="file" 
                               id="profile_picture" 
                               name="profile_picture" 
                               accept="image/*" 
                               style="display: none;"
                               required>
                    </label>
                    <button type="submit" class="btn btn-upload">
                        Upload Photo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 