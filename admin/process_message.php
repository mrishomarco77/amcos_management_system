<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Handle Send Message
if (isset($_POST['send_message'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $message_type = trim($_POST['message_type']);
    $input_id = ($message_type === 'input_availability') ? intval($_POST['input_id']) : null;
    $sent_to = trim($_POST['sent_to']);
    $schedule_time = !empty($_POST['schedule_time']) ? $_POST['schedule_time'] : null;
    $status = !empty($schedule_time) ? 'scheduled' : 'sent';
    $admin_id = $_SESSION['aid'];

    // Validate required fields
    if (empty($subject) || empty($message) || empty($message_type) || empty($sent_to)) {
        $_SESSION['error'] = "All required fields must be filled";
        header('location: farming_inputs.php');
        exit();
    }

    try {
        // Start transaction
        $con->begin_transaction();

        // Insert the main message
        $msg_stmt = $con->prepare("INSERT INTO farmer_messages (subject, message, message_type, input_id, sent_to, status, schedule_time, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $msg_stmt->bind_param("sssisssi", $subject, $message, $message_type, $input_id, $sent_to, $status, $schedule_time, $admin_id);
        
        if (!$msg_stmt->execute()) {
            throw new Exception("Failed to create message");
        }
        
        $message_id = $msg_stmt->insert_id;

        // Get target farmers based on send_to type
        $farmer_query = "SELECT id FROM farmers WHERE status = 'active'";
        $params = [];
        $types = "";

        if ($sent_to === 'specific' && !empty($_POST['farmer_ids'])) {
            $farmer_ids = $_POST['farmer_ids'];
            $placeholders = str_repeat('?,', count($farmer_ids) - 1) . '?';
            $farmer_query .= " AND id IN ($placeholders)";
            $params = $farmer_ids;
            $types = str_repeat('i', count($farmer_ids));
        } elseif ($sent_to === 'by_location' && !empty($_POST['locations'])) {
            $locations = $_POST['locations'];
            $placeholders = str_repeat('?,', count($locations) - 1) . '?';
            $farmer_query .= " AND location IN ($placeholders)";
            $params = $locations;
            $types = str_repeat('s', count($locations));
        }

        // Prepare and execute farmer query
        if (!empty($params)) {
            $farmer_stmt = $con->prepare($farmer_query);
            $farmer_stmt->bind_param($types, ...$params);
        } else {
            $farmer_stmt = $con->prepare($farmer_query);
        }
        
        $farmer_stmt->execute();
        $farmer_result = $farmer_stmt->get_result();

        // Insert message recipients
        $recipient_stmt = $con->prepare("INSERT INTO message_recipients (message_id, farmer_id, read_status, created_at) VALUES (?, ?, 'unread', NOW())");
        $recipient_count = 0;

        while ($farmer = $farmer_result->fetch_assoc()) {
            $recipient_stmt->bind_param("ii", $message_id, $farmer['id']);
            if ($recipient_stmt->execute()) {
                $recipient_count++;
            }
        }

        // Commit transaction
        $con->commit();

        $action = ($status === 'scheduled') ? 'scheduled' : 'sent';
        $_SESSION['success'] = "Message successfully $action to $recipient_count farmer(s)";

        // Close statements
        $msg_stmt->close();
        $farmer_stmt->close();
        $recipient_stmt->close();

    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        $_SESSION['error'] = "Error sending message: " . $e->getMessage();
    }

    header('location: farming_inputs.php');
    exit();
}

// If no valid action is specified, redirect back to the inputs page
header('location: farming_inputs.php');
exit();
?> 