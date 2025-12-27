<?php
require_once 'config.php';
require_once 'includes/auth.php';

requireLogin();
checkSessionTimeout();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_role'])) {
    setUserRole($_POST['set_role']);
}

// Ensure user can be a freelancer
if (!canAccessRole('freelancer')) {
    header('Location: ' . SITE_URL . '/role_select.php');
    exit;
}

setUserRole('freelancer');

$userId = getCurrentUserId();
$userName = $_SESSION['username'] ?? 'User';
$userInfo = getCurrentUser();

// Fetch available jobs
$jobsQuery = "SELECT * FROM jobs WHERE status = 'open' ORDER BY created_at DESC LIMIT 20";
$jobsResult = $conn->query($jobsQuery);
$jobs = $jobsResult->fetch_all(MYSQLI_ASSOC);

// Fetch freelancer's proposals
$proposalsQuery = "SELECT p.*, j.title as job_title, j.budget_min, j.budget_max FROM proposals p 
                   JOIN jobs j ON p.job_id = j.id 
                   WHERE p.freelancer_id = ? 
                   ORDER BY p.created_at DESC";
$proposalsStmt = $conn->prepare($proposalsQuery);
$proposalsStmt->bind_param("i", $userId);
$proposalsStmt->execute();
$proposalsResult = $proposalsStmt->get_result();
$proposals = $proposalsResult->fetch_all(MYSQLI_ASSOC);

// Fetch services analytics
$servicesAnalyticsQuery = "SELECT COUNT(*) as total_services FROM services WHERE freelancer_id = ?";
$servicesAnalyticsStmt = $conn->prepare($servicesAnalyticsQuery);
$servicesAnalyticsStmt->bind_param("i", $userId);
$servicesAnalyticsStmt->execute();
$servicesAnalytics = $servicesAnalyticsStmt->get_result()->fetch_assoc();

// Fetch service orders analytics
$ordersAnalyticsQuery = "SELECT 
                         COUNT(*) as total_orders,
                         SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                         SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_orders,
                         SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as service_earnings
                        FROM service_orders WHERE freelancer_id = ?";
$ordersAnalyticsStmt = $conn->prepare($ordersAnalyticsQuery);
$ordersAnalyticsStmt->bind_param("i", $userId);
$ordersAnalyticsStmt->execute();
$ordersAnalytics = $ordersAnalyticsStmt->get_result()->fetch_assoc();

// Fetch freelancer's service orders with client info
$serviceOrdersQuery = "SELECT so.*, s.title as service_title, u.first_name, u.last_name, u.profile_photo, up.rating 
                       FROM service_orders so
                       JOIN services s ON so.service_id = s.id
                       JOIN users u ON so.client_id = u.id
                       LEFT JOIN user_profiles up ON u.id = up.user_id
                       WHERE so.freelancer_id = ?
                       ORDER BY so.created_at DESC";
$serviceOrdersStmt = $conn->prepare($serviceOrdersQuery);
$serviceOrdersStmt->bind_param("i", $userId);
$serviceOrdersStmt->execute();
$serviceOrdersResult = $serviceOrdersStmt->get_result();
$serviceOrders = $serviceOrdersResult->fetch_all(MYSQLI_ASSOC);

// Handle proposal submission
$submitSuccess = '';
$submitError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_proposal'])) {
    $jobId = intval($_POST['job_id'] ?? 0);
    $bidAmount = floatval($_POST['bid_amount'] ?? 0);
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $timeline = trim($_POST['timeline'] ?? '');
    
    if ($jobId <= 0 || $bidAmount <= 0) {
        $submitError = 'Invalid job or bid amount';
    } elseif ($bidAmount < MIN_BUDGET) {
        $submitError = 'Minimum bid is ' . number_format(MIN_BUDGET, 0) . ' ' . CURRENCY_SYMBOL;
    } elseif (empty($coverLetter) || strlen($coverLetter) < 20) {
        $submitError = 'Cover letter must be at least 20 characters';
    } else {
        // Check if already proposed
        $checkQuery = "SELECT id FROM proposals WHERE job_id = ? AND freelancer_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $jobId, $userId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $submitError = 'You have already submitted a proposal for this job';
        } else {
            $query = "INSERT INTO proposals (job_id, freelancer_id, bid_amount, cover_letter, timeline, status)
                      VALUES (?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iidss", $jobId, $userId, $bidAmount, $coverLetter, $timeline);
            
            if ($stmt->execute()) {
                $submitSuccess = 'Proposal submitted successfully!';
                // Increment job proposals count
                $updateQuery = "UPDATE jobs SET proposals_count = proposals_count + 1 WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("i", $jobId);
                $updateStmt->execute();
                // Refresh proposals list
                $proposalsStmt = $conn->prepare($proposalsQuery);
                $proposalsStmt->bind_param("i", $userId);
                $proposalsStmt->execute();
                $proposalsResult = $proposalsStmt->get_result();
                $proposals = $proposalsResult->fetch_all(MYSQLI_ASSOC);
            } else {
                $submitError = 'Error submitting proposal: ' . $conn->error;
            }
        }
    }
}

