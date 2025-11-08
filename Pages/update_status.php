<?php
session_start();
require_once '../Process/db_connection.php';

// Get database connection
$connection = getDBConnection();

// Validate POST data
if (!isset($_POST['id'], $_POST['status'])) {
    echo "invalid";
    exit;
}

$id = trim($_POST['id']);
$status = trim($_POST['status']);

// ✅ Only allow valid statuses for safety
$allowedStatuses = ['Approved', 'Printed', 'Released', 'Pending'];

if (!in_array($status, $allowedStatuses, true)) {
    echo "invalid status";
    exit;
}

// ✅ Get the logged-in admin username or full name from session
$releasedBy = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Unknown Admin';

if ($status === 'Released') {
    // ✅ When Released, update both RequestStatus and ReleasedBy
    $stmt = $connection->prepare("
        UPDATE docsreqtbl 
        SET RequestStatus = ?, ReleasedBy = ? 
        WHERE ReqId = ?
    ");
    $stmt->bind_param("sss", $status, $releasedBy, $id);
} else {
    // ✅ Otherwise, update only the status
    $stmt = $connection->prepare("
        UPDATE docsreqtbl 
        SET RequestStatus = ? 
        WHERE ReqId = ?
    ");
    $stmt->bind_param("ss", $status, $id);
}

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>
