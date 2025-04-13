<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - AMCOS' : 'AMCOS Management System'; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/public/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/assets/css/custom.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">AMCOS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/public/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/farmers.php">Farmers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/market_prices.php">Market Prices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/public/announcement.php">Announcements</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/public/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/public/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/public/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4">
        <?php if (isset($alertMessage)): ?>
            <div class="alert alert-<?php echo $alertType ?? 'info'; ?> alert-dismissible fade show">
                <?php echo $alertMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php include $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> AMCOS Management System</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/public/about.php" class="text-decoration-none text-dark me-3">About</a>
                    <a href="/public/contact.php" class="text-decoration-none text-dark me-3">Contact</a>
                    <a href="/public/privacy.php" class="text-decoration-none text-dark">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="/public/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/public/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/public/assets/js/custom.js"></script>
</body>
</html> 