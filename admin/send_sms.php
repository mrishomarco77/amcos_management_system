<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $recipients = $_POST['recipients'];
        $message = $_POST['message'];
        
        // Get farmers' phone numbers
        $phone_numbers = array();
        
        if (in_array('all', $recipients)) {
            // Send to all farmers
            $query = "SELECT phone_number FROM farmers WHERE status = 'active'";
            $result = $con->query($query);
            while ($row = $result->fetch_assoc()) {
                $phone_numbers[] = $row['phone_number'];
            }
        } else {
            // Send to selected farmers
            $query = "SELECT phone_number FROM farmers WHERE id IN (" . implode(',', array_map('intval', $recipients)) . ")";
            $result = $con->query($query);
            while ($row = $result->fetch_assoc()) {
                $phone_numbers[] = $row['phone_number'];
            }
        }

        // Initialize Africa's Talking Gateway
        $username = "YOUR_USERNAME";  // Replace with your Africa's Talking username
        $apiKey = "YOUR_API_KEY";     // Replace with your Africa's Talking API key
        
        // Include Africa's Talking Gateway
        require_once('../vendor/africastalking/africastalking-php/src/AfricasTalking.php');
        use AfricasTalking\SDK\AfricasTalking;

        // Initialize the SDK
        $AT = new AfricasTalking($username, $apiKey);

        // Get the SMS service
        $sms = $AT->sms();

        // Send message to all recipients
        try {
            $result = $sms->send([
                'to' => implode(',', $phone_numbers),
                'message' => $message,
                'from' => 'AMCOS'  // Replace with your sender ID
            ]);

            // Log the message in database
            $stmt = $con->prepare("INSERT INTO sms_logs (recipients, message, status, response) VALUES (?, ?, 'sent', ?)");
            $recipients_str = implode(',', $phone_numbers);
            $response_str = json_encode($result);
            $stmt->bind_param("sss", $recipients_str, $message, $response_str);
            $stmt->execute();

            $response['success'] = true;
            $response['message'] = 'Messages sent successfully';
        } catch (Exception $e) {
            throw new Exception("Error sending SMS: " . $e->getMessage());
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Log the error
    error_log("SMS Error: " . $e->getMessage());
    
    // Log failed attempt in database
    $stmt = $con->prepare("INSERT INTO sms_logs (recipients, message, status, response) VALUES (?, ?, 'failed', ?)");
    $recipients_str = isset($phone_numbers) ? implode(',', $phone_numbers) : '';
    $error_str = $e->getMessage();
    $stmt->bind_param("sss", $recipients_str, $message, $error_str);
    $stmt->execute();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 