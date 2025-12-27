<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireGuest();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            // Get user's primary role and redirect accordingly
            $user = getCurrentUser();
            $primaryRole = $user['primary_role'] ?? 'freelancer';
            
            if ($primaryRole === 'client') {
                header('Location: ' . SITE_URL . '/client_dashboard.php');
            } else {
                header('Location: ' . SITE_URL . '/freelancer_dashboard.php');
            }
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZanaHustle</title>
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
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Login to your ZanaHustle account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php">Register here</a></p>
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
