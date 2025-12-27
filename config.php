<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'abc');

// Site Configuration
define('SITE_NAME', 'ZanaHustle');
define('SITE_URL', 'http://localhost/ZanaHustle');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Currency Configuration
define('CURRENCY', 'TZS'); // Tanzania Shilling
define('CURRENCY_SYMBOL', 'TZS');
define('MIN_BUDGET', 10000); // Minimum TZS 10,000
define('USD_TO_TZS', 2450); // Exchange rate (1 USD = 2450 TZS) - adjust as needed

// Session timeout (30 minutes in seconds)
define('SESSION_TIMEOUT', 1800);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
