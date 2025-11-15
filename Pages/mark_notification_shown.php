<?php
// mark_notification_shown.php
// Marks the scholarship notification as shown for the user

session_start();
require_once '../Process/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['applicationId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$userId = $_SESSION['user_id'];
$applicationId = intval($_POST['applicationId']);
$conn = getDBConnection();

// Update the PassedNotified flag to 1
$sql = "UPDATE scholarship
        SET PassedNotified = 1
        WHERE ApplicationID = ?
        AND UserID = ?
        AND RequestStatus = 'Approved'";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ii", $applicationId, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$conn->close();
