<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();

$freelancerId = intval($_GET['id'] ?? 0);

if ($freelancerId <= 0) {
    header('Location: ' . SITE_URL . '/browse_freelancers.php');
    exit;
}

// Fetch freelancer profile
$query = "SELECT u.*, up.* FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id
          WHERE u.id = ? AND u.can_be_freelancer = TRUE AND u.is_active = TRUE";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $freelancerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/browse_freelancers.php');
    exit;
}

$freelancer = $result->fetch_assoc();

// Fetch freelancer's reviews
$reviewsQuery = "SELECT r.*, u.first_name, u.username FROM reviews r 
                 JOIN users u ON r.reviewer_id = u.id
                 WHERE r.reviewee_id = ? ORDER BY r.created_at DESC LIMIT 10";

$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $freelancerId);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();
$reviews = $reviewsResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($freelancer['first_name'] ?? 'Freelancer'); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
    <style>
        .profile-hero {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            border: 3px solid white;
        }

        .profile-name {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .profile-username {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .stat-box {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
        }

        .profile-container {
            max-width: 1000px;
            margin: -40px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-top: 40px;
        }

        .profile-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .about-text {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .skills-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .skill-item {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 6px;
            color: #0f172a;
            font-weight: 500;
        }

        .portfolio-link {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 24px;
            background: #6366f1;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .portfolio-link:hover {
            background: #4f46e5;
        }

        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .review-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #6366f1;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-author {
            font-weight: 600;
            color: #0f172a;
        }

        .review-rating {
            color: #f59e0b;
        }

        .review-text {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .sidebar-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .rate-box {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .rate-amount {
            font-size: 28px;
            font-weight: 600;
            color: #6366f1;
        }

        .rate-label {
            font-size: 12px;
            color: #64748b;
        }

        .rate-usd {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        .rating-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .stars-large {
            font-size: 28px;
            color: #f59e0b;
            margin-bottom: 5px;
        }

        .rating-text {
            font-size: 14px;
            color: #64748b;
        }

        .action-button {
            width: 100%;
            padding: 12px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 10px;
        }

        .action-button:hover {
            background: #4f46e5;
        }

        .no-reviews {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }

            .skills-grid {
                grid-template-columns: 1fr;
            }

            .profile-stats {
                gap: 20px;
            }

            .profile-name {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-hero">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($freelancer['first_name'] ?? $freelancer['username'], 0, 1)); ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($freelancer['first_name'] . ' ' . ($freelancer['last_name'] ?? '')); ?></div>
        <div class="profile-username">@<?php echo htmlspecialchars($freelancer['username']); ?></div>

        <div class="profile-stats">
            <div class="stat-box">
                <div class="stat-value"><?php echo number_format($freelancer['completed_projects'] ?? 0); ?></div>
                <div class="stat-label">Projects Completed</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">
                    <?php 
                    $rating = $freelancer['rating'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= $rating) ? '★' : '☆';
                    }
                    ?>
                </div>
                <div class="stat-label">Rating</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo number_format($freelancer['reviews_count'] ?? 0); ?></div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-content">
            <!-- Main Content -->
            <div>
                <!-- About Section -->
                <div class="profile-section">
                    <h2 class="section-title">About</h2>
                    <div class="about-text">
                        <?php echo htmlspecialchars($freelancer['bio'] ?? 'No bio provided'); ?>
                    </div>
                    <?php if ($freelancer['country'] || $freelancer['city']): ?>
                        <p style="color: #64748b; font-size: 14px;">
                            <strong>Location:</strong> <?php echo htmlspecialchars(($freelancer['city'] ?? '') . ', ' . ($freelancer['country'] ?? '')); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Skills Section -->
                <?php if (!empty($freelancer['skills'])): ?>
                    <div class="profile-section">
                        <h2 class="section-title">Skills</h2>
                        <div class="skills-grid">
                            <?php 
                            $skills = array_map('trim', explode(',', $freelancer['skills']));
                            foreach ($skills as $skill): 
                            ?>
                                <div class="skill-item">✓ <?php echo htmlspecialchars($skill); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Portfolio Section -->
                <?php if (!empty($freelancer['portfolio_url'])): ?>
                    <div class="profile-section">
                        <h2 class="section-title">Portfolio</h2>
                        <p style="color: #64748b; margin-bottom: 15px;">Check out my portfolio to see my previous work</p>
                        <a href="<?php echo htmlspecialchars($freelancer['portfolio_url']); ?>" target="_blank" class="portfolio-link">View Portfolio ↗</a>
                    </div>
                <?php endif; ?>

                <!-- Reviews Section -->
                <div class="profile-section">
                    <h2 class="section-title">Reviews (<?php echo count($reviews); ?>)</h2>
                    <?php if (empty($reviews)): ?>
                        <div class="no-reviews">
                            <p>No reviews yet. Be the first to work with this freelancer!</p>
                        </div>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-author"><?php echo htmlspecialchars($review['first_name'] ?? $review['username']); ?></div>
                                        <div class="review-rating">
                                            <?php 
                                            $rating = $review['rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $rating) ? '★' : '☆';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="review-text"><?php echo htmlspecialchars($review['comment'] ?? ''); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Hourly Rate -->
                <div class="sidebar-card">
                    <div class="rate-box">
                        <div class="rate-amount"><?php echo number_format($freelancer['hourly_rate'] ?? 0, 0); ?> <span style="font-size: 14px;"><?php echo CURRENCY_SYMBOL; ?></span></div>
                        <div class="rate-label">Hourly Rate</div>
                        <div class="rate-usd">≈ $<?php echo number_format(($freelancer['hourly_rate'] ?? 0) / USD_TO_TZS, 2); ?></div>
                    </div>

                    <div class="rating-display">
                        <div class="stars-large">
                            <?php 
                            $rating = $freelancer['rating'] ?? 0;
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $rating) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <div class="rating-text">
                            <?php echo number_format($rating, 1); ?> / 5.0<br>
                            (<?php echo number_format($freelancer['reviews_count'] ?? 0); ?> reviews)
                        </div>
                    </div>

                    <?php if ($freelancer['verification_status'] === 'verified'): ?>
                        <div style="background: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; text-align: center; font-weight: 600; font-size: 12px; margin-bottom: 15px;">
                            ✓ Verified Freelancer
                        </div>
                    <?php endif; ?>

                    <button class="action-button" onclick="contactFreelancer(<?php echo $freelancer['id']; ?>)">Contact & Hire</button>
                    <button class="action-button" style="background: #f1f5f9; color: #0f172a;" onclick="sendMessage(<?php echo $freelancer['id']; ?>)">Send Message</button>

                    <a href="<?php echo SITE_URL; ?>/browse_freelancers.php" style="display: block; text-align: center; color: #6366f1; text-decoration: none; margin-top: 15px; font-weight: 600;">← Back to Browse</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function contactFreelancer(freelancerId) {
            alert('Hire & contract feature coming soon! Freelancer ID: ' + freelancerId);
        }

        function sendMessage(freelancerId) {
            alert('Messaging feature coming soon! Freelancer ID: ' + freelancerId);
        }
    </script>
</body>
</html>
