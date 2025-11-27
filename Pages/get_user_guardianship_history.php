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
    // Get all guardianship requests for this user
    $sql = "SELECT id, refno, request_type, child_name, child_age, child_address,
                   guardianship_since, solo_parent_since, purpose, applicant_name,
                   applicant_relationship, request_date, RequestStatus, Reason
            FROM guardianshiptbl 
            WHERE user_id = ? 
            ORDER BY request_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'id' => $row['id'],
            'refno' => $row['refno'],
            'request_type' => $row['request_type'],
            'child_name' => $row['child_name'],
            'child_age' => $row['child_age'],
            'applicant_name' => $row['applicant_name'],
            'applicant_relationship' => $row['applicant_relationship'],
            'since_date' => $row['guardianship_since'] ?: $row['solo_parent_since'],
            'purpose' => $row['purpose'],
            'date_requested' => date('M d, Y', strtotime($row['request_date'])),
            'status' => $row['RequestStatus'],
            'decline_reason' => $row['Reason']
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
        'message' => 'Error fetching guardianship request history: ' . $e->getMessage()
    ]);
}
?>
