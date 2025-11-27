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
    // Get all unemployment requests for this user
    $sql = "SELECT id, refno, certificate_type, fullname, age, address, 
                   unemployed_since, no_fixed_income_since, purpose, 
                   request_date, RequestStatus, Reason
            FROM unemploymenttbl 
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
            'certificate_type' => $row['certificate_type'],
            'fullname' => $row['fullname'],
            'age' => $row['age'],
            'address' => $row['address'],
            'since_date' => $row['unemployed_since'] ?: $row['no_fixed_income_since'],
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
        'message' => 'Error fetching unemployment request history: ' . $e->getMessage()
    ]);
}
?>
