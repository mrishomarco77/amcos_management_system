<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_POST['update_settings'])) {
    try {
        // Handle file uploads first
        $logo_path = null;
        $favicon_path = null;

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $logo_info = getimagesize($_FILES['logo']['tmp_name']);
            if ($logo_info !== false) {
                $logo_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logo_filename = 'logo_' . time() . '.' . $logo_extension;
                $logo_destination = 'uploads/' . $logo_filename;

                // Create uploads directory if it doesn't exist
                if (!file_exists('uploads/')) {
                    mkdir('uploads/', 0777, true);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_destination)) {
                    $logo_path = $logo_destination;

                    // Delete old logo if exists
                    $stmt = $connection->prepare("SELECT logo FROM settings LIMIT 1");
                    $stmt->execute();
                    $old_logo = $stmt->fetchColumn();
                    if ($old_logo && file_exists($old_logo)) {
                        unlink($old_logo);
                    }
                }
            }
        }

        // Handle favicon upload
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
            $favicon_info = getimagesize($_FILES['favicon']['tmp_name']);
            if ($favicon_info !== false) {
                $favicon_extension = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
                $favicon_filename = 'favicon_' . time() . '.' . $favicon_extension;
                $favicon_destination = 'uploads/' . $favicon_filename;

                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $favicon_destination)) {
                    $favicon_path = $favicon_destination;

                    // Delete old favicon if exists
                    $stmt = $connection->prepare("SELECT favicon FROM settings LIMIT 1");
                    $stmt->execute();
                    $old_favicon = $stmt->fetchColumn();
                    if ($old_favicon && file_exists($old_favicon)) {
                        unlink($old_favicon);
                    }
                }
            }
        }

        // Check if settings record exists
        $stmt = $connection->query("SELECT COUNT(*) FROM settings");
        $settings_exist = $stmt->fetchColumn() > 0;

        if ($settings_exist) {
            // Update existing settings
            $sql = "UPDATE settings SET 
                    system_name = ?,
                    organization_name = ?,
                    address = ?,
                    phone = ?,
                    email = ?,
                    currency = ?,
                    timezone = ?";
            
            $params = [
                $_POST['system_name'],
                $_POST['organization_name'],
                $_POST['address'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['currency'],
                $_POST['timezone']
            ];

            // Add logo and favicon to update if they were uploaded
            if ($logo_path) {
                $sql .= ", logo = ?";
                $params[] = $logo_path;
            }
            if ($favicon_path) {
                $sql .= ", favicon = ?";
                $params[] = $favicon_path;
            }

            $sql .= " WHERE id = 1";

            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert new settings
            $sql = "INSERT INTO settings (
                    system_name, organization_name, address, phone, email, 
                    currency, timezone, logo, favicon
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([
                $_POST['system_name'],
                $_POST['organization_name'],
                $_POST['address'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['currency'],
                $_POST['timezone'],
                $logo_path,
                $favicon_path
            ]);
        }

        $_SESSION['success'] = "Settings updated successfully";
    } catch (Exception $e) {
        error_log("Error updating settings: " . $e->getMessage());
        $_SESSION['error'] = "Error updating settings: " . $e->getMessage();
    }

    header("Location: settings.php");
    exit();
}

// If no valid action is specified, redirect back to settings page
header("Location: settings.php");
exit(); 