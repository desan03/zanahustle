<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_role'])) {
    setUserRole($_POST['set_role']);
}

// Ensure user can be a client
if (!canAccessRole('client')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}

setUserRole('client');

$userId = getCurrentUserId();
$userName = $_SESSION['username'] ?? 'User';
$userInfo = getCurrentUser();

// Fetch client's service orders
$ordersQuery = "SELECT so.*, s.title as service_title, s.price, u.first_name, u.last_name, up.rating 
                FROM service_orders so
                JOIN services s ON so.service_id = s.id
                JOIN users u ON so.freelancer_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE so.client_id = ? 
                ORDER BY so.created_at DESC";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("i", $userId);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);

// Fetch available freelancer services (excluding user's own services)
$servicesQuery = "SELECT s.*, u.first_name, u.last_name, u.profile_photo, up.rating, up.reviews_count
                  FROM services s
                  JOIN users u ON s.freelancer_id = u.id
                  LEFT JOIN user_profiles up ON u.id = up.user_id
                  WHERE s.status = 'active' AND s.freelancer_id != ?
                  ORDER BY s.created_at DESC
                  LIMIT 12";
$servicesStmt = $conn->prepare($servicesQuery);
$servicesStmt->bind_param("i", $userId);
$servicesStmt->execute();
$servicesResult = $servicesStmt->get_result();
$services = $servicesResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - ZanaHustle</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/main.css">
</head>
<body class="dashboard-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-icon">🚀</span>
                    <span class="logo-text">ZanaHustle</span>
                </a>
            </div>
            <div class="navbar-menu">
                <div class="nav-tabs">
                    <a href="#" class="nav-tab active" data-tab="services">
                        <span class="icon">🏢</span>
                        <span>Browse Services</span>
                    </a>
                    <a href="#" class="nav-tab" data-tab="my-orders">
                        <span class="icon">📋</span>
                        <span>My Orders</span>
                    </a>
                </div>
                <div class="nav-links">
                    <span class="user-greeting">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <span class="role-badge">Client</span>
                    <a href="<?php echo SITE_URL; ?>/edit_profile.php" class="btn btn-secondary btn-sm">My Profile</a>
                    <a href="<?php echo SITE_URL; ?>/role_select.php" class="btn btn-secondary btn-sm">Switch Role</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-logout btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Main Content -->
            <main class="dashboard-content">
                <!-- Browse Services Tab -->
                <section id="services" class="tab-content active">
                    <div class="page-header">
                        <h1>Available Freelancer Services</h1>
                        <p>Explore and hire from our network of talented freelancers</p>
                    </div>
                    
                    <?php if (empty($services)): ?>
                        <div class="empty-state">
                            <p>No services available at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="services-grid">
                            <?php foreach ($services as $service): ?>
                                <div class="service-card">
                                    <div class="service-header">
                                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                                    </div>
                                    <div class="freelancer-info">
                                        <p><strong><?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?></strong></p>
                                        <p class="rating">⭐ <?php echo number_format($service['rating'] ?? 0, 1); ?> (<?php echo $service['reviews_count'] ?? 0; ?> reviews)</p>
                                    </div>
                                    <div class="service-description">
                                        <p><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)); ?>...</p>
                                    </div>
                                    <div class="service-meta">
                                        <div class="meta-item">
                                            <span class="meta-label"> Price:</span>
                                            <span class="meta-value"><?php echo number_format($service['price'], 0); ?> <?php echo CURRENCY_SYMBOL; ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">📅 Delivery:</span>
                                            <span class="meta-value"><?php echo $service['delivery_time']; ?> days</span>
                                        </div>
                                    </div>
                                    <div class="service-actions">
                                        <button class="btn btn-primary btn-block hire-btn" data-service-id="<?php echo $service['id']; ?>" data-service-title="<?php echo htmlspecialchars($service['title']); ?>" data-price="<?php echo $service['price']; ?>">Hire Now</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="view-more-container">
                            <a href="<?php echo SITE_URL; ?>/browse_services.php" class="btn btn-secondary">View All Services</a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- My Orders Tab -->
                <section id="my-orders" class="tab-content">
                    <div class="dashboard-header">
                        <h1>My Orders</h1>
                    </div>

                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <p>You haven't hired any freelancers yet.</p>
                            <a href="<?php echo SITE_URL; ?>/browse_services.php" class="btn btn-primary">Browse & Hire Freelancers</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-grid">
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <h3><?php echo htmlspecialchars($order['service_title']); ?></h3>
                                        <span class="order-status <?php echo htmlspecialchars($order['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span>
                                    </div>
                                    <div class="freelancer-info">
                                        <p><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                                        <p>Rating: ⭐ <?php echo number_format($order['rating'] ?? 0, 1); ?></p>
                                    </div>
                                    <div class="order-meta">
                                        <p><strong>Price:</strong> <?php echo number_format($order['amount'], 0); ?> <?php echo CURRENCY_SYMBOL; ?></p>
                                        <p><strong>Delivery Date:</strong> <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></p>
                                        <p><strong>Ordered:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="order-actions">
                                        <button class="btn btn-secondary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['service_title']); ?>', <?php echo $order['amount']; ?>, '<?php echo date('M d, Y', strtotime($order['delivery_date'])); ?>', '<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>', '<?php echo htmlspecialchars($order['status']); ?>')">View Details</button>
                                        <button class="btn btn-primary btn-sm" onclick="messageFreelancer(<?php echo $order['freelancer_id']; ?>, '<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>')">Message Freelancer</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="modal-close" onclick="closeModal('orderDetailsModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent"></div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Message Freelancer</h2>
                <button class="modal-close" onclick="closeModal('messageModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p id="freelancerName" style="margin-bottom: 15px;"><strong>To:</strong> <span id="freelancerNameSpan"></span></p>
                <textarea id="messageText" placeholder="Type your message..." style="width: 100%; height: 150px; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; resize: vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('messageModal')">Cancel</button>
                <button class="btn btn-primary" onclick="sendMessage()">Send Message</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ZanaHustle. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        const CURRENCY = '<?php echo CURRENCY_SYMBOL; ?>';
        const USD_TO_TZS = <?php echo USD_TO_TZS; ?>;
        
        // Tab switching functionality from top navbar
        document.querySelectorAll('.nav-tab').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                if (!tabId) return;
                
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Remove active class from all nav tabs
                document.querySelectorAll('.nav-tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Show selected tab
                const selectedTab = document.getElementById(tabId);
                if (selectedTab) {
                    selectedTab.classList.add('active');
                    this.classList.add('active');
                }
            });
        });

        // Hire button functionality
        document.querySelectorAll('.hire-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const serviceId = this.getAttribute('data-service-id');
                const serviceTitle = this.getAttribute('data-service-title');
                const price = this.getAttribute('data-price');
                
                // Convert price to TZS if needed
                const priceInTZS = Math.round(price * USD_TO_TZS);
                
                // Show a confirmation dialog or modal
                const confirmed = confirm(`Order "${serviceTitle}" for ${CURRENCY}${priceInTZS}?`);
                
                if (confirmed) {
                    // TODO: Implement order placement
                    // For now, redirect to browse_services.php which has the full ordering flow
                    window.location.href = SITE_URL + '/browse_services.php?service=' + serviceId;
                }
            });
        });

        // View Order Details
        function viewOrderDetails(orderId, serviceTitle, amount, deliveryDate, freelancerName, status) {
            const content = `
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin-bottom: 10px; color: var(--text-dark);">${serviceTitle}</h3>
                        <span class="order-status ${status.toLowerCase().replace(/ /g, '_')}" style="padding: 6px 12px; border-radius: 4px; display: inline-block; font-weight: 600; font-size: 12px;">
                            ${status.toUpperCase()}
                        </span>
                    </div>
                    <div style="grid-template-columns: 1fr 1fr; display: grid; gap: 20px;">
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Freelancer</strong></p>
                            <p style="color: var(--text-dark); font-size: 14px;">${freelancerName}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Order ID</strong></p>
                            <p style="color: var(--text-dark); font-size: 14px;">#${orderId}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Price</strong></p>
                            <p style="color: var(--text-dark); font-size: 18px; font-weight: 600;">${CURRENCY}${number_format(amount)}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Delivery Date</strong></p>
                            <p style="color: var(--text-dark); font-size: 14px;">${deliveryDate}</p>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('orderDetailsContent').innerHTML = content;
            openModal('orderDetailsModal');
        }

        // Message Freelancer
        let currentFreelancerId = null;
        function messageFreelancer(freelancerId, freelancerName) {
            currentFreelancerId = freelancerId;
            document.getElementById('freelancerNameSpan').textContent = freelancerName;
            document.getElementById('messageText').value = '';
            openModal('messageModal');
        }

        // Send Message
        function sendMessage() {
            const message = document.getElementById('messageText').value.trim();
            if (!message) {
                alert('Please type a message');
                return;
            }
            
            fetch(SITE_URL + '/api/send_order_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=0&client_id=' + currentFreelancerId + '&message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message sent to freelancer!');
                    closeModal('messageModal');
                } else {
                    alert('Error sending message: ' + data.message);
                }
            })
            .catch(err => alert('Error: ' + err.message));
        }

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Helper function to format numbers
        function number_format(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
</body>
</html>