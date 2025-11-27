<?php
require_once __DIR__ . '/../config/session_config.php';
initRoleBasedSession('staff');
require_once '../Process/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$userId = intval($_GET['user_id']);
$conn = getDBConnection();

try {
    // Get all business requests from businesstbl for this user
    $sql = "SELECT BsnssID, refno, BusinessName, BusinessLoc, OwnerName, RequestType, 
                   RequestedDate, RequestStatus, Reason, ClosureDate
            FROM businesstbl 
            WHERE UserId = ? 
            ORDER BY RequestedDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'business_id' => $row['BsnssID'],
            'refno' => $row['refno'],
            'business_name' => $row['BusinessName'],
            'business_location' => $row['BusinessLoc'],
            'owner_name' => $row['OwnerName'],
            'request_type' => ucfirst($row['RequestType']),
            'date_requested' => date('M d, Y', strtotime($row['RequestedDate'])),
            'status' => $row['RequestStatus'],
            'decline_reason' => $row['Reason'],
            'closure_date' => $row['ClosureDate'] ? date('M d, Y', strtotime($row['ClosureDate'])) : null
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'total' => count($requests)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching business request history: ' . $e->getMessage()
    ]);
}
?>
