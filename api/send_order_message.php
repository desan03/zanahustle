<?php
require_once '../config.php';
require_once '../includes/auth.php';

requireLogin();

header('Content-Type: application/json');

$orderId = intval($_POST['order_id'] ?? 0);
$clientId = intval($_POST['client_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($orderId <= 0 || $clientId <= 0 || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    $senderId = getCurrentUserId();
    $subject = "Order #" . $orderId;
    
    $query = "INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $senderId, $clientId, $subject, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error sending message']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
