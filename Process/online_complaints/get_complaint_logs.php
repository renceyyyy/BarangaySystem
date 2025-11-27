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

$complaint_id = isset($_GET['complaint_id']) ? intval($_GET['complaint_id']) : 0;

if (!$complaint_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}

// Fetch all logs for this complaint, ordered by created_at descending
$sql = "SELECT 
    Cmp_log_id,
    CmpID,
    user_verification,
    rejection_reason,
    brgy_solution_logs,
    performed_by,
    created_at,
    updated_at
FROM complaint_logstbl
WHERE CmpID = ?
ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'Cmp_log_id' => $row['Cmp_log_id'],
        'CmpID' => $row['CmpID'],
        'user_verification' => $row['user_verification'] ?: 'pending',
        'rejection_reason' => $row['rejection_reason'],
        'brgy_solution_logs' => $row['brgy_solution_logs'],
        'performed_by' => $row['performed_by'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'logs' => $logs,
    'total' => count($logs)
]);
?>