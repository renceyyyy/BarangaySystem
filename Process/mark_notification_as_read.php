<?php
/**
 * Mark Notification As Read
 * 
 * This endpoint marks a specific notification as read when the user
 * acknowledges/dismisses it on their device
 */

session_name('BarangayResidentSession');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'db_connection.php';

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get the notification ID from POST
$notificationId = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;

if (!$notificationId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid notification ID']);
    exit();
}

// Mark the notification as read
$sql = "UPDATE user_notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ii", $notificationId, $userId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark notification as read']);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

$conn->close();
?>
