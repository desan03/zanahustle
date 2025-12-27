<?php
/**
 * Error Handling and Logging
 * Logs errors to file instead of displaying them to users
 */

// Define error log file
define('ERROR_LOG_FILE', __DIR__ . '/../../logs/error.log');

/**
 * Create logs directory if it doesn't exist
 */
function ensureLogDirectory() {
    $logDir = dirname(ERROR_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
}

/**
 * Log an error message
 */
function logError($message, $severity = 'ERROR') {
    ensureLogDirectory();
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user = $_SESSION['user_id'] ?? 'UNKNOWN';
    
    $logMessage = "[{$timestamp}] [{$severity}] User: {$user} | IP: {$ip} | {$message}\n";
    
    error_log($logMessage, 3, ERROR_LOG_FILE);
}

/**
 * Log an exception
 */
function logException($exception) {
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    
    logError("Exception: {$message} in {$file}:{$line}", 'EXCEPTION');
}

/**
 * Set up global error handler
 */
function setupErrorHandling() {
    // Custom error handler
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        logError("PHP Error ({$errno}): {$errstr} in {$errfile}:{$errline}", 'PHP_ERROR');
        
        // Don't show technical details to users
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            return false; // Use default PHP handler in debug mode
        }
        
        // In production, show generic message
        if ($errno !== E_WARNING && $errno !== E_NOTICE) {
            http_response_code(500);
            die('<h1>An error occurred. Please try again later.</h1>');
        }
        
        return true; // Suppress default handler
    });
    
    // Custom exception handler
    set_exception_handler(function($exception) {
        logException($exception);
        
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            throw $exception;
        }
        
        http_response_code(500);
        die('<h1>An error occurred. Please try again later.</h1>');
    });
}

/**
 * Shutdown handler to catch fatal errors
 */
function handleShutdown() {
    $error = error_get_last();
    if ($error !== null) {
        logError("Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}", 'FATAL');
    }
}

register_shutdown_function('handleShutdown');

// Initialize error handling
setupErrorHandling();
