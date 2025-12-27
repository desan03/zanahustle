<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

// Ensure user can be a client
if (!canAccessRole('client')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}

setUserRole('client');

$userId = getCurrentUserId();
$userName = $_SESSION['username'] ?? 'User';

// Get filter parameters
$searchSkills = trim($_GET['skills'] ?? '');
$minRate = floatval($_GET['min_rate'] ?? 0);
$maxRate = floatval($_GET['max_rate'] ?? 999999);
$minRating = floatval($_GET['min_rating'] ?? 0);
$sortBy = $_GET['sort'] ?? 'rating';

// Build query with filters
$query = "SELECT u.*, up.* FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id
          WHERE u.can_be_freelancer = TRUE AND u.is_active = TRUE";

$params = [];
$types = "";

// Add skill filter
if (!empty($searchSkills)) {
    $query .= " AND (up.skills LIKE ?)";
    $types .= "s";
    $searchTerm = "%" . $searchSkills . "%";
    $params[] = $searchTerm;
}

// Add hourly rate filter
$query .= " AND (up.hourly_rate BETWEEN ? AND ?)";
$types .= "dd";
$params[] = $minRate;
$params[] = $maxRate;

// Add rating filter
$query .= " AND (up.rating >= ?)";
$types .= "d";
$params[] = $minRating;

