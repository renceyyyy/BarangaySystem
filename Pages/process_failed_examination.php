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

            // Get applicant's user ID for notification
            $getUserSql = "SELECT UserID FROM scholarship WHERE ApplicationID = ?";
            $getUserStmt = $conn->prepare($getUserSql);
            if ($getUserStmt) {
                $getUserStmt->bind_param("i", $applicationId);
                $getUserStmt->execute();
                $userResult = $getUserStmt->get_result();
                if ($userRow = $userResult->fetch_assoc()) {
                    $applicantUserId = $userRow['UserID'];
                    
                    // Create notification for the applicant
                    $notifMessage = "We regret to inform you that your Scholarship Application (ID: {$applicationId}) did not pass the examination. You may reapply in the next scholarship period.";
                    $notifSql = "INSERT INTO user_notifications (user_id, refno, message, status, request_type, created_at) VALUES (?, ?, ?, 'declined', 'Scholarship', NOW())";
                    $notifStmt = $conn->prepare($notifSql);
                    if ($notifStmt) {
                        $notifStmt->bind_param("iss", $applicantUserId, $applicationId, $notifMessage);
                        $notifStmt->execute();
                        $notifStmt->close();
                    }
                }
                $getUserStmt->close();
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
