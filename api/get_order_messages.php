<?php
require_once '../config.php';
require_once '../includes/auth.php';

requireLogin();

header('Content-Type: application/json');

$orderId = intval($_GET['order_id'] ?? 0);
$userId = getCurrentUserId();

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Verify user has access to this order
    $verifyQuery = "SELECT id FROM service_orders WHERE id = ? AND (client_id = ? OR freelancer_id = ?)";
    $verifyStmt = $conn->prepare($verifyQuery);
    $verifyStmt->bind_param("iii", $orderId, $userId, $userId);
    $verifyStmt->execute();
    
    if ($verifyStmt->get_result()->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Get messages related to this order
    $query = "SELECT m.*, u.first_name, u.last_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE (m.order_id = ? OR m.subject LIKE ?)
              AND (m.sender_id = ? OR m.receiver_id = ?)
              ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $subject = "Order #" . $orderId . "%";
    $stmt->bind_param("isii", $orderId, $subject, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_id' => (int)$row['sender_id'],
            'sender_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'body' => htmlspecialchars($row['body']),
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    logException($e);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
