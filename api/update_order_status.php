<?php
require_once '../config.php';
require_once '../includes/auth.php';

requireLogin();

header('Content-Type: application/json');

$orderId = intval($_POST['order_id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($orderId <= 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate status
$validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $userId = getCurrentUserId();
    
    // Verify that the freelancer owns this order
    $checkQuery = "SELECT freelancer_id FROM service_orders WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $orderId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $row = $result->fetch_assoc();
    if ($row['freelancer_id'] !== $userId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Update order status
    $updateQuery = "UPDATE service_orders SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $status, $orderId);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating order']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
