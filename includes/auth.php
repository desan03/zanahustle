<?php
// Authentication Helper Functions

/**
 * Hash a password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against its hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Register a new user
 */
function registerUser($username, $email, $password, $firstName = '', $lastName = '', $primaryRole = 'freelancer') {
    global $conn;
    
    // Check if username already exists
    $checkQuery = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'error' => 'Username already exists'];
    }
    
    $passwordHash = hashPassword($password);
    
    $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, can_be_client, can_be_freelancer, primary_role) 
              VALUES (?, ?, ?, ?, ?, TRUE, TRUE, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $username, $email, $passwordHash, $firstName, $lastName, $primaryRole);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // Create user profile
        $profileQuery = "INSERT INTO user_profiles (user_id) VALUES (?)";
        $profileStmt = $conn->prepare($profileQuery);
        $profileStmt->bind_param("i", $userId);
        $profileStmt->execute();
        
        return ['success' => true, 'user_id' => $userId];
    } else {
        return ['success' => false, 'error' => 'Registration failed: ' . $conn->error];
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    global $conn;
    
    $query = "SELECT id, username, password_hash, first_name, can_be_client, can_be_freelancer 
              FROM users WHERE username = ? AND is_active = TRUE";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }
    
    $user = $result->fetch_assoc();
    
    if (!verifyPassword($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid username or password'];
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['can_be_client'] = (bool)$user['can_be_client'];
    $_SESSION['can_be_freelancer'] = (bool)$user['can_be_freelancer'];
    $_SESSION['last_activity'] = time();
    
    return ['success' => true, 'user_id' => $user['id']];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    $userId = getCurrentUserId();
    
    $query = "SELECT u.*, up.skills, up.hourly_rate, up.total_earnings, up.rating 
              FROM users u 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              WHERE u.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logoutUser();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Require guest - redirect if logged in
 */
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: ' . SITE_URL . '/role_select.php');
        exit;
    }
}

/**
 * Set user role
 */
function setUserRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!in_array($role, ['client', 'freelancer'])) {
        return false;
    }
    
    $_SESSION['current_role'] = $role;
    return true;
}

/**
 * Get current role
 */
function getCurrentRole() {
    return $_SESSION['current_role'] ?? null;
}

/**
 * Check if user can access role
 */
function canAccessRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if ($role === 'client') {
        return $_SESSION['can_be_client'] ?? false;
    } elseif ($role === 'freelancer') {
        return $_SESSION['can_be_freelancer'] ?? false;
    }
    
    return false;
}

?>
