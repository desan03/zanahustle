<?php
require_once '../config.php';
require_once '../includes/auth.php';

requireLogin();

header('Content-Type: application/json');

$orderId = intval($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Get messages related to this order
    $query = "SELECT m.*, u.first_name, u.last_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE m.subject LIKE ? 
              ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $subject = "Order #" . $orderId . "%";
    $stmt->bind_param("s", $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'body' => htmlspecialchars($row['body']),
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
