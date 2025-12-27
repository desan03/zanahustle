<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyRequestCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $primaryRole = trim($_POST['primary_role'] ?? 'freelancer');
        
        // Validation
        if (empty($username)) {
            $error = 'Username is required';
        } elseif (empty($email)) {
            $error = 'Email is required';
        } elseif (empty($password)) {
            $error = 'Password is required';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } elseif (!in_array($primaryRole, ['client', 'freelancer'])) {
            $error = 'Please select a valid role';
        } else {
            $result = registerUser($username, $email, $password, $firstName, $lastName, $primaryRole);
            if ($result['success']) {
                $success = 'Registration successful! Redirecting to login...';
                header('Refresh: 2; url=' . SITE_URL . '/login.php');
            } else {
                $error = $result['error'];
            }
        }
    }
}

// Initialize CSRF token if not exists
initializeCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ZanaHustle</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
</head>
<body class="auth-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-box">
            <h1>Create Account</h1>
            <p class="auth-subtitle">Join ZanaHustle and start earning or hiring</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <?php echo generateCsrfField(); ?>
                
                <div class="form-group">
                    <label>Select Your Primary Role</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="primary_role" value="freelancer" checked>
                            <span class="role-card">
                                <span class="role-icon">💼</span>
                                <span class="role-title">Freelancer</span>
                                <span class="role-desc">Sell your skills & services</span>
                            </span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="primary_role" value="client">
                            <span class="role-card">
                                <span class="role-icon">👔</span>
                                <span class="role-title">Client</span>
                                <span class="role-desc">Hire talented freelancers</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="John" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="johndoe" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="john@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min 8 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ZanaHustle. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
</body>
</html>
