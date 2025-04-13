<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'amcos_db');

// Application Configuration
define('SITE_URL', 'http://localhost/amcos_management_system');
define('ADMIN_URL', SITE_URL . '/admin');
define('PUBLIC_URL', SITE_URL . '/public');

// Upload Directories
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/amcos_management_system/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Session Configuration
define('SESSION_NAME', 'AMCOS_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security Configuration
define('HASH_COST', 10); // Password hashing cost
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 300); // 5 minutes

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/error.log');

// Time Zone
date_default_timezone_set('Africa/Dar_es_Salaam'); 