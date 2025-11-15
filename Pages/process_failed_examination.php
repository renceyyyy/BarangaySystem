<?php
session_start();
require_once '../Process/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: SKpage.php");
    exit();
}

$applicationId = intval($_GET['id']);
$conn = getDBConnection();

// Update scholarship status to "Failed"
$sql = "UPDATE scholarship SET RequestStatus = 'Failed' WHERE ApplicationID = ? AND RequestStatus = 'For Examination'";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $applicationId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log activity
            $logSql = "INSERT INTO activity_logs (UserID, Action, Details, Timestamp) VALUES (?, 'Scholarship Failed - Did not pass examination', ?, NOW())";
            $logStmt = $conn->prepare($logSql);
            if ($logStmt) {
                $details = "Marked scholarship application ID {$applicationId} as Failed";
                $logStmt->bind_param("is", $_SESSION['user_id'], $details);
                $logStmt->execute();
                $logStmt->close();
            }

            $_SESSION['success_message'] = "Scholarship application marked as Failed.";
        } else {
            $_SESSION['error_message'] = "Application not found or not in 'For Examination' status.";
        }
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
