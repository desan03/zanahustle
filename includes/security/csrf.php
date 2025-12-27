<?php
/**
 * CSRF Token Protection
 * Implements CSRF token generation and validation
 */

/**
 * Initialize CSRF token in session if not exists
 */
function initializeCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get current CSRF token
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        initializeCsrfToken();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF token HTML input field
 */
function generateCsrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken()) . '">';
}

/**
 * Verify CSRF token from POST/PUT/DELETE requests
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token from request
 * Checks POST data first, then JSON body
 * 
 * @return bool True if valid, false otherwise
 */
function verifyRequestCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    
    // If no POST token, check JSON body
    if (empty($token)) {
        $json = json_decode(file_get_contents('php://input'), true);
        $token = $json['csrf_token'] ?? '';
    }
    
    return verifyCsrfToken($token);
}

/**
 * Validate CSRF token or return error
 * Use in protected endpoints
 */
function validateCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!verifyRequestCsrf()) {
            http_response_code(403);
            die(json_encode(['error' => 'Invalid CSRF token']));
        }
    }
}
