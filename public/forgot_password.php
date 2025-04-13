<?php
require_once '../includes/config.php';
require '../vendor/autoload.php'; // We'll install PHPMailer via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Tafadhali ingiza barua pepe / Please enter your email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Barua pepe si sahihi / Invalid email format";
    } else {
        try {
            // Check if email exists in database with proper join to get first_name
            $stmt = $connection->prepare("
                SELECT u.id, f.first_name 
                FROM users u 
                LEFT JOIN farmers f ON f.user_id = u.id 
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store token in database
                $stmt = $connection->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
                $stmt->execute([$token, $expires, $email]);

                // Configure PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->SMTPDebug = 2; // Enable verbose debug output
                    $mail->Debugoutput = 'html';
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'amcosmanagementsystem@gmail.com';
                    $mail->Password = 'nzsd vzbs jpqu mqdr';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Set timeout
                    $mail->Timeout = 30;
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // Recipients
                    $mail->setFrom('amcosmanagementsystem@gmail.com', 'AMCOS Management System');
                    $mail->addAddress($email);

                    // Content
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/amcos_management_system/public/reset_password.php?token=" . $token;
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset Your Password - AMCOS Management System';
                    
                    // Use username if first_name is not available
                    $greeting = $user['first_name'] ? htmlspecialchars($user['first_name']) : "User";
                    
                    $mail->Body = "
                        <h2>Password Reset Request</h2>
                        <p>Hello {$greeting},</p>
                        <p>You have requested to reset your password. Click the link below to proceed:</p>
                        <p><a href='{$reset_link}'>{$reset_link}</a></p>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't request this, please ignore this email.</p>
                        <br>
                        <p>Best regards,<br>AMCOS Management System</p>
                    ";

                    if (!$mail->send()) {
                        throw new Exception("Mailer Error: " . $mail->ErrorInfo);
                    }
                    
                    $success = "Maelekezo yametumwa kwenye barua pepe yako / Reset instructions have been sent to your email";
                    
                } catch (Exception $e) {
                    error_log("Mail error: " . $e->getMessage());
                    $error = "Imeshindwa kutuma barua pepe. Tafadhali jaribu tena baadae / Failed to send email. Error details: " . $e->getMessage();
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = "Ikiwa barua pepe ipo, maelekezo yatatumwa / If the email exists, reset instructions will be sent";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = "Kuna hitilafu ya mfumo. Tafadhali jaribu tena / System error. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AMCOS Management System</title>
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
        .forgot-password-container {
            max-width: 450px;
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
        .form-control {
            height: 46px;
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
        }
        .btn-reset {
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
        .btn-reset:hover {
            background: linear-gradient(135deg, #1976d2 0%, #1156a4 100%);
            color: white;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-login a {
            color: #1e88e5;
            text-decoration: none;
        }
        .back-to-login a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-text {
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lock me-2"></i>Forgot Password</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <p class="form-text">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>

                <form method="POST" action="">
                    <div class="form-group">
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               placeholder="Enter your email address"
                               required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-reset">
                        Reset Password
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="login.php">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 