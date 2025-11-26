<?php
session_name('BarangayResidentSession');
session_start();

header('Content-Type: application/json');

require_once '../db_connection.php';
$conn = getDBConnection();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Security check - must be logged in as resident
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
$log_id = isset($_POST['log_id']) ? trim($_POST['log_id']) : '';

if (!$action || !$complaint_id || !$log_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Verify this complaint belongs to this user by matching personal details
$verify_sql = "SELECT c.CmpID, c.refno 
               FROM complaintbl c
               INNER JOIN userloginfo u ON u.UserID = ?
               WHERE c.CmpID = ?
               AND c.RequestStatus = 'awaiting_verification'
               AND LOWER(TRIM(c.Firstname)) = LOWER(TRIM(u.Firstname))
               AND LOWER(TRIM(c.Lastname)) = LOWER(TRIM(u.Lastname))
               AND (
                   (c.Middlename IS NULL AND (u.Middlename IS NULL OR u.Middlename = '' OR u.Middlename = 'uncompleted'))
                   OR LOWER(TRIM(COALESCE(c.Middlename, ''))) = LOWER(TRIM(COALESCE(u.Middlename, '')))
               )";

$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param('ii', $user_id, $complaint_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Complaint not found or access denied']);
    $verify_stmt->close();
    $conn->close();
    exit;
}

$complaint_data = $verify_result->fetch_assoc();
$verify_stmt->close();

try {
    $conn->begin_transaction();

    if ($action === 'approve') {
        // Update complaint_logstbl - set user_verification to 'approved'
        $update_log_sql = "UPDATE complaint_logstbl 
                           SET user_verification = 'approved', updated_at = NOW() 
                           WHERE Cmp_log_id = ? AND CmpID = ?";
        $log_stmt = $conn->prepare($update_log_sql);
        $log_stmt->bind_param('si', $log_id, $complaint_id);
        
        if (!$log_stmt->execute()) {
            throw new Exception('Failed to update log entry');
        }
        $log_stmt->close();

        // Update complaintbl - set RequestStatus to 'Resolved'
        $update_complaint_sql = "UPDATE complaintbl SET RequestStatus = 'Resolved' WHERE CmpID = ?";
        $complaint_stmt = $conn->prepare($update_complaint_sql);
        $complaint_stmt->bind_param('i', $complaint_id);
        
        if (!$complaint_stmt->execute()) {
            throw new Exception('Failed to update complaint status');
        }
        $complaint_stmt->close();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Complaint has been marked as resolved. Thank you for your feedback!',
            'new_status' => 'Resolved'
        ]);

    } elseif ($action === 'reject') {
        $rejection_reason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';
        
        if (empty($rejection_reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection']);
            $conn->rollback();
            exit;
        }

        // Update complaint_logstbl - set user_verification to 'rejected' and add rejection reason
        $update_log_sql = "UPDATE complaint_logstbl 
                           SET user_verification = 'rejected', rejection_reason = ?, updated_at = NOW() 
                           WHERE Cmp_log_id = ? AND CmpID = ?";
        $log_stmt = $conn->prepare($update_log_sql);
        $log_stmt->bind_param('ssi', $rejection_reason, $log_id, $complaint_id);
        
        if (!$log_stmt->execute()) {
            throw new Exception('Failed to update log entry');
        }
        $log_stmt->close();

        // Update complaintbl - set RequestStatus back to 'in_progress'
        $update_complaint_sql = "UPDATE complaintbl SET RequestStatus = 'in_progress' WHERE CmpID = ?";
        $complaint_stmt = $conn->prepare($update_complaint_sql);
        $complaint_stmt->bind_param('i', $complaint_id);
        
        if (!$complaint_stmt->execute()) {
            throw new Exception('Failed to update complaint status');
        }
        $complaint_stmt->close();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Your feedback has been submitted. The barangay will review and provide a new solution.',
            'new_status' => 'in_progress'
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>