<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

try {
    // Create buyers table
    $sql = "CREATE TABLE IF NOT EXISTS `buyers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `company_name` varchar(255) NOT NULL,
        `contact_person` varchar(255) NOT NULL,
        `phone_number` varchar(20) NOT NULL,
        `email` varchar(255) DEFAULT NULL,
        `address` text,
        `location` varchar(255) DEFAULT NULL,
        `registration_number` varchar(50) DEFAULT NULL,
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `company_name` (`company_name`),
        UNIQUE KEY `registration_number` (`registration_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $con->query($sql);
    echo "Buyers table created successfully!<br>";

    // Create sales table
    $sql = "CREATE TABLE IF NOT EXISTS `sales` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `buyer_id` int(11) NOT NULL,
        `weight_kg` decimal(10,2) NOT NULL,
        `price_per_kg` decimal(10,2) NOT NULL,
        `total_amount` decimal(10,2) NOT NULL,
        `sale_date` date NOT NULL,
        `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
        `notes` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `buyer_id` (`buyer_id`),
        CONSTRAINT `sales_buyer_fk` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $con->query($sql);
    echo "Sales table created successfully!<br>";

    // Create payments table
    $sql = "CREATE TABLE IF NOT EXISTS `payments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `payment_type` enum('farmer','buyer') NOT NULL,
        `farmer_id` int(11) DEFAULT NULL,
        `buyer_id` int(11) DEFAULT NULL,
        `purchase_id` int(11) DEFAULT NULL,
        `sale_id` int(11) DEFAULT NULL,
        `amount` decimal(10,2) NOT NULL,
        `payment_method` enum('cash','bank_transfer','mobile_money') NOT NULL,
        `reference_number` varchar(50) DEFAULT NULL,
        `payment_date` date NOT NULL,
        `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
        `notes` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `farmer_id` (`farmer_id`),
        KEY `buyer_id` (`buyer_id`),
        KEY `purchase_id` (`purchase_id`),
        KEY `sale_id` (`sale_id`),
        CONSTRAINT `payments_farmer_fk` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE SET NULL,
        CONSTRAINT `payments_buyer_fk` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE SET NULL,
        CONSTRAINT `payments_purchase_fk` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL,
        CONSTRAINT `payments_sale_fk` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $con->query($sql);
    echo "Payments table created successfully!<br>";

    // Create purchases table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `purchases` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `farmer_id` int(11) NOT NULL,
        `weight_kg` decimal(10,2) NOT NULL,
        `price_per_kg` decimal(10,2) NOT NULL,
        `total_amount` decimal(10,2) NOT NULL,
        `purchase_date` date NOT NULL,
        `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
        `notes` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `farmer_id` (`farmer_id`),
        CONSTRAINT `purchases_farmer_fk` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $con->query($sql);
    echo "Purchases table created successfully!<br>";

    echo "<br>All tables created successfully! You can now go back to the application.";
    
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?> 