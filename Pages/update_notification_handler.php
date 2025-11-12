<?php
// Handle notification counter updates
// Initialize resident session
require_once __DIR__ . '/../config/session_resident.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Initialize session variables if not set
if (!isset($_SESSION['popup_display_count'])) {
    $_SESSION['popup_display_count'] = 0;
}
if (!isset($_SESSION['declined_popup_display_count'])) {
    $_SESSION['declined_popup_display_count'] = 0;
}

require_once '../Process/db_connection.php';
$conn = getDBConnection();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'increment_counter':
        // This is called when approval notification is manually closed
        echo json_encode([
            'success' => true, 
            'displayCount' => $_SESSION['popup_display_count'],
            'remaining' => max(0, 3 - $_SESSION['popup_display_count'])
        ]);
        break;
        
    case 'increment_declined_counter':
        // This is called when declined notification is manually closed
        echo json_encode([
            'success' => true, 
            'displayCount' => $_SESSION['declined_popup_display_count'],
            'remaining' => max(0, 3 - $_SESSION['declined_popup_display_count'])
        ]);
        break;
        
    case 'mark_declined_viewed':
        // Mark declined notification as viewed in database
        if (isset($_POST['refno']) && isset($_POST['document_type'])) {
            require_once '../Pages/declined_notification.php';
            
            $requestData = [
                'refno' => $_POST['refno'],
                'document_type' => $_POST['document_type']
            ];
            
            $success = markDeclinedNotificationAsViewed($conn, $requestData);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        }
        break;
        
    case 'reset_counter':
        // This can be called to reset the counters (admin function)
        $_SESSION['popup_display_count'] = 0;
        $_SESSION['declined_popup_display_count'] = 0;
        $_SESSION['last_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
        echo json_encode([
            'success' => true, 
            'message' => 'Counters reset successfully'
        ]);
        break;
        
    case 'get_status':
        // Get current counter status for both types
        echo json_encode([
            'success' => true,
            'approvalDisplayCount' => $_SESSION['popup_display_count'],
            'approvalRemaining' => max(0, 3 - $_SESSION['popup_display_count']),
            'declinedDisplayCount' => $_SESSION['declined_popup_display_count'],
            'declinedRemaining' => max(0, 3 - $_SESSION['declined_popup_display_count']),
            'lastCheck' => $_SESSION['last_notification_check'] ?? null
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
