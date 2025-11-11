<?php
session_start();
require_once __DIR__ . '/../Process/db_connection.php';

$conn = getDBConnection();

if (isset($_SESSION['username']) && isset($_SESSION['audit_id'])) {
    $username = $_SESSION['username'];
    $audit_id = $_SESSION['audit_id']; // This holds the AuditID
    $timeOut = date("Y-m-d H:i:s");

    $sql = "UPDATE audittrail SET TimeOut = ? WHERE AuditID = ? AND username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sis", $timeOut, $audit_id, $username);
        if (!$stmt->execute()) {
            error_log("Audit update failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare logout audit update: " . $conn->error);
    }
}

// Clear all session variables
unset($_SESSION['user_id']);
unset($_SESSION['profile_pic']);
unset($_SESSION['Firstname']);
unset($_SESSION['Lastname']);
unset($_SESSION['AccountStatus']);

session_unset();
session_destroy();
header("Location: ../Login/login.php");
exit();
?>
