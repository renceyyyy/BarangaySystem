<?php
session_start();
require_once '../Process/db_connection.php';

// Only allow admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['reason'])) {
    $connection = getDBConnection();
    $id = $connection->real_escape_string($_GET['id']);
    $reason = $connection->real_escape_string($_GET['reason']);
    
    // Update status to Declined and store reason
    $sql = "UPDATE no_birthcert_tbl SET RequestStatus = 'Declined', DeclineReason = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $reason, $id);
    
    if ($stmt->execute()) {
        // Log the activity
        $admin = $_SESSION['username'];
        $activity = "Declined No Birth Certificate request ID: $id. Reason: $reason";
        $log_sql = "INSERT INTO activity_log (username, activity, timestamp) VALUES (?, ?, NOW())";
        $log_stmt = $connection->prepare($log_sql);
        $log_stmt->bind_param("ss", $admin, $activity);
        $log_stmt->execute();
        
        header("Location: Adminpage.php?panel=nobirthCertPanel&message=declined");
    } else {
        header("Location: Adminpage.php?panel=nobirthCertPanel&error=decline_failed");
    }
    
    $stmt->close();
    $log_stmt->close();
    $connection->close();
} else {
    header("Location: Adminpage.php?panel=nobirthCertPanel&error=no_id_or_reason");
}
?>