// Handle proposal withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_proposal'])) {
    $proposalId = intval($_POST['proposal_id'] ?? 0);
    $deleteStmt = $conn->prepare("DELETE FROM proposals WHERE id = ? AND freelancer_id = ?");
    $deleteStmt->bind_param("ii", $proposalId, $userId);
    if ($deleteStmt->execute()) {
        $submitSuccess = 'Proposal withdrawn!';
        // Decrement job proposals count
        $getJobId = $conn->prepare("SELECT job_id FROM proposals WHERE id = ?");
        $getJobId->bind_param("i", $proposalId);
        $getJobId->execute();
        $result = $getJobId->get_result();
        if ($row = $result->fetch_assoc()) {
            $updateQuery = "UPDATE jobs SET proposals_count = GREATEST(0, proposals_count - 1) WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $row['job_id']);
            $updateStmt->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Dashboard - ZanaHustle</title>
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
                    <a href="#" class="nav-tab active" data-tab="overview">
                        <span class="icon">📊</span>
                        <span>Overview</span>
                    </a>
                    <a href="#" class="nav-tab" data-tab="orders">
                        <span class="icon">📦</span>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="nav-links">
                    <span class="user-greeting">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                    <span class="role-badge">Freelancer</span>
                    <a href="<?php echo SITE_URL; ?>/freelancer_services.php" class="btn btn-secondary btn-sm">My Services</a>
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
                <!-- Overview Tab -->
                <section id="overview" class="tab-content active">
                    <div class="dashboard-header">
                        <h1>Dashboard Overview</h1>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card" style="cursor: pointer;" onclick="navigateTo('<?php echo SITE_URL; ?>/freelancer_services.php')">
                            <div class="stat-value"><?php echo number_format(($ordersAnalytics['service_earnings'] ?? 0), 0); ?> <?php echo CURRENCY_SYMBOL; ?></div>
                            <div class="stat-label">Service Earnings</div>
                            <div class="stat-sub">≈ $<?php echo number_format(($ordersAnalytics['service_earnings'] ?? 0) / USD_TO_TZS, 2); ?></div>
                        </div>
                        <div class="stat-card" style="cursor: pointer;" onclick="navigateTo('<?php echo SITE_URL; ?>/freelancer_services.php')">
                            <div class="stat-value"><?php echo ($servicesAnalytics['total_services'] ?? 0); ?></div>
                            <div class="stat-label">Published Services</div>
                        </div>
                        <div class="stat-card" style="cursor: pointer;" onclick="switchTab('orders')">
                            <div class="stat-value"><?php echo ($ordersAnalytics['completed_orders'] ?? 0); ?></div>
                            <div class="stat-label">Completed Orders</div>
                        </div>
                        <div class="stat-card" style="cursor: pointer;" onclick="switchTab('orders')">
                            <div class="stat-value"><?php echo ($ordersAnalytics['active_orders'] ?? 0); ?></div>
                            <div class="stat-label">Active Orders</div>
                        </div>
                        <div class="stat-card" style="cursor: pointer;" onclick="navigateTo('<?php echo SITE_URL; ?>/edit_profile.php')">
                            <div class="stat-value">★ <?php echo number_format($userInfo['rating'] ?? 0, 1); ?></div>
                            <div class="stat-label">Rating</div>
                            <div class="stat-sub"><?php echo ($userInfo['reviews_count'] ?? 0); ?> reviews</div>
                        </div>
                    </div>

                    <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                        <h3 style="margin-top: 0; color: #1e40af;">💡 Tip: Publish Services to Earn More</h3>
                        <p style="color: #1e3a8a; margin: 10px 0;">Publishing services allows clients to directly purchase your offerings. You currently have <strong><?php echo ($servicesAnalytics['total_services'] ?? 0); ?></strong> published services.</p>
                        <a href="<?php echo SITE_URL; ?>/freelancer_services.php" class="btn btn-primary" style="display: inline-block;">Manage Services →</a>
                    </div>
                </section>

                <!-- Orders Tab -->
                <section id="orders" class="tab-content">
                    <div class="page-header">
                        <h1>Service Orders</h1>
                        <p>View and manage orders from clients for your services</p>
                    </div>

                    <?php if (empty($serviceOrders)): ?>
                        <div class="empty-state">
                            <p>No orders yet. Start publishing services to receive orders from clients.</p>
                            <a href="<?php echo SITE_URL; ?>/freelancer_services.php" class="btn btn-primary">Publish a Service</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-grid">
                            <?php foreach ($serviceOrders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <h3><?php echo htmlspecialchars($order['service_title']); ?></h3>
                                        <span class="order-status <?php echo htmlspecialchars($order['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span>
                                    </div>
                                    <div class="freelancer-info">
                                        <p><strong>Client: <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                                        <p>Rating: ⭐ <?php echo number_format($order['rating'] ?? 0, 1); ?></p>
                                    </div>
                                    <div class="order-meta">
                                        <p><strong>Amount:</strong> <?php echo number_format($order['amount'], 0); ?> <?php echo CURRENCY_SYMBOL; ?></p>
                                        <p><strong>Delivery Date:</strong> <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></p>
                                        <p><strong>Ordered:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="order-actions">
                                        <button class="btn btn-secondary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['service_title']); ?>', <?php echo $order['amount']; ?>, '<?php echo date('M d, Y', strtotime($order['delivery_date'])); ?>', '<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>', '<?php echo htmlspecialchars($order['status']); ?>', <?php echo $order['client_id']; ?>)">View Details</button>
                                        <button class="btn btn-primary btn-sm" onclick="viewMessages(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>', <?php echo $order['client_id']; ?>)">Messages</button>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-success btn-sm" onclick="acceptOrder(<?php echo $order['id']; ?>)" style="background-color: #10b981; color: white; padding: 6px 12px;">Accept</button>
                                            <button class="btn btn-error btn-sm" onclick="denyOrder(<?php echo $order['id']; ?>)" style="background-color: #ef4444; color: white; padding: 6px 12px;">Deny</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </main>
        </div>
    </div>

    <!-- Proposal Modal -->
    <div id="proposalModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeProposalModal()">&times;</span>
            <h2>Submit Proposal</h2>

            <?php if ($submitSuccess): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($submitSuccess); ?></div>
            <?php endif; ?>

            <?php if ($submitError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($submitError); ?></div>
            <?php endif; ?>

            <form method="POST" class="proposal-form">
                <input type="hidden" id="modalJobId" name="job_id" value="">

                <div class="form-group">
                    <label for="modalJobTitle">Job</label>
                    <input type="text" id="modalJobTitle" disabled>
                </div>

                <div class="form-group">
                    <label for="bid_amount">Your Bid Amount (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                    <input type="number" id="bid_amount" name="bid_amount" step="1000" min="<?php echo MIN_BUDGET; ?>" placeholder="<?php echo number_format(MIN_BUDGET, 0); ?>" required>
                    <small style="color: #666;">Minimum: <?php echo number_format(MIN_BUDGET, 0); ?> <?php echo CURRENCY_SYMBOL; ?></small>
                    <div id="bidUSD" style="margin-top: 5px; color: #666; font-size: 12px;"></div>
                </div>

                <div class="form-group">
                    <label for="timeline">Timeline *</label>
                    <select id="timeline" name="timeline" required>
                        <option value="">Select timeline</option>
                        <option value="less-than-a-week">Less than a week</option>
                        <option value="1-2-weeks">1-2 weeks</option>
                        <option value="2-4-weeks">2-4 weeks</option>
                        <option value="1-3-months">1-3 months</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cover_letter">Cover Letter *</label>
                    <textarea id="cover_letter" name="cover_letter" placeholder="Tell the client why you're the best fit for this job..." required rows="5"></textarea>
                </div>

                <button type="submit" name="submit_proposal" value="1" class="btn btn-primary btn-block">Submit Proposal</button>
            </form>
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

    <!-- Messages Modal -->
    <div id="messagesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Messages with <span id="clientNameSpan"></span></h2>
                <button class="modal-close" onclick="closeModal('messagesModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messagesContent" style="height: 400px; overflow-y: auto; margin-bottom: 15px; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <p style="color: var(--text-light); text-align: center;">Loading messages...</p>
                </div>
                <textarea id="messageInput" placeholder="Type your response..." style="width: 100%; height: 100px; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; resize: vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('messagesModal')">Close</button>
                <button class="btn btn-primary" onclick="sendOrderMessage()">Send Message</button>
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
        let currentClientId = null;
        let currentOrderId = null;

        // Tab switching functionality
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

        // View Order Details
        function viewOrderDetails(orderId, serviceTitle, amount, deliveryDate, clientName, status, clientId) {
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
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Client</strong></p>
                            <p style="color: var(--text-dark); font-size: 14px;">${clientName}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Order ID</strong></p>
                            <p style="color: var(--text-dark); font-size: 14px;">#${orderId}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 5px;"><strong>Amount</strong></p>
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

        // View Messages
        function viewMessages(orderId, clientName, clientId) {
            currentOrderId = orderId;
            currentClientId = clientId;
            document.getElementById('clientNameSpan').textContent = clientName;
            document.getElementById('messageInput').value = '';
            
            // Fetch messages for this order
            fetch(SITE_URL + '/api/get_order_messages.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const messagesHtml = data.messages.map(msg => `
                            <div style="margin-bottom: 15px; padding: 10px; background: white; border-radius: 6px;">
                                <p style="margin: 0 0 5px 0; font-weight: 600; color: var(--text-dark); font-size: 13px;">${msg.sender_name}</p>
                                <p style="margin: 0 0 5px 0; color: var(--text-light); font-size: 12px;">${new Date(msg.created_at).toLocaleString()}</p>
                                <p style="margin: 0; color: var(--text-dark); font-size: 14px;">${msg.body}</p>
                            </div>
                        `).join('');
                        document.getElementById('messagesContent').innerHTML = messagesHtml || '<p style="text-align: center; color: var(--text-light);">No messages yet</p>';
                    }
                })
                .catch(err => {
                    document.getElementById('messagesContent').innerHTML = '<p style="color: var(--error-color);">Error loading messages</p>';
                });
            
            openModal('messagesModal');
        }

        // Send Order Message
        function sendOrderMessage() {
            const message = document.getElementById('messageInput').value.trim();
            if (!message) {
                alert('Please type a message');
                return;
            }

            fetch(SITE_URL + '/api/send_order_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=' + currentOrderId + '&client_id=' + currentClientId + '&message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message sent!');
                    document.getElementById('messageInput').value = '';
                    // Refresh messages
                    viewMessages(currentOrderId, document.getElementById('clientNameSpan').textContent, currentClientId);
                } else {
                    alert('Error sending message: ' + data.message);
                }
            })
            .catch(err => alert('Error: ' + err.message));
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

        // Bid conversion
        document.getElementById('bid_amount')?.addEventListener('input', function() {
            const bid = parseFloat(this.value) || 0;
            const bidUsd = document.getElementById('bidUSD');
            if (bidUsd) {
                bidUsd.textContent = bid > 0 ? '≈ $' + (bid / USD_TO_TZS).toFixed(2) : '';
            }
        });

        function openProposalForm(jobId) {
            const job = jobs.find(j => j.id === jobId);
            if (job) {
                document.getElementById('modalJobId').value = jobId;
                document.getElementById('modalJobTitle').value = job.title;
                document.getElementById('proposalModal').style.display = 'block';
                document.getElementById('bid_amount').value = '';
                document.getElementById('bid_amount').dispatchEvent(new Event('input'))
                document.getElementById('modalJobTitle').value = job.title;
                document.getElementById('proposalModal').style.display = 'block';
            }
        }

        function closeProposalModal() {
            document.getElementById('proposalModal').style.display = 'none';
        }

        function openJobModal(jobId) {
            const job = jobs.find(j => j.id === jobId);
            if (job) {
                alert(job.title + '\n\n' + job.description);
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('proposalModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Accept Order
        function acceptOrder(orderId) {
            if (confirm('Accept this order?')) {
                fetch(SITE_URL + '/api/update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId + '&status=in_progress'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order accepted!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Error: ' + err.message));
            }
        }

        // Deny Order
        function denyOrder(orderId) {
            if (confirm('Deny this order? This action cannot be undone.')) {
                fetch(SITE_URL + '/api/update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId + '&status=cancelled'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order denied!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Error: ' + err.message));
            }
        }

        // Navigation helpers
        function navigateTo(url) {
            window.location.href = url;
        }

        function switchTab(tabName) {
            const tab = document.querySelector('[data-tab="' + tabName + '"]');
            if (tab) {
                tab.click();
            }
        }
    </script>
</body>
</html>
