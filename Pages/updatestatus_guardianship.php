<?php
session_name('BarangayStaffSession');
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

// ✅ Get the logged-in user's full name from session
$releasedBy = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Unknown User';

if ($status === 'Released') {
    // ✅ When Released, update both RequestStatus and ReleasedBy
    $stmt = $connection->prepare("
        UPDATE guardianshiptbl 
        SET RequestStatus = ?, ReleasedBy = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sss", $status, $releasedBy, $id);
} else {
    // ✅ For other statuses, just update the status
    $stmt = $connection->prepare("
        UPDATE guardianshiptbl 
        SET RequestStatus = ? 
        WHERE id = ?
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