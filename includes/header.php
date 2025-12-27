<?php
// Global header template

function getSiteHeader($title = '') {
    $siteName = SITE_NAME;
    $headerTitle = $title ? $title . ' - ' . $siteName : $siteName;
    $isLoggedIn = isLoggedIn();
    $currentRole = getCurrentRole();
    
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$headerTitle</title>
    <link rel="stylesheet" href="{SITE_URL}/css/main.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="{SITE_URL}" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
            <div class="navbar-menu">
HTML;
    
    if ($isLoggedIn) {
        $username = $_SESSION['username'] ?? 'User';
        $headerTitle .= <<<HTML
                <div class="nav-links">
                    <span class="user-greeting">Welcome, $username</span>
                    <span class="role-badge">$currentRole</span>
                    <a href="{SITE_URL}/logout.php" class="btn btn-logout">Logout</a>
                </div>
HTML;
    } else {
        $headerTitle .= <<<HTML
                <div class="nav-links">
                    <a href="{SITE_URL}/login.php" class="btn btn-primary">Login</a>
                    <a href="{SITE_URL}/register.php" class="btn btn-secondary">Register</a>
                </div>
HTML;
    }
    
    $headerTitle .= <<<HTML
            </div>
        </div>
    </nav>
HTML;
    
    return str_replace('{SITE_URL}', SITE_URL, $headerTitle);
}

/**
 * Get site footer
 */
function getSiteFooter() {
    $year = date('Y');
    $siteName = SITE_NAME;
    
    return <<<HTML
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>$siteName</h3>
                    <p>Connecting East African talent with real opportunities.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{SITE_URL}#how-it-works">How It Works</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: hello@zanahustle.com</p>
                    <p>Phone: +255 123 456 789</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; $year $siteName. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="{SITE_URL}/js/script.js"></script>
</body>
</html>
HTML;
    
    return str_replace('{SITE_URL}', SITE_URL, $this);
}

?>
