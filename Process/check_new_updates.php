<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_updates']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();
    
    // Initialize last check time if not set
    if (!isset($_SESSION['last_realtime_check'])) {
        $_SESSION['last_realtime_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
    }
    
    $lastCheck = $_SESSION['last_realtime_check'];
    
    $response = [
        'hasNewApprovals' => false,
        'hasNewDeclines' => false
    ];
    
    // Check for new approved requests across all tables
    $approvalTables = [
        ['table' => 'docsreqtbl', 'user_col' => 'UserId', 'date_col' => 'DateRequested'],
        ['table' => 'businesstbl', 'user_col' => 'UserId', 'date_col' => 'RequestedDate'],
        ['table' => 'scholarship', 'user_col' => 'UserID', 'date_col' => 'DateApplied'],
        ['table' => 'unemploymenttbl', 'user_col' => 'user_id', 'date_col' => 'request_date'],
        ['table' => 'guardianshiptbl', 'user_col' => 'user_id', 'date_col' => 'request_date'],
        ['table' => 'no_birthcert_tbl', 'user_col' => 'user_id', 'date_col' => 'request_date'],
        ['table' => 'complaintbl', 'user_col' => 'Userid', 'date_col' => 'DateComplained']
    ];
    
    foreach ($approvalTables as $table) {
        $sql = "SELECT COUNT(*) as count FROM {$table['table']} 
                WHERE {$table['user_col']} = ? 
                AND RequestStatus = 'approved' 
                AND {$table['date_col']} >= ?";
        
        $stmt = db_prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $userId, $lastCheck);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $response['hasNewApprovals'] = true;
                $_SESSION['last_realtime_check'] = date('Y-m-d H:i:s');
                break;
            }
            $stmt->close();
        }
    }
    
    // Check for new declined requests across all tables
    foreach ($approvalTables as $table) {
        $sql = "SELECT COUNT(*) as count FROM {$table['table']} 
                WHERE {$table['user_col']} = ? 
                AND RequestStatus = 'declined' 
                AND {$table['date_col']} >= ?";
        
        $stmt = db_prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $userId, $lastCheck);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $response['hasNewDeclines'] = true;
                $_SESSION['last_realtime_check'] = date('Y-m-d H:i:s');
                break;
            }
            $stmt->close();
        }
    }
    
    echo json_encode($response);
}
?>
