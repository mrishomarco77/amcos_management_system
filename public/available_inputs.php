<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Inputs - AMCOS Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #1b5e20;
            --secondary-color: #2e7d32;
            --accent-color: #43a047;
            --light-bg: #f8f9fa;
            --border-color: #e0e0e0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
            background-color: var(--light-bg);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        .top-nav {
            background: var(--primary-color);
            padding: 15px 20px;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .top-nav {
                left: 0;
            }
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .page-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .input-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .input-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .input-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }

        .input-details {
            padding: 1.5rem;
        }

        .input-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .input-meta {
            background: var(--light-bg);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .input-meta p {
            margin-bottom: 5px;
            color: #555;
        }

        .input-meta i {
            color: var(--secondary-color);
            width: 20px;
        }

        .input-description {
            color: #4a5568;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .input-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .request-btn {
            background-color: var(--accent-color);
            border: none;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .request-btn:hover {
            background-color: var(--secondary-color);
            transform: translateX(5px);
        }

        .request-btn i {
            transition: transform 0.3s;
        }

        .request-btn:hover i {
            transform: translateX(5px);
        }

        .season-info {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .season-info i {
            color: #ff9800;
            margin-right: 10px;
        }

        .input-status {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #4caf50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .input-status.low-stock {
            background: #ff9800;
        }

        .input-status.out-of-stock {
            background: #f44336;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Top Navigation Bar -->
    <div class="top-nav">
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="available_inputs.php"><i class="fas fa-box"></i> Farm Inputs</a>
            <a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a>
            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="page-header">
                    <h2><i class="fas fa-boxes"></i> Available Farm Inputs</h2>
                    <p class="text-muted">Essential inputs for cotton farming - 2024 Season</p>
                </div>

                <div class="season-info">
                    <i class="fas fa-calendar-alt"></i>
                    <strong>Current Cotton Season:</strong> Applications for farm inputs are now open. Early requests are recommended to ensure availability.
                </div>

                <div class="row g-4">
                    <!-- Spraying Pump -->
                    <div class="col-md-4">
                        <div class="input-card">
                            <div class="input-status">In Stock</div>
                            <img src="../uploads/inputs/sprayer.jpg" alt="Knapsack Sprayer" class="input-image">
                            <div class="input-details">
                                <h4 class="input-title">Professional Knapsack Sprayer</h4>
                                <div class="input-meta">
                                    <p><i class="fas fa-box"></i> Unit: Per piece</p>
                                    <p><i class="fas fa-info-circle"></i> Capacity: 16 Liters</p>
                                    <p><i class="fas fa-check-circle"></i> Warranty: 1 Year</p>
                                </div>
                                <p class="input-description">Heavy-duty knapsack sprayer designed for cotton pest control. Features adjustable nozzle, pressure control, and ergonomic design for comfortable use.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="input-price">120,000 TZS</span>
                                    <button class="btn btn-primary request-btn" onclick="requestInput(1)">
                                        Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pesticides -->
                    <div class="col-md-4">
                        <div class="input-card">
                            <div class="input-status">In Stock</div>
                            <img src="../uploads/inputs/pesticide.jpg" alt="Cotton Pesticide" class="input-image">
                            <div class="input-details">
                                <h4 class="input-title">Cotton Protection Pesticide</h4>
                                <div class="input-meta">
                                    <p><i class="fas fa-flask"></i> Unit: Per liter</p>
                                    <p><i class="fas fa-seedling"></i> Coverage: 2L/hectare</p>
                                    <p><i class="fas fa-shield-alt"></i> Safety Period: 14 days</p>
                                </div>
                                <p class="input-description">Specialized pesticide formulated for cotton pest control. Effective against bollworms, aphids, and other common cotton pests. Includes safety equipment.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="input-price">25,000 TZS/L</span>
                                    <button class="btn btn-primary request-btn" onclick="requestInput(2)">
                                        Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cotton Seeds -->
                    <div class="col-md-4">
                        <div class="input-card">
                            <div class="input-status low-stock">Limited Stock</div>
                            <img src="../uploads/inputs/cotton-seeds.jpg" alt="Cotton Seeds" class="input-image">
                            <div class="input-details">
                                <h4 class="input-title">UK91 Cotton Seeds</h4>
                                <div class="input-meta">
                                    <p><i class="fas fa-weight"></i> Unit: Per kg</p>
                                    <p><i class="fas fa-leaf"></i> Required: 10kg/hectare</p>
                                    <p><i class="fas fa-percentage"></i> Germination Rate: 95%</p>
                                </div>
                                <p class="input-description">Premium quality UK91 cotton seeds. Drought-resistant variety with high yield potential. Certified and tested for Tanzania's climate.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="input-price">15,000 TZS/kg</span>
                                    <button class="btn btn-primary request-btn" onclick="requestInput(3)">
                                        Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fertilizers -->
                    <div class="col-md-4">
                        <div class="input-card">
                            <div class="input-status">In Stock</div>
                            <img src="../uploads/inputs/fertilizer.jpg" alt="Cotton Fertilizer" class="input-image">
                            <div class="input-details">
                                <h4 class="input-title">Cotton NPK Fertilizer</h4>
                                <div class="input-meta">
                                    <p><i class="fas fa-box"></i> Unit: 50kg bag</p>
                                    <p><i class="fas fa-calculator"></i> Required: 2 bags/hectare</p>
                                    <p><i class="fas fa-info-circle"></i> NPK Ratio: 20-10-10</p>
                                </div>
                                <p class="input-description">Balanced NPK fertilizer optimized for cotton growth stages. Enhances root development, flowering, and boll formation.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="input-price">75,000 TZS/bag</span>
                                    <button class="btn btn-primary request-btn" onclick="requestInput(4)">
                                        Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hoes -->
                    <div class="col-md-4">
                        <div class="input-card">
                            <div class="input-status">In Stock</div>
                            <img src="../uploads/inputs/hoe.jpg" alt="Farming Hoe" class="input-image">
                            <div class="input-details">
                                <h4 class="input-title">Professional Farming Hoe</h4>
                                <div class="input-meta">
                                    <p><i class="fas fa-tools"></i> Unit: Per piece</p>
                                    <p><i class="fas fa-star"></i> Quality: Premium Steel</p>
                                    <p><i class="fas fa-ruler"></i> Handle Length: 4 ft</p>
                                </div>
                                <p class="input-description">Heavy-duty farming hoe with hardwood handle. Ideal for cotton field preparation and weed control. Durable steel head with optimal weight balance.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="input-price">12,000 TZS</span>
                                    <button class="btn btn-primary request-btn" onclick="requestInput(5)">
                                        Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function requestInput(inputId) {
            if (confirm('Would you like to request this input? Our team will review your request and contact you.')) {
                fetch('request_input.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        input_id: inputId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Your request has been submitted successfully! Our team will review it shortly.');
                    } else {
                        alert(data.message || 'There was an error processing your request. Please try again.');
                    }
                })
                .catch(error => {
                    alert('There was an error processing your request. Please try again.');
                });
            }
        }
    </script>
</body>
</html> 