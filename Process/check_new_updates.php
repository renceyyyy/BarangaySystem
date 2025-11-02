<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_updates']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $response = [
        'hasNewApprovals' => false,
        'hasNewDeclines' => false,
        'approvalDisplayCount' => $_SESSION['approval_display_count'] ?? 0,
        'declineDisplayCount' => $_SESSION['decline_display_count'] ?? 0
    ];
    
    // Check for new declined requests
    require_once '../Pages/declined_notification.php';
    $declinedRequests = checkDeclinedRequests($conn, $userId);
    
    if (!empty($declinedRequests)) {
        $response['hasNewDeclines'] = true;
    }
    
    echo json_encode($response);
}
?>