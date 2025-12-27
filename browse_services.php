<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

// Ensure user is a client
if (!canAccessRole('client')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}

setUserRole('client');

$userId = getCurrentUserId();
$userName = $_SESSION['username'] ?? 'User';

// Get search/filter parameters
$searchQuery = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$maxPrice = intval($_GET['max_price'] ?? 0);
$sortBy = trim($_GET['sort'] ?? 'newest');

// Build query
$query = "SELECT s.*, u.first_name, u.last_name, u.profile_photo, up.rating, up.reviews_count
          FROM services s
          JOIN users u ON s.freelancer_id = u.id
          LEFT JOIN user_profiles up ON u.id = up.user_id
          WHERE s.status = 'active' AND s.freelancer_id != ?";

$params = [$userId];
$types = "i";

// Add search filter
if (!empty($searchQuery)) {
    $query .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params = array_merge($params, [$searchTerm, $searchTerm]);
    $types .= "ss";
}

// Add category filter
if (!empty($category)) {
    $query .= " AND s.category = ?";
    $params[] = $category;
    $types .= "s";
}

// Add price filter
if ($maxPrice > 0) {
    $query .= " AND s.price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

// Add sorting
switch ($sortBy) {
    case 'price-low':
        $query .= " ORDER BY s.price ASC";
        break;
    case 'price-high':
        $query .= " ORDER BY s.price DESC";
        break;
    case 'rating':
        $query .= " ORDER BY u.rating DESC, s.created_at DESC";
        break;
    case 'popular':
        $query .= " ORDER BY s.orders_count DESC, s.views DESC";
        break;
    default: // newest
        $query .= " ORDER BY s.created_at DESC";
}

// Execute query
if (empty($params)) {
    $servicesResult = $conn->query($query);
    $services = $servicesResult->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $servicesResult = $stmt->get_result();
    $services = $servicesResult->fetch_all(MYSQLI_ASSOC);
}

// Get distinct categories
$categoriesQuery = "SELECT DISTINCT category FROM services WHERE status = 'active' ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);

// Handle order submission
$orderSuccess = '';
$orderError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $serviceId = intval($_POST['service_id'] ?? 0);
    $deliveryDate = trim($_POST['delivery_date'] ?? '');
    
    if ($serviceId <= 0) {
        $orderError = 'Invalid service selected';
    } elseif (empty($deliveryDate)) {
        $orderError = 'Please select a delivery date';
    } else {
        // Get service details
        $serviceQuery = "SELECT * FROM services WHERE id = ? AND status = 'active'";
        $serviceStmt = $conn->prepare($serviceQuery);
        $serviceStmt->bind_param("i", $serviceId);
        $serviceStmt->execute();
        $serviceResult = $serviceStmt->get_result();
        
        if ($serviceResult->num_rows === 0) {
            $orderError = 'Service not found';
        } else {
            $service = $serviceResult->fetch_assoc();
            
            // Create order
            $orderQuery = "INSERT INTO service_orders (service_id, client_id, freelancer_id, amount, status, delivery_date)
                          VALUES (?, ?, ?, ?, 'pending', ?)";
            $orderStmt = $conn->prepare($orderQuery);
            $orderStmt->bind_param("iiids", $serviceId, $userId, $service['freelancer_id'], $service['price'], $deliveryDate);
            
            if ($orderStmt->execute()) {
                // Update service stats
                $updateQuery = "UPDATE services SET orders_count = orders_count + 1 WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $serviceId);
                $updateStmt->execute();
                
                $orderSuccess = 'Order placed successfully! The freelancer will review your order soon.';
                // Refresh services list using prepared statement
                if (empty($params)) {
                    $servicesResult = $conn->query($query);
                    $services = $servicesResult->fetch_all(MYSQLI_ASSOC);
                } else {
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $servicesResult = $stmt->get_result();
                    $services = $servicesResult->fetch_all(MYSQLI_ASSOC);
                }
            } else {
                $orderError = 'Error placing order: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Services - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
    <style>
        .browse-container {
            padding: 40px 20px;
            background: #f8fafc;
            min-height: calc(100vh - 80px);
        }

        .browse-header {
            margin-bottom: 40px;
        }

        .browse-header h1 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .browse-content {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-title {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            color: #0f172a;
            font-size: 13px;
        }

        .filter-group input[type="checkbox"],
        .filter-group input[type="radio"] {
            margin-right: 8px;
            cursor: pointer;
        }

        .filter-group input[type="text"],
        .filter-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .filter-group button {
            width: 100%;
            padding: 8px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-group button:hover {
            background: #4f46e5;
        }

        .services-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .services-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .services-toolbar select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 13px;
            background: white;
            cursor: pointer;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .service-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .service-card:hover {
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .service-card-header {
            padding: 15px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .service-category {
            display: inline-block;
            background: #e2e8f0;
            color: #64748b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .service-title {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .freelancer-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .freelancer-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #6366f1;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
        }

        .freelancer-rating {
            color: #f59e0b;
        }

        .service-card-body {
            padding: 15px;
        }

        .service-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .service-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .meta-item {
            text-align: center;
        }

        .meta-value {
            font-weight: 600;
            color: #6366f1;
        }

        .meta-label {
            font-size: 11px;
            color: #64748b;
        }

        .service-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .price-amount {
            font-size: 18px;
            font-weight: 600;
            color: #6366f1;
        }

        .price-usd {
            font-size: 11px;
            color: #64748b;
        }

        .service-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary-sm {
            background: #6366f1;
            color: white;
        }

        .btn-primary-sm:hover {
            background: #4f46e5;
        }

        .btn-secondary-sm {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary-sm:hover {
            background: #e2e8f0;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #0f172a;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .browse-content {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .filters {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="browse-container">
        <div class="container">
            <div class="browse-header">
                <h1>Browse Freelancer Services</h1>
                <p>Discover and order services from talented freelancers</p>
            </div>

            <?php if ($orderSuccess): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($orderSuccess); ?></div>
            <?php endif; ?>

            <?php if ($orderError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($orderError); ?></div>
            <?php endif; ?>

            <div class="browse-content">
                <!-- Filters Sidebar -->
                <aside class="filters">
                    <h3 class="filter-title">Filters</h3>

                    <form method="GET" class="filter-form">
                        <!-- Search -->
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Search services..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>

                        <!-- Category -->
                        <div class="filter-group">
                            <div class="filter-title" style="margin: 0 0 10px 0;">Category</div>
                            <label>
                                <input type="radio" name="category" value="" <?php echo empty($category) ? 'checked' : ''; ?>>
                                All Categories
                            </label>
                            <?php foreach ($categories as $cat): ?>
                                <label>
                                    <input type="radio" name="category" value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $cat['category']))); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-group">
                            <label>Max Price (<?php echo CURRENCY_SYMBOL; ?>)</label>
                            <input type="number" name="max_price" placeholder="No limit" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                        </div>

                        <!-- Sort -->
                        <div class="filter-group">
                            <label>Sort By</label>
                            <select name="sort">
                                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="price-low" <?php echo $sortBy === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-high" <?php echo $sortBy === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Best Rated</option>
                                <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>

                        <button type="submit" class="filter-group" style="margin-bottom: 0;">Apply Filters</button>
                    </form>
                </aside>

                <!-- Services Grid -->
                <main class="services-section">
                    <div class="services-toolbar">
                        <div>Showing <?php echo count($services); ?> service(s)</div>
                        <select onchange="window.location.href='?sort=' + this.value<?php echo !empty($searchQuery) ? " + '&search=" . urlencode($searchQuery) . "'" : ''; ?><?php echo !empty($category) ? " + '&category=" . urlencode($category) . "'" : ''; ?>">
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Sort: Newest</option>
                            <option value="price-low" <?php echo $sortBy === 'price-low' ? 'selected' : ''; ?>>Sort: Price Low to High</option>
                            <option value="price-high" <?php echo $sortBy === 'price-high' ? 'selected' : ''; ?>>Sort: Price High to Low</option>
                            <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Sort: Best Rated</option>
                            <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Sort: Most Popular</option>
                        </select>
                    </div>

                    <?php if (empty($services)): ?>
                        <div class="empty-state">
                            <h3>No services found</h3>
                            <p>Try adjusting your filters or search terms</p>
                        </div>
                    <?php else: ?>
                        <div class="services-grid">
                            <?php foreach ($services as $service): ?>
                                <div class="service-card">
                                    <div class="service-card-header">
                                        <span class="service-category"><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $service['category']))); ?></span>
                                        <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                                        <div class="freelancer-info">
                                            <div class="freelancer-avatar"><?php echo strtoupper(substr($service['first_name'], 0, 1)); ?></div>
                                            <div>
                                                <div><?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?></div>
                                                <div class="freelancer-rating">
                                                    ★ <?php echo number_format($service['rating'] ?? 0, 1); ?> (<?php echo ($service['reviews_count'] ?? 0); ?>)
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="service-card-body">
                                        <p class="service-desc"><?php echo htmlspecialchars(substr($service['description'], 0, 100) . '...'); ?></p>

                                        <div class="service-meta">
                                            <div class="meta-item">
                                                <div class="meta-value"><?php echo $service['views']; ?></div>
                                                <div class="meta-label">Views</div>
                                            </div>
                                            <div class="meta-item">
                                                <div class="meta-value"><?php echo $service['orders_count']; ?></div>
                                                <div class="meta-label">Orders</div>
                                            </div>
                                        </div>

                                        <div class="service-price">
                                            <div>
                                                <div class="price-amount"><?php echo number_format($service['price'], 0); ?> <?php echo CURRENCY_SYMBOL; ?></div>
                                                <div class="price-usd">≈ $<?php echo number_format($service['price'] / USD_TO_TZS, 2); ?></div>
                                            </div>
                                            <div style="font-size: 12px; text-align: right; color: #64748b;">
                                                <div><?php echo $service['delivery_time']; ?> days</div>
                                                <div><?php echo $service['revisions']; ?> revisions</div>
                                            </div>
                                        </div>

                                        <div class="service-actions">
                                            <button class="btn-small btn-secondary-sm" onclick="viewServiceDetails(<?php echo $service['id']; ?>)">View</button>
                                            <button class="btn-small btn-primary-sm" onclick="orderService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['title']); ?>', <?php echo $service['price']; ?>)">Order</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <!-- Service Details Modal -->
    <div id="serviceDetailsModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeServiceDetailsModal()">&times;</span>
            <div id="serviceDetailsContent" style="padding: 30px;"></div>
        </div>
    </div>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeOrderModal()">&times;</span>
            <h2 id="modalServiceTitle">Order Service</h2>

            <form method="POST" class="order-form">
                <input type="hidden" id="serviceId" name="service_id" value="">

                <div class="form-group">
                    <label for="orderDeliveryDate">Preferred Delivery Date *</label>
                    <input type="date" id="orderDeliveryDate" name="delivery_date" required>
                    <small style="color: #64748b;">The freelancer will work to meet this deadline</small>
                </div>

                <div class="form-group">
                    <div style="background: #f0f9ff; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <p style="margin: 0 0 5px 0; color: #0f172a; font-weight: 600;">Price: <span id="orderPrice"></span></p>
                        <p style="margin: 5px 0; color: #64748b; font-size: 12px;" id="orderPriceUSD"></p>
                    </div>
                </div>

                <button type="submit" name="place_order" value="1" class="btn btn-primary btn-block">Confirm Order</button>
            </form>
        </div>
    </div>

    <script>
        const CURRENCY = '<?php echo CURRENCY_SYMBOL; ?>';
        const USD_TO_TZS = <?php echo USD_TO_TZS; ?>;

        function orderService(serviceId, title, price) {
            document.getElementById('modalServiceTitle').textContent = 'Order: ' + title;
            document.getElementById('serviceId').value = serviceId;
            document.getElementById('orderPrice').textContent = new Intl.NumberFormat().format(Math.floor(price)) + ' ' + CURRENCY;
            document.getElementById('orderPriceUSD').textContent = '≈ $' + (price / USD_TO_TZS).toFixed(2);
            
            // Set minimum delivery date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('orderDeliveryDate').min = tomorrow.toISOString().split('T')[0];
            
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function closeServiceDetailsModal() {
            document.getElementById('serviceDetailsModal').style.display = 'none';
        }

        function viewServiceDetails(serviceId) {
            // Fetch service details from the services list
            const servicesData = <?php echo json_encode($services); ?>;
            const service = servicesData.find(s => s.id === serviceId);
            
            if (!service) {
                alert('Service not found');
                return;
            }

            const detailsHTML = `
                <h2 style="margin-bottom: 20px; color: #0f172a;">${service.title}</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <h3 style="color: #0f172a; margin-bottom: 15px; font-size: 16px;">About This Service</h3>
                        <p style="color: #64748b; line-height: 1.6; margin-bottom: 20px;">${service.description}</p>
                        
                        <h3 style="color: #0f172a; margin-bottom: 15px; font-size: 16px;">About the Freelancer</h3>
                        <div style="display: flex; gap: 15px;">
                            <div style="width: 60px; height: 60px; background: #6366f1; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 20px;">
                                ${service.first_name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <p style="color: #0f172a; font-weight: 600; margin-bottom: 5px;">${service.first_name} ${service.last_name}</p>
                                <p style="color: #f59e0b; margin-bottom: 5px;">★ ${service.rating.toFixed(1)} (${service.reviews_count} reviews)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 style="color: #0f172a; margin-bottom: 15px; font-size: 16px;">Service Details</h3>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <span style="color: #64748b; font-size: 14px;">Price</span>
                                <span style="color: #0f172a; font-weight: 600; font-size: 18px;">${new Intl.NumberFormat().format(Math.floor(service.price))} ${CURRENCY}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <span style="color: #64748b; font-size: 14px;">Delivery Time</span>
                                <span style="color: #0f172a; font-weight: 600;">${service.delivery_time} days</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <span style="color: #64748b; font-size: 14px;">Revisions</span>
                                <span style="color: #0f172a; font-weight: 600;">${service.revisions}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b; font-size: 14px;">Category</span>
                                <span style="color: #0f172a; font-weight: 600;">${service.category.replace(/-/g, ' ')}</span>
                            </div>
                            
                            <button class="btn-small btn-primary-sm" onclick="orderService(${service.id}, '${service.title.replace(/'/g, "\\'")}', ${service.price})" style="margin-top: 10px;">
                                Order Now
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('serviceDetailsContent').innerHTML = detailsHTML;
            document.getElementById('serviceDetailsModal').style.display = 'block';
        }

        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const serviceDetailsModal = document.getElementById('serviceDetailsModal');
            
            if (event.target === orderModal) {
                closeOrderModal();
            }
            if (event.target === serviceDetailsModal) {
                closeServiceDetailsModal();
            }
        }
    </script>
</body>
</html>
