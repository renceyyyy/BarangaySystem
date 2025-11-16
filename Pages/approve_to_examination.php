<?php
require_once __DIR__ . '/../config/session_config.php';
initRoleBasedSession('sk');
require_once '../Process/db_connection.php';

// Check if user is logged in and has SK role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'sk') {
    header("Location: ../Login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: SKpage.php");
    exit();
}

$applicationId = $_GET['id'];
$conn = getDBConnection();

// Update scholarship status to "For Examination"
$sql = "UPDATE scholarship SET RequestStatus = 'For Examination' WHERE ApplicationID = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $applicationId);

    if ($stmt->execute()) {
        // Log activity
        $logSql = "INSERT INTO activity_logs (UserID, Action, Details, Timestamp) VALUES (?, 'Scholarship Approved to Examination', ?, NOW())";
        $logStmt = $conn->prepare($logSql);
        if ($logStmt) {
            $details = "Moved scholarship application ID {$applicationId} to For Examination status";
            $logStmt->bind_param("is", $_SESSION['user_id'], $details);
            $logStmt->execute();
            $logStmt->close();
        }

        $_SESSION['success_message'] = "Scholarship application moved to For Examination status successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating scholarship status.";
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Database error occurred.";
}

$conn->close();
header("Location: SKpage.php");
exit();
