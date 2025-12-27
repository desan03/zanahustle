<?php
/**
 * Rate Limiting
 * Prevents brute force attacks on login and other sensitive endpoints
 */

/**
 * Check if user has exceeded rate limit
 * Uses session to track failed attempts
 */
function checkRateLimit($action = 'login', $maxAttempts = 5, $windowSeconds = 600) {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    if (!isset($_SESSION['rate_limits'][$action])) {
        $_SESSION['rate_limits'][$action] = [];
    }
    
    $now = time();
    $attempts = $_SESSION['rate_limits'][$action];
    
    // Remove old attempts outside the time window
    $attempts = array_filter($attempts, function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });
    
    $_SESSION['rate_limits'][$action] = array_values($attempts);
    
    if (count($attempts) >= $maxAttempts) {
        return false; // Rate limit exceeded
    }
    
    return true; // Within rate limit
}

/**
 * Record a failed attempt for rate limiting
 */
function recordFailedAttempt($action = 'login') {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    if (!isset($_SESSION['rate_limits'][$action])) {
        $_SESSION['rate_limits'][$action] = [];
    }
    
    $_SESSION['rate_limits'][$action][] = time();
}

/**
 * Clear rate limit for an action
 */
function clearRateLimit($action = 'login') {
    if (isset($_SESSION['rate_limits'][$action])) {
        unset($_SESSION['rate_limits'][$action]);
    }
}

/**
 * Get remaining attempts before rate limit
 */
function getRemainingAttempts($action = 'login', $maxAttempts = 5, $windowSeconds = 600) {
    if (!isset($_SESSION['rate_limits'][$action])) {
        return $maxAttempts;
    }
    
    $now = time();
    $attempts = $_SESSION['rate_limits'][$action];
    
    // Remove old attempts
    $attempts = array_filter($attempts, function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });
    
    return max(0, $maxAttempts - count($attempts));
}
