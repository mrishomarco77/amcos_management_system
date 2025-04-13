<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

try {
    // Create crops table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS crops (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Crops table created successfully<br>";

    // Create farmers table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS farmers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone_number VARCHAR(20),
            address TEXT,
            farm_size DECIMAL(10,2),
            cotton_farm_size DECIMAL(10,2),
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (user_id)
        )
    ");
    echo "Farmers table created successfully<br>";

    // Create farmer_crops table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS farmer_crops (
            id INT PRIMARY KEY AUTO_INCREMENT,
            farmer_id INT NOT NULL,
            crop_id INT NOT NULL,
            farm_size DECIMAL(10,2),
            planting_date DATE,
            expected_harvest_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmers(id),
            FOREIGN KEY (crop_id) REFERENCES crops(id)
        )
    ");
    echo "Farmer crops table created successfully<br>";

    // Create market_prices table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS market_prices (
            id INT PRIMARY KEY AUTO_INCREMENT,
            crop_id INT NOT NULL,
            price_per_kg DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (crop_id) REFERENCES crops(id)
        )
    ");
    echo "Market prices table created successfully<br>";

    // Create cotton_records table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS cotton_records (
            id INT PRIMARY KEY AUTO_INCREMENT,
            farmer_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            price_per_kg DECIMAL(10,2) NOT NULL,
            harvest_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmers(id)
        )
    ");
    echo "Cotton records table created successfully<br>";

    // Create farm_inputs table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS farm_inputs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            unit VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Farm inputs table created successfully<br>";

    // Create farmer_inputs table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS farmer_inputs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            farmer_id INT NOT NULL,
            input_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'delivered') DEFAULT 'pending',
            request_date DATE NOT NULL,
            approval_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES farmers(id),
            FOREIGN KEY (input_id) REFERENCES farm_inputs(id)
        )
    ");
    echo "Farmer inputs table created successfully<br>";

    // Create trainings table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS trainings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            time TIME NOT NULL,
            location VARCHAR(200),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Trainings table created successfully<br>";

    // Check if crops table is empty before inserting
    $stmt = $connection->query("SELECT COUNT(*) FROM crops");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insert initial crops
        $connection->exec("
            INSERT INTO crops (name, description) VALUES
            ('Cotton', 'Cotton crop'),
            ('Maize', 'Maize crop'),
            ('Beans', 'Beans crop')
        ");
        echo "Initial crops added successfully<br>";

        // Insert sample market prices
        $connection->exec("
            INSERT INTO market_prices (crop_id, price_per_kg, date) VALUES
            (1, 2500.00, CURRENT_DATE),
            (2, 1200.00, CURRENT_DATE),
            (3, 2000.00, CURRENT_DATE)
        ");
        echo "Sample market prices added successfully<br>";

        // Insert sample farm inputs
        $connection->exec("
            INSERT INTO farm_inputs (name, description, unit) VALUES
            ('Seeds', 'Cotton seeds', 'kg'),
            ('Fertilizer', 'NPK Fertilizer', 'kg'),
            ('Pesticide', 'Insect control', 'liters')
        ");
        echo "Sample farm inputs added successfully<br>";
    }

    echo "Database setup completed successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 