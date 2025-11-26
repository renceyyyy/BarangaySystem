<?php
session_name('BarangayStaffSession');
session_start();

header('Content-Type: application/json');

require_once '../db_connection.php';
$conn = getDBConnection();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$complaint_id = isset($_POST['complaint_id']) ? intval($_POST['complaint_id']) : 0;
if (!$complaint_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}

try {
    $conn->begin_transaction();

    // Update complaint status to "in_progress"
    $sql = "UPDATE complaintbl SET RequestStatus = 'in_progress' WHERE CmpID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $complaint_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        exit;
    }
    $stmt->close();

    // Get current max sequence number for this CmpID
    $maxSql = "SELECT MAX(CAST(SUBSTRING_INDEX(Cmp_log_id, '-', -1) AS UNSIGNED)) AS max_num FROM complaint_logstbl WHERE CmpID = ?";
    $maxStmt = $conn->prepare($maxSql);
    $maxStmt->bind_param('i', $complaint_id);
    $maxStmt->execute();
    $maxResult = $maxStmt->get_result();
    $row = $maxResult->fetch_assoc();
    $maxNum = isset($row['max_num']) ? intval($row['max_num']) : 0;
    $maxStmt->close();

    $nextNum = $maxNum + 1;
    $suffix = str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    $log_id = sprintf('CMP-%d-%s', $complaint_id, $suffix);

    // Insert log entry
    $log_sql = "INSERT INTO complaint_logstbl (Cmp_log_id, CmpID, user_verification, created_at) VALUES (?, ?, 'pending', NOW())";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param('si', $log_id, $complaint_id);
    if (!$log_stmt->execute()) {
        $log_stmt->close();
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to create log entry']);
        exit;
    }
    $log_stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Complaint processing started']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
$conn->close();
?>