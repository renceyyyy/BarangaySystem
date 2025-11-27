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
    // Get all requests from docsreqtbl for this user
    $sql = "SELECT refno, Firstname, Lastname, Gender, ContactNo, Address, Docutype, 
                   DateRequested, RequestStatus, Reason
            FROM docsreqtbl 
            WHERE UserId = ? 
            ORDER BY DateRequested DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'refno' => $row['refno'],
            'firstname' => $row['Firstname'],
            'lastname' => $row['Lastname'],
            'gender' => $row['Gender'],
            'contact' => $row['ContactNo'],
            'address' => $row['Address'],
            'doctype' => $row['Docutype'],
            'date_requested' => date('M d, Y', strtotime($row['DateRequested'])),
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
        'message' => 'Error fetching request history: ' . $e->getMessage()
    ]);
}
?>
