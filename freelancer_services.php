<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

// Ensure user is a freelancer
if (!canAccessRole('freelancer')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}

setUserRole('freelancer');

$userId = getCurrentUserId();
$userName = $_SESSION['username'] ?? 'User';

$success = '';
$error = '';

// Handle service creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_service'])) {
    $title = trim($_POST['service_title'] ?? '');
    $description = trim($_POST['service_description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $deliveryTime = intval($_POST['delivery_time'] ?? 0);
    $revisions = intval($_POST['revisions'] ?? 2);
    $features = trim($_POST['features'] ?? '');
    
    if (empty($title)) {
        $error = 'Service title is required';
    } elseif (empty($description)) {
        $error = 'Service description is required';
    } elseif ($price < MIN_BUDGET) {
        $error = 'Minimum price is ' . number_format(MIN_BUDGET, 0) . ' ' . CURRENCY_SYMBOL;
    } elseif ($deliveryTime <= 0) {
        $error = 'Delivery time must be greater than 0';
    } else {
        $stmt = $conn->prepare("INSERT INTO services (freelancer_id, title, description, category, price, delivery_time, revisions, features)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssidis", $userId, $title, $description, $category, $price, $deliveryTime, $revisions, $features);
        
        if ($stmt->execute()) {
            $success = 'Service published successfully!';
        } else {
            $error = 'Error creating service: ' . $conn->error;
        }
    }
}

// Handle service deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    $serviceId = intval($_POST['service_id'] ?? 0);
    $deleteStmt = $conn->prepare("DELETE FROM services WHERE id = ? AND freelancer_id = ?");
    $deleteStmt->bind_param("ii", $serviceId, $userId);
    if ($deleteStmt->execute()) {
        $success = 'Service deleted successfully!';
    } else {
        $error = 'Error deleting service';
    }
}

// Fetch freelancer's services
$servicesQuery = "SELECT * FROM services WHERE freelancer_id = ? ORDER BY created_at DESC";
$servicesStmt = $conn->prepare($servicesQuery);
$servicesStmt->bind_param("i", $userId);
$servicesStmt->execute();
$servicesResult = $servicesStmt->get_result();
$services = $servicesResult->fetch_all(MYSQLI_ASSOC);

// Fetch freelancer analytics
$analyticsQuery = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN so.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN so.status = 'in_progress' THEN 1 ELSE 0 END) as active_orders,
                    SUM(CASE WHEN so.status = 'completed' THEN so.amount ELSE 0 END) as total_earnings
                   FROM service_orders so
                   WHERE so.freelancer_id = ?";
$analyticsStmt = $conn->prepare($analyticsQuery);
$analyticsStmt->bind_param("i", $userId);
$analyticsStmt->execute();
$analytics = $analyticsStmt->get_result()->fetch_assoc();