// Add sorting
switch ($sortBy) {
    case 'rate_low':
        $query .= " ORDER BY up.hourly_rate ASC";
        break;
    case 'rate_high':
        $query .= " ORDER BY up.hourly_rate DESC";
        break;
    case 'newest':
        $query .= " ORDER BY u.created_at DESC";
        break;
    case 'rating':
    default:
        $query .= " ORDER BY up.rating DESC, up.reviews_count DESC";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$freelancers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Freelancers - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
    <style>
        .browse-container {
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: calc(100vh - 80px);
        }

        .browse-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .browse-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .browse-header p {
            font-size: 16px;
            color: #64748b;
        }

        .filter-sidebar {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .filter-section {
            margin-bottom: 25px;
        }

        .filter-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13px;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .filter-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .filter-button {
            width: 100%;
            padding: 10px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-button:hover {
            background: #4f46e5;
        }

        .clear-filters {
            width: 100%;
            padding: 10px;
            background: #f1f5f9;
            color: #0f172a;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .clear-filters:hover {
            background: #e2e8f0;
        }

        .browse-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }

        .freelancers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .freelancer-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .freelancer-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .freelancer-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .freelancer-avatar {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .freelancer-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .freelancer-username {
            font-size: 12px;
            opacity: 0.9;
        }

        .freelancer-body {
            padding: 20px;
            flex: 1;
        }

        .rating-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .stars {
            color: #f59e0b;
            font-size: 14px;
        }

        .review-count {
            font-size: 12px;
            color: #64748b;
        }

        .hourly-rate {
            font-size: 18px;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 15px;
        }

        .hourly-rate-usd {
            font-size: 12px;
            color: #64748b;
        }

        .skills-section {
            margin-bottom: 15px;
        }

        .skills-label {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .skill-tag {
            background: #f1f5f9;
            color: #0f172a;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .verification-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .completed-jobs {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 15px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary-small {
            background: #6366f1;
            color: white;
        }

        .btn-primary-small:hover {
            background: #4f46e5;
        }

        .btn-secondary-small {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary-small:hover {
            background: #e2e8f0;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .results-info {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .browse-content {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                position: relative;
                top: 0;
            }

            .freelancers-grid {
                grid-template-columns: 1fr;
            }

            .browse-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="browse-container">
        <div class="container">
            <div class="browse-header">
                <h1>Find Talented Freelancers</h1>
                <p>Browse and filter skilled freelancers ready to work on your projects</p>
            </div>

            <div class="browse-content">
                <!-- Filter Sidebar -->
                <aside class="filter-sidebar">
                    <form method="GET" action="">
                        <div class="filter-section">
                            <h3>Search Skills</h3>
                            <div class="filter-group">
                                <input type="text" name="skills" class="filter-input" placeholder="e.g., Web Design, PHP..." value="<?php echo htmlspecialchars($searchSkills); ?>">
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3>Hourly Rate (<?php echo CURRENCY_SYMBOL; ?>)</h3>
                            <div class="filter-range">
                                <input type="number" name="min_rate" class="filter-input" placeholder="Min" value="<?php echo $minRate > 0 ? $minRate : ''; ?>" step="1000">
                                <input type="number" name="max_rate" class="filter-input" placeholder="Max" value="<?php echo $maxRate < 999999 ? $maxRate : ''; ?>" step="1000">
                            </div>
                            <small style="color: #64748b; font-size: 11px; display: block; margin-top: 5px;">
                                TZS / hour
                            </small>
                        </div>

                        <div class="filter-section">
                            <h3>Minimum Rating</h3>
                            <div class="filter-group">
                                <select name="min_rating" class="filter-select">
                                    <option value="0" <?php echo $minRating == 0 ? 'selected' : ''; ?>>All Ratings</option>
                                    <option value="3" <?php echo $minRating == 3 ? 'selected' : ''; ?>>★★★ and above</option>
                                    <option value="3.5" <?php echo $minRating == 3.5 ? 'selected' : ''; ?>>★★★★ and above</option>
                                    <option value="4.5" <?php echo $minRating == 4.5 ? 'selected' : ''; ?>>★★★★★ (Top Rated)</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3>Sort By</h3>
                            <div class="filter-group">
                                <select name="sort" class="filter-select">
                                    <option value="rating" <?php echo $sortBy == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                    <option value="rate_low" <?php echo $sortBy == 'rate_low' ? 'selected' : ''; ?>>Lowest Rate</option>
                                    <option value="rate_high" <?php echo $sortBy == 'rate_high' ? 'selected' : ''; ?>>Highest Rate</option>
                                    <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="filter-button">Apply Filters</button>
                        <a href="<?php echo SITE_URL; ?>/browse_freelancers.php" class="clear-filters">Clear Filters</a>
                    </form>
                </aside>

                <!-- Freelancers Grid -->
                <div>
                    <div class="results-info">
                        Found <strong><?php echo count($freelancers); ?></strong> freelancer<?php echo count($freelancers) !== 1 ? 's' : ''; ?>
                    </div>

                    <?php if (empty($freelancers)): ?>
                        <div class="empty-state">
                            <h3>No freelancers found</h3>
                            <p>Try adjusting your filters to find more freelancers</p>
                        </div>
                    <?php else: ?>
                        <div class="freelancers-grid">
                            <?php foreach ($freelancers as $freelancer): ?>
                                <div class="freelancer-card">
                                    <div class="freelancer-header">
                                        <div class="freelancer-avatar">
                                            <?php echo strtoupper(substr($freelancer['first_name'] ?? $freelancer['username'], 0, 1)); ?>
                                        </div>
                                        <div class="freelancer-name"><?php echo htmlspecialchars($freelancer['first_name'] ?? 'Freelancer'); ?></div>
                                        <div class="freelancer-username">@<?php echo htmlspecialchars($freelancer['username']); ?></div>
                                    </div>

                                    <div class="freelancer-body">
                                        <?php if ($freelancer['verification_status'] === 'verified'): ?>
                                            <div class="verification-badge">✓ Verified</div>
                                        <?php endif; ?>

                                        <div class="rating-section">
                                            <span class="stars">
                                                <?php 
                                                $rating = $freelancer['rating'] ?? 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo ($i <= $rating) ? '★' : '☆';
                                                }
                                                ?>
                                            </span>
                                            <span class="review-count"><?php echo number_format($freelancer['reviews_count'] ?? 0); ?> reviews</span>
                                        </div>

                                        <div class="hourly-rate">
                                            <?php echo number_format($freelancer['hourly_rate'] ?? 0, 0); ?> <span style="font-size: 12px;"><?php echo CURRENCY_SYMBOL; ?>/h</span>
                                        </div>
                                        <div class="hourly-rate-usd">
                                            ≈ $<?php echo number_format(($freelancer['hourly_rate'] ?? 0) / USD_TO_TZS, 2); ?>/h
                                        </div>

                                        <div class="completed-jobs">
                                            <strong><?php echo $freelancer['completed_projects'] ?? 0; ?></strong> projects completed
                                        </div>

                                        <?php if (!empty($freelancer['skills'])): ?>
                                            <div class="skills-section">
                                                <div class="skills-label">Skills</div>
                                                <div class="skills-list">
                                                    <?php 
                                                    $skills = array_slice(array_map('trim', explode(',', $freelancer['skills'])), 0, 3);
                                                    foreach ($skills as $skill): 
                                                    ?>
                                                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div style="padding: 0 20px 20px;">
                                        <div class="card-actions">
                                            <button class="btn-small btn-primary-small" onclick="contactFreelancer(<?php echo $freelancer['id']; ?>)">Contact</button>
                                            <a href="<?php echo SITE_URL; ?>/freelancer_profile.php?id=<?php echo $freelancer['id']; ?>" class="btn-small btn-secondary-small" style="text-decoration: none; text-align: center;">View Profile</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function contactFreelancer(freelancerId) {
            alert('Messaging feature coming soon! Freelancer ID: ' + freelancerId);
            // window.location.href = '<?php echo SITE_URL; ?>/send_message.php?to=' + freelancerId;
        }
    </script>
</body>
</html>
