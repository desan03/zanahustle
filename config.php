<?php
/**
 * ZanaHustle Configuration
 * Secure configuration with environment variable support
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'abc');

// ============================================
// SITE CONFIGURATION
// ============================================
define('SITE_NAME', 'ZanaHustle');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/ZanaHustle');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('TEMP_DIR', sys_get_temp_dir());

// ============================================
// SECURITY CONFIGURATION
// ============================================
define('DEBUG_MODE', (getenv('DEBUG_MODE') === 'true')); // Set to false in production
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_SECURE', getenv('SESSION_SECURE') === 'true'); // HTTPS only cookies
define('SESSION_HTTPONLY', true); // No JavaScript access to cookies

// ============================================
// CURRENCY CONFIGURATION
// ============================================
define('CURRENCY', 'TZS'); // Tanzania Shilling
define('CURRENCY_SYMBOL', 'TZS');
define('MIN_BUDGET', 10000); // Minimum TZS 10,000
define('USD_TO_TZS', 2450); // Exchange rate (1 USD = 2450 TZS) - adjust as needed

// ============================================
// DATABASE CONNECTION
// ============================================
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // Log error but don't expose details to user
        error_log("Database connection failed: " . $conn->connect_error);
        
        if (DEBUG_MODE) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            die("A database error occurred. Please try again later.");
        }
    }
    
    $conn->set_charset("utf8mb4");
    
    // Set strict SQL mode for security
    $conn->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    
} catch (Exception $e) {
    error_log("Database exception: " . $e->getMessage());
    
    if (DEBUG_MODE) {
        die("Database connection error: " . $e->getMessage());
    } else {
        die("A database error occurred. Please try again later.");
    }
}

// ============================================
// SESSION CONFIGURATION
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    
    if (SESSION_SECURE) {
        ini_set('session.cookie_secure', 1);
    }
    
    // Set session timeout to configured value
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    
    // Start the session
    session_start();
    
    // Verify session hasn't been hijacked
    if (isset($_SESSION['user_agent'])) {
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            session_destroy();
            die("Session security validation failed.");
        }
    }
}

// ============================================
// SECURITY HEADERS
// ============================================
require_once __DIR__ . '/includes/security/headers.php';

// ============================================
// SECURITY UTILITIES
// ============================================
require_once __DIR__ . '/includes/security/csrf.php';
require_once __DIR__ . '/includes/security/validation.php';
require_once __DIR__ . '/includes/security/rate_limit.php';
require_once __DIR__ . '/includes/security/error_handler.php';
?>