$userProfile = getCurrentUser();
$rating = $userProfile['rating'] ?? 0;
$completedProjects = $userProfile['completed_projects'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
    <style>
        .services-container {
            padding: 40px 20px;
            background: #f8fafc;
            min-height: calc(100vh - 80px);
        }

        .services-header {
            margin-bottom: 40px;
        }

        .services-header h1 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .analytics-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .analytics-value {
            font-size: 24px;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 5px;
        }

        .analytics-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }

        .services-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .service-form {
            max-width: 600px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-submit {
            padding: 12px 30px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #4f46e5;
        }

        .services-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .service-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }

        .service-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .service-title {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .service-category {
            display: inline-block;
            background: #e2e8f0;
            color: #64748b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .service-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .service-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        .info-item {
            text-align: center;
        }

        .info-value {
            font-weight: 600;
            color: #6366f1;
        }

        .info-label {
            color: #64748b;
            font-size: 11px;
        }

        .service-price {
            font-size: 20px;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 15px;
        }

        .service-price-usd {
            font-size: 12px;
            color: #64748b;
        }

        .service-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #6366f1;
            color: white;
        }

        .btn-edit:hover {
            background: #4f46e5;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .services-list {
                grid-template-columns: 1fr;
            }

            .analytics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="services-container">
        <div class="container">
            <div class="services-header">
                <h1>My Services & Analytics</h1>
            </div>

            <!-- Analytics Dashboard -->
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="analytics-value"><?php echo ($analytics['total_orders'] ?? 0); ?></div>
                    <div class="analytics-label">Total Orders</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value"><?php echo ($analytics['completed_orders'] ?? 0); ?></div>
                    <div class="analytics-label">Completed</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value"><?php echo ($analytics['active_orders'] ?? 0); ?></div>
                    <div class="analytics-label">Active</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value"><?php echo number_format(($analytics['total_earnings'] ?? 0), 0); ?> <?php echo CURRENCY_SYMBOL; ?></div>
                    <div class="analytics-label">Total Earnings</div>
                </div>
            </div>

            <div class="services-section">
                <h2 class="section-title">Create New Service</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="service-form">
                    <div class="form-group">
                        <label for="service_title">Service Title *</label>
                        <input type="text" id="service_title" name="service_title" placeholder="e.g., Professional Logo Design" required>
                    </div>

                    <div class="form-group">
                        <label for="service_description">Service Description *</label>
                        <textarea id="service_description" name="service_description" placeholder="Describe what you'll deliver, requirements, and any important details..." required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="web-development">Web Development</option>
                                <option value="mobile-development">Mobile Development</option>
                                <option value="graphic-design">Graphic Design</option>
                                <option value="writing">Writing & Content</option>
                                <option value="marketing">Digital Marketing</option>
                                <option value="business">Business Consulting</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Price (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                            <input type="number" id="price" name="price" step="1000" min="<?php echo MIN_BUDGET; ?>" placeholder="<?php echo number_format(MIN_BUDGET, 0); ?>" required>
                            <small style="color: #64748b;">Minimum: <?php echo number_format(MIN_BUDGET, 0); ?> <?php echo CURRENCY_SYMBOL; ?></small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="delivery_time">Delivery Time (days) *</label>
                            <input type="number" id="delivery_time" name="delivery_time" min="1" placeholder="5" required>
                        </div>

                        <div class="form-group">
                            <label for="revisions">Number of Revisions</label>
                            <input type="number" id="revisions" name="revisions" min="0" value="2">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="features">Features (comma-separated)</label>
                        <textarea id="features" name="features" placeholder="e.g., High quality, Fast delivery, Unlimited revisions" style="min-height: 80px;"></textarea>
                    </div>

                    <button type="submit" name="create_service" value="1" class="btn-submit">Publish Service</button>
                </form>
            </div>

            <!-- My Services -->
            <div class="services-section" style="margin-top: 40px;">
                <h2 class="section-title">My Services (<?php echo count($services); ?>)</h2>

                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <h3>No services yet</h3>
                        <p>Create your first service above to start earning money!</p>
                    </div>
                <?php else: ?>
                    <div class="services-list">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <div class="service-title"><?php echo htmlspecialchars($service['title']); ?></div>
                                <span class="service-category"><?php echo htmlspecialchars($service['category']); ?></span>

                                <div class="service-desc"><?php echo htmlspecialchars(substr($service['description'], 0, 80) . '...'); ?></div>

                                <div class="service-info">
                                    <div class="info-item">
                                        <div class="info-value"><?php echo $service['views']; ?></div>
                                        <div class="info-label">Impressions</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-value"><?php echo $service['orders_count']; ?></div>
                                        <div class="info-label">Clicks</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-value">⭐ <?php echo number_format($service['rating'], 1); ?></div>
                                        <div class="info-label">Rating (<?php echo $service['reviews_count']; ?>)</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-value"><?php echo $service['delivery_time']; ?> d</div>
                                        <div class="info-label">Delivery</div>
                                    </div>
                                </div>

                                <div class="service-price">
                                    <?php echo number_format($service['price'], 0); ?> <span style="font-size: 12px;"><?php echo CURRENCY_SYMBOL; ?></span>
                                    <div class="service-price-usd">≈ $<?php echo number_format($service['price'] / USD_TO_TZS, 2); ?></div>
                                </div>

                                <div class="service-actions">
                                    <button class="btn-small btn-edit" onclick="editService(<?php echo $service['id']; ?>)">Edit</button>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="delete_service" value="1" onclick="return confirm('Delete this service?')" class="btn-small btn-delete" style="width: 100%;">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function editService(serviceId) {
            alert('Edit service feature coming soon! Service ID: ' + serviceId);
        }
    </script>
</body>
</html>
