<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

$username = $_SESSION['username'] ?? 'User';
$canBeClient = $_SESSION['can_be_client'] ?? false;
$canBeFreelancer = $_SESSION['can_be_freelancer'] ?? false;

// If user doesn't have any role enabled, logout
if (!$canBeClient && !$canBeFreelancer) {
    logoutUser();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Role - ZanaHustle</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
</head>
<body class="role-select-page">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
            <div class="navbar-menu">
                <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="role-select-container">
        <div class="role-select-box">
            <div class="role-select-header">
                <h1>Welcome Back, <?php echo htmlspecialchars($username); ?>! 👋</h1>
                <p class="subtitle">What would you like to do today?</p>
                <div class="header-accent"></div>
            </div>

            <div class="role-cards-grid">
                <?php if ($canBeClient): ?>
                    <div class="role-card-item client-card">
                        <div class="card-header">
                            <div class="role-icon-large">💼</div>
                            <div class="role-badge">Client</div>
                        </div>
                        <div class="card-content">
                            <h2>Post Jobs & Hire</h2>
                            <p>Connect with talented freelancers, post projects, review proposals, and manage contracts</p>
                            <div class="card-features">
                                <span class="feature">✓ Browse Services</span>
                                <span class="feature">✓ Post Jobs</span>
                                <span class="feature">✓ Track Projects</span>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo SITE_URL; ?>/client_dashboard.php">
                            <input type="hidden" name="set_role" value="client">
                            <button type="submit" class="btn btn-primary btn-block btn-large">Get Talent Now</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if ($canBeFreelancer): ?>
                    <div class="role-card-item freelancer-card">
                        <div class="card-header">
                            <div class="role-icon-large">🎯</div>
                            <div class="role-badge">Freelancer</div>
                        </div>
                        <div class="card-content">
                            <h2>Publish & Earn</h2>
                            <p>Publish your services, browse opportunities, submit proposals, and grow your income</p>
                            <div class="card-features">
                                <span class="feature">✓ Publish Services</span>
                                <span class="feature">✓ Find Work</span>
                                <span class="feature">✓ Earn Money</span>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo SITE_URL; ?>/freelancer_dashboard.php">
                            <input type="hidden" name="set_role" value="freelancer">
                            <button type="submit" class="btn btn-primary btn-block btn-large">Start Earning</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="role-switcher-info">
                <p>💡 <strong>Tip:</strong> You can switch between roles anytime from your dashboard menu</p>
            </div>
        </div>
    </div>

    <style>
        .role-select-container {
            min-height: calc(100vh - 80px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
        }

        .role-select-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .role-select-container::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        .role-select-box {
            position: relative;
            z-index: 1;
            max-width: 900px;
            width: 100%;
        }

        .role-select-header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .role-select-header h1 {
            font-size: 40px;
            margin: 0 0 10px 0;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .role-select-header .subtitle {
            font-size: 18px;
            opacity: 0.95;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .header-accent {
            height: 4px;
            width: 100px;
            background: white;
            margin: 20px auto 0;
            border-radius: 2px;
        }

        .role-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .role-card-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .role-card-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        }

        .role-card-item.client-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        }

        .role-card-item.freelancer-card .card-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .card-header {
            padding: 40px 20px;
            color: white;
            text-align: center;
            position: relative;
        }

        .role-icon-large {
            font-size: 64px;
            margin-bottom: 15px;
            display: block;
        }

        .role-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-content {
            flex: 1;
            padding: 30px 25px;
        }

        .card-content h2 {
            margin: 0 0 12px 0;
            font-size: 24px;
            color: #0f172a;
        }

        .card-content p {
            margin: 0 0 25px 0;
            color: #64748b;
            line-height: 1.6;
            font-size: 15px;
        }

        .card-features {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 25px;
        }

        .feature {
            display: flex;
            align-items: center;
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }

        .feature::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            background: #6366f1;
            border-radius: 50%;
            margin-right: 10px;
        }

        .btn-large {
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .client-card .btn-large {
            background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        }

        .client-card .btn-large:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #4c51bf 100%);
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .freelancer-card .btn-large {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .freelancer-card .btn-large:hover {
            background: linear-gradient(135deg, #f5576c 0%, #e63b63 100%);
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(245, 87, 108, 0.3);
        }

        .role-switcher-info {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.95);
            font-size: 14px;
            backdrop-filter: blur(10px);
        }

        .role-switcher-info p {
            margin: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        @media (max-width: 768px) {
            .role-cards-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .role-select-header h1 {
                font-size: 28px;
            }

            .role-select-header .subtitle {
                font-size: 16px;
            }

            .card-content {
                padding: 25px 20px;
            }

            .card-content h2 {
                font-size: 20px;
            }

            .role-select-container::before,
            .role-select-container::after {
                width: 250px;
                height: 250px;
            }
        }
    </style>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ZanaHustle. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
</body>
</html>
