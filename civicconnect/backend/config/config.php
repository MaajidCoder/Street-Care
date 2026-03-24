<?php
// backend/config/config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'civicconnect');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_PORT', 3307); // Custom XAMPP port based on user history

// Site configuration
define('SITE_NAME', 'Street Care');
// Adjust this URL based on where the project is actually hosted
define('SITE_URL', 'http://localhost/street%20care/civicconnect/');
define('UPLOAD_PATH', __DIR__ . '/../../assets/uploads/');

// Create uploads directory if not exists
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
} // Ensure assets/uploads exists
if (!file_exists(__DIR__ . '/../../assets/uploads/issues/')) {
    mkdir(__DIR__ . '/../../assets/uploads/issues/', 0777, true);
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from output (logs only)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../php_error.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// CORS Headers for API access from Frontend
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// End of file
