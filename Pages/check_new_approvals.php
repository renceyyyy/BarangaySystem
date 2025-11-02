<?php
// check_new_approvals.php - Real-time approval checker
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasNewApprovals' => false, 'totalNewApprovals' => 0]);
    exit;
}

// Initialize session variables if not set
if (!isset($_SESSION['shown_notifications'])) {
    $_SESSION['shown_notifications'] = [];
}

if (!isset($_SESSION['last_notification_check'])) {
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
}

$userId = $_SESSION['user_id'];
$lastCheck = $_SESSION['last_notification_check'];
$shownNotifications = $_SESSION['shown_notifications'];

// Check all tables for new approvals since last check
$hasNewApprovals = false;
$newApprovalCount = 0;
$newApprovalDetails = [];

$tables = [
    ['table' => 'docsreqtbl', 'user_col' => 'UserId', 'date_col' => 'DateRequested', 'id_col' => 'refno', 'prefix' => 'doc_'],
    ['table' => 'businesstbl', 'user_col' => 'UserId', 'date_col' => 'RequestedDate', 'id_col' => 'refno', 'prefix' => 'bus_'],
    ['table' => 'scholarship', 'user_col' => 'UserID', 'date_col' => 'DateApplied', 'id_col' => 'ApplicationID', 'prefix' => 'sch_'],
    ['table' => 'unemploymenttbl', 'user_col' => 'user_id', 'date_col' => 'request_date', 'id_col' => 'refno', 'prefix' => 'une_'],
    ['table' => 'guardianshiptbl', 'user_col' => 'user_id', 'date_col' => 'request_date', 'id_col' => 'refno', 'prefix' => 'gua_'],
    ['table' => 'no_birthcert_tbl', 'user_col' => 'user_id', 'date_col' => 'request_date', 'id_col' => 'refno', 'prefix' => 'nbc_'],
    ['table' => 'complaintbl', 'user_col' => 'Userid', 'date_col' => 'DateComplained', 'id_col' => 'refno', 'prefix' => 'com_']
];

foreach ($tables as $table) {
    $sql = "SELECT {$table['id_col']} as id, {$table['date_col']} as approval_date 
            FROM {$table['table']} 
            WHERE {$table['user_col']} = ? 
            AND RequestStatus = 'approved' 
            AND {$table['date_col']} > ?";
            
    $stmt = db_prepare($sql);
    if ($stmt) {
        $stmt->bind_param("is", $userId, $lastCheck);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $uniqueId = $table['prefix'] . $row['id'];
            
            // Check if this approval has been shown less than 3 times
            $displayCount = isset($shownNotifications[$uniqueId]) ? $shownNotifications[$uniqueId] : 0;
            
            if ($displayCount < 3) {
                $hasNewApprovals = true;
                $newApprovalCount++;
                $newApprovalDetails[] = [
                    'unique_id' => $uniqueId,
                    'table' => $table['table'],
                    'approval_date' => $row['approval_date'],
                    'displays_left' => 3 - $displayCount
                ];
            }
        }
        
        $stmt->close();
    }
}

// Also check for completely new approvals (approved in the last 2 minutes)
$recentCheck = date('Y-m-d H:i:s', strtotime('-2 minutes'));
$brandNewApprovals = 0;

foreach ($tables as $table) {
    $sql = "SELECT COUNT(*) as new_count FROM {$table['table']} 
            WHERE {$table['user_col']} = ? 
            AND RequestStatus = 'approved' 
            AND {$table['date_col']} > ?";
            
    $stmt = db_prepare($sql);
    if ($stmt) {
        $stmt->bind_param("is", $userId, $recentCheck);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $brandNewApprovals += $row['new_count'];
        $stmt->close();
    }
}

// Respond with JSON
echo json_encode([
    'hasNewApprovals' => $hasNewApprovals,
    'newApprovalCount' => $newApprovalCount,
    'brandNewApprovals' => $brandNewApprovals,
    'newApprovalDetails' => $newApprovalDetails,
    'lastCheck' => $lastCheck,
    'currentTime' => date('Y-m-d H:i:s')
]);
?>