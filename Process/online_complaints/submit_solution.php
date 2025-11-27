<?php
// filepath: d:\xampp\htdocs\BarangaySampaguita\BarangaySystem\Process\online_complaints\submit_solution.php
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
$performed_by = isset($_POST['performed_by']) ? trim($_POST['performed_by']) : '';
$solution_logs = isset($_POST['solution_logs']) ? trim($_POST['solution_logs']) : '';

if (!$complaint_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}
if (!$performed_by || !$solution_logs) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->begin_transaction();

    // Get complaint details first
    $complaint_sql = "SELECT Firstname, Lastname, Middlename, age, address FROM complaintbl WHERE CmpID = ?";
    $complaint_stmt = $conn->prepare($complaint_sql);
    $complaint_stmt->bind_param('i', $complaint_id);
    $complaint_stmt->execute();
    $complaint_result = $complaint_stmt->get_result();
    
    if ($complaint_result->num_rows === 0) {
        $complaint_stmt->close();
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Complaint not found']);
        exit;
    }
    
    $complaint_data = $complaint_result->fetch_assoc();
    $complaint_stmt->close();

    // Normalize complaint data
    $comp_firstname = strtolower(trim($complaint_data['Firstname'] ?? ''));
    $comp_lastname = strtolower(trim($complaint_data['Lastname'] ?? ''));
    $comp_middlename = strtolower(trim($complaint_data['Middlename'] ?? ''));
    $comp_age = intval($complaint_data['age'] ?? 0);
    $comp_address = strtolower(trim($complaint_data['address'] ?? ''));

    // Check if there's a matching resident who can verify this complaint
    $match_sql = "SELECT UserID, Firstname, Lastname, Middlename, Age, Address 
                  FROM userloginfo 
                  WHERE LOWER(TRIM(Firstname)) = ? 
                  AND LOWER(TRIM(Lastname)) = ?
                  AND Firstname != 'uncompleted' 
                  AND Lastname != 'uncompleted'";
    
    $match_stmt = $conn->prepare($match_sql);
    $match_stmt->bind_param('ss', $comp_firstname, $comp_lastname);
    $match_stmt->execute();
    $match_result = $match_stmt->get_result();

    $has_matching_resident = false;

    while ($user_row = $match_result->fetch_assoc()) {
        // Normalize user data
        $user_firstname = strtolower(trim($user_row['Firstname'] ?? ''));
        $user_lastname = strtolower(trim($user_row['Lastname'] ?? ''));
        $user_middlename = strtolower(trim($user_row['Middlename'] ?? ''));
        $user_age = intval($user_row['Age'] ?? 0);
        $user_address = strtolower(trim($user_row['Address'] ?? ''));

        // Check if names match
        if ($user_firstname === $comp_firstname && $user_lastname === $comp_lastname) {
            $matches = 2; // firstname + lastname

            // Check additional fields
            if ($comp_middlename !== '' && $user_middlename !== '' && 
                $user_middlename !== 'uncompleted' && $comp_middlename === $user_middlename) {
                $matches++;
            }
            if ($comp_address !== '' && $user_address !== '' && 
                $user_address !== 'uncompleted' && $comp_address === $user_address) {
                $matches++;
            }
            if ($comp_age > 0 && $user_age > 0 && $comp_age === $user_age) {
                $matches++;
            }

            // If we have at least 3 matching fields, this resident can verify
            if ($matches >= 3) {
                $has_matching_resident = true;
                break;
            }
        }
    }

    $match_stmt->close();

    // Determine the status based on whether there's a matching resident
    $new_status = $has_matching_resident ? 'awaiting_verification' : 'Resolved';
    $verification_status = $has_matching_resident ? 'pending' : 'approved';

    // Update complaint status
    $sql = "UPDATE complaintbl SET RequestStatus = ? WHERE CmpID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $new_status, $complaint_id);
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        exit;
    }
    $stmt->close();

    // Try update existing pending log
    $update_log_sql = "UPDATE complaint_logstbl 
                       SET performed_by = ?, 
                           brgy_solution_logs = ?, 
                           user_verification = ?,
                           updated_at = NOW() 
                       WHERE CmpID = ? AND user_verification = 'pending'";
    $log_stmt = $conn->prepare($update_log_sql);
    $log_stmt->bind_param('sssi', $performed_by, $solution_logs, $verification_status, $complaint_id);
    $log_stmt->execute();

    if ($log_stmt->affected_rows === 0) {
        // No pending log found — create a new log with CMP-<CmpID>-NNN format
        $log_stmt->close();

        $maxSql = "SELECT MAX(CAST(SUBSTRING_INDEX(Cmp_log_id, '-', -1) AS UNSIGNED)) AS max_num 
                   FROM complaint_logstbl WHERE CmpID = ?";
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

        $insert_log_sql = "INSERT INTO complaint_logstbl 
                           (Cmp_log_id, CmpID, performed_by, brgy_solution_logs, user_verification, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_log_sql);
        $insert_stmt->bind_param('sisss', $log_id, $complaint_id, $performed_by, $solution_logs, $verification_status);
        if (!$insert_stmt->execute()) {
            $insert_stmt->close();
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create log entry']);
            exit;
        }
        $insert_stmt->close();
    } else {
        $log_stmt->close();
    }

    $conn->commit();
    
    $message = $has_matching_resident 
        ? 'Solution submitted for verification' 
        : 'Solution submitted and complaint automatically resolved (no matching resident found)';
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'auto_resolved' => !$has_matching_resident
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>