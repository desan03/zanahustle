<?php
require_once 'config.php';
require_once 'includes/auth.php';

$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZanaHustle - Freelancing Platform for East Africa</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
</head>
<body class="home-page">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
            <div class="navbar-menu">
                <?php if ($isLoggedIn): ?>
                    <div class="nav-links">
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                        <a href="<?php echo SITE_URL; ?>/role_select.php" class="btn btn-primary">Dashboard</a>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-logout">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="nav-links">
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary">Login</a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-secondary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Connecting East African Talent with Real Opportunities</h1>
                <p>ZanaHustle is the leading freelancing platform built for Tanzania and East Africa. Find work, hire talent, and grow your business.</p>
                <div class="hero-ctas">
                    <?php if (!$isLoggedIn): ?>
                        <a href="<?php echo SITE_URL; ?>/register.php?role=freelancer" class="btn btn-primary btn-lg">Find Work</a>
                        <a href="<?php echo SITE_URL; ?>/register.php?role=client" class="btn btn-secondary btn-lg">Hire Talent</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/role_select.php" class="btn btn-primary btn-lg">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-illustration">
                    <span>💼 👨‍💻 📱</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="section-header">
                <h2>About ZanaHustle</h2>
                <p>Your gateway to global opportunities, right here in East Africa</p>
            </div>

            <div class="about-content">
                <div class="about-card">
                    <div class="about-icon">🌍</div>
                    <h3>Global Platform, Local Touch</h3>
                    <p>We understand the East African market. Built by professionals who know the region, for professionals who want to grow here.</p>
                </div>

                <div class="about-card">
                    <div class="about-icon">💰</div>
                    <h3>Mobile Money Ready</h3>
                    <p>Pay and get paid easily through M-Pesa, Airtel Money, and other local payment methods. No complicated international transfers.</p>
                </div>

                <div class="about-card">
                    <div class="about-icon">🤝</div>
                    <h3>Trust & Security</h3>
                    <p>Secure transactions, verified users, and transparent ratings. Your protection is our priority.</p>
                </div>

                <div class="about-card">
                    <div class="about-icon">🚀</div>
                    <h3>Grow Your Career</h3>
                    <p>Whether you're looking to hire or earn, ZanaHustle is your platform to scale and succeed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>Get started in four simple steps</p>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create an Account</h3>
                    <p>Sign up with your email and password. It takes less than 2 minutes.</p>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Choose Your Role</h3>
                    <p>Decide if you want to be a Client posting jobs or a Freelancer finding work.</p>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Post or Apply</h3>
                    <p>Post a job or submit proposals for jobs that match your skills.</p>
                </div>

                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Work & Earn</h3>
                    <p>Complete projects, deliver quality work, and build your reputation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- For Clients Section -->
    <section class="for-clients">
        <div class="container">
            <div class="section-content">
                <div class="section-text">
                    <h2>For Clients</h2>
                    <p>Find talented East African professionals ready to bring your projects to life.</p>
                    <ul class="feature-list">
                        <li>✓ Post detailed job descriptions</li>
                        <li>✓ Review proposals from qualified freelancers</li>
                        <li>✓ Manage projects and track progress</li>
                        <li>✓ Secure payment and escrow protection</li>
                        <li>✓ Rate and review completed work</li>
                    </ul>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Hire Now</a>
                </div>
                <div class="section-image">
                    <div class="illustration">💼</div>
                </div>
            </div>
        </div>
    </section>

    <!-- For Freelancers Section -->
    <section class="for-freelancers">
        <div class="container">
            <div class="section-content">
                <div class="section-image">
                    <div class="illustration">👨‍💻</div>
                </div>
                <div class="section-text">
                    <h2>For Freelancers</h2>
                    <p>Showcase your skills and earn money from projects that fit your expertise.</p>
                    <ul class="feature-list">
                        <li>✓ Browse jobs from local and international clients</li>
                        <li>✓ Submit competitive proposals</li>
                        <li>✓ Build your portfolio</li>
                        <li>✓ Earn and withdraw via mobile money</li>
                        <li>✓ Get top-rated badges and grow your reputation</li>
                    </ul>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Start Earning</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Users Say</h2>
                <p>Success stories from our community</p>
            </div>

            <div class="testimonial-cards">
                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"ZanaHustle helped me find my first freelance client. Now I'm earning consistently from home!"</p>
                    <div class="testimonial-author">
                        <div class="avatar">👩</div>
                        <div>
                            <p class="author-name">Sarah M.</p>
                            <p class="author-title">Freelance Designer, Nairobi</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"I hired a developer through ZanaHustle and the project exceeded my expectations. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <div class="avatar">👨</div>
                        <div>
                            <p class="author-name">James K.</p>
                            <p class="author-title">Startup Founder, Dar es Salaam</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"The payment system is so easy with M-Pesa. This is the platform East Africa needed!"</p>
                    <div class="testimonial-author">
                        <div class="avatar">👩‍💼</div>
                        <div>
                            <p class="author-name">Grace T.</p>
                            <p class="author-title">Content Writer, Uganda</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Found amazing talent for my marketing campaign. ZanaHustle made the entire process smooth and transparent."</p>
                    <div class="testimonial-author">
                        <div class="avatar">👨‍🔧</div>
                        <div>
                            <p class="author-name">David O.</p>
                            <p class="author-title">Business Owner, Rwanda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners">
        <div class="container">
            <div class="section-header">
                <h2>Our Partners</h2>
                <p>Trusted by leading organizations</p>
            </div>

            <div class="partner-logos">
                <div class="partner-logo">
                    <div class="logo-box">🏦 Equity Bank</div>
                </div>
                <div class="partner-logo">
                    <div class="logo-box">📱 Safaricom</div>
                </div>
                <div class="partner-logo">
                    <div class="logo-box">🌐 Google Africa</div>
                </div>
                <div class="partner-logo">
                    <div class="logo-box">💻 Microsoft</div>
                </div>
                <div class="partner-logo">
                    <div class="logo-box">🚀 Andela</div>
                </div>
                <div class="partner-logo">
                    <div class="logo-box">🎯 IHub</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of East African professionals on ZanaHustle today</p>
            <?php if (!$isLoggedIn): ?>
                <div class="cta-buttons">
                    <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary btn-lg">Create Account</a>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-secondary btn-lg">Already have an account?</a>
                </div>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/role_select.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ZanaHustle</h3>
                    <p>Connecting East African talent with real opportunities.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#about">About Us</a></li>
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
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: <a href="mailto:hello@zanahustle.com">hello@zanahustle.com</a></p>
                    <p>Phone: +255 123 456 789</p>
                    <p>Follow us: 
                        <a href="#">Twitter</a> | 
                        <a href="#">Facebook</a> | 
                        <a href="#">LinkedIn</a>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ZanaHustle. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
</body>
</html>
