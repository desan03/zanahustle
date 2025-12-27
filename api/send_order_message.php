<?php
require_once '../config.php';
require_once '../includes/auth.php';

requireLogin();

header('Content-Type: application/json');

// Verify CSRF token
if (!verifyRequestCsrf()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$orderId = intval($_POST['order_id'] ?? 0);
$clientId = intval($_POST['client_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($orderId <= 0 || $clientId <= 0 || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    $senderId = getCurrentUserId();
    
    // Verify user is involved in this order (either client or freelancer)
    $orderQuery = "SELECT client_id, freelancer_id FROM service_orders WHERE id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order = $orderResult->fetch_assoc();
    
    // Verify user is part of this order
    if ($senderId !== (int)$order['client_id'] && $senderId !== (int)$order['freelancer_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Verify recipient is the other party in the order
    if ($clientId !== (int)$order['client_id'] && $clientId !== (int)$order['freelancer_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid recipient']);
        exit;
    }
    
    $subject = "Order #" . $orderId;
    
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, body, order_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissi", $senderId, $clientId, $subject, $message, $orderId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error sending message']);
    }
} catch (Exception $e) {
    logException($e);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
