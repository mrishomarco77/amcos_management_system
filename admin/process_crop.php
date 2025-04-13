<?php
session_start();
require_once 'includes/db_connect.php';

function handleImageUpload($image_file) {
    $target_dir = "images/";
    $file_extension = strtolower(pathinfo($image_file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Check if image file is a actual image or fake image
    $check = getimagesize($image_file["tmp_name"]);
    if($check === false) {
        throw new Exception("File is not an image.");
    }

    // Check file size (5MB max)
    if ($image_file["size"] > 5000000) {
        throw new Exception("File is too large. Maximum size is 5MB.");
    }

    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        throw new Exception("Only JPG, JPEG & PNG files are allowed.");
    }

    // Move uploaded file
    if (!move_uploaded_file($image_file["tmp_name"], $target_file)) {
        throw new Exception("Failed to upload image.");
    }

    return $new_filename;
}

// Add Crop
if (isset($_POST['add_crop'])) {
    try {
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_filename = handleImageUpload($_FILES['image']);
        }

        $stmt = $connection->prepare("
            INSERT INTO crops (name, description, price_per_kg, status, image) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price_per_kg'],
            $_POST['status'],
            $image_filename
        ]);

        $_SESSION['success'] = "Crop added successfully";
    } catch (Exception $e) {
        error_log("Error adding crop: " . $e->getMessage());
        $_SESSION['error'] = "Error adding crop: " . $e->getMessage();
    }

    header("Location: crops.php");
    exit();
}

// Edit Crop
if (isset($_POST['edit_crop'])) {
    try {
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_filename = handleImageUpload($_FILES['image']);
            
            // Get old image to delete
            $stmt = $connection->prepare("SELECT image FROM crops WHERE id = ?");
            $stmt->execute([$_POST['crop_id']]);
            $old_image = $stmt->fetchColumn();
            
            // Delete old image if exists
            if ($old_image && file_exists("images/" . $old_image)) {
                unlink("images/" . $old_image);
            }
            
            // Update with new image
            $stmt = $connection->prepare("
                UPDATE crops 
                SET name = ?, 
                    description = ?, 
                    price_per_kg = ?, 
                    status = ?,
                    image = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price_per_kg'],
                $_POST['status'],
                $image_filename,
                $_POST['crop_id']
            ]);
        } else {
            // Update without changing image
            $stmt = $connection->prepare("
                UPDATE crops 
                SET name = ?, 
                    description = ?, 
                    price_per_kg = ?, 
                    status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price_per_kg'],
                $_POST['status'],
                $_POST['crop_id']
            ]);
        }

        $_SESSION['success'] = "Crop updated successfully";
    } catch (Exception $e) {
        error_log("Error updating crop: " . $e->getMessage());
        $_SESSION['error'] = "Error updating crop: " . $e->getMessage();
    }

    header("Location: crops.php");
    exit();
}

// If no valid action is specified, redirect back to crops page
header("Location: crops.php");
exit(); 