<?php
// Try to start the correct session (staff or resident) so we can access audit data
require_once __DIR__ . '/../Process/db_connection.php';

$conn = getDBConnection();

$sessionFound = false;

// Attempt staff session first
session_name('BarangayStaffSession');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['username']) && isset($_SESSION['audit_id'])) {
    $sessionFound = true;
} else {
    // Not a staff session; try resident session
    session_write_close();
    session_name('BarangayResidentSession');
    session_start();
    if (isset($_SESSION['username']) && isset($_SESSION['audit_id'])) {
        $sessionFound = true;
    }
}

if ($sessionFound) {
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
} else {
    error_log('No active session found when attempting logout audit update.');
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
