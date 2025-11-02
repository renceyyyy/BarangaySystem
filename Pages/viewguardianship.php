<?php
// Simple JSON endpoint to return a guardianship request by id
header('Content-Type: application/json; charset=utf-8');

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    $connection->close();
    exit;
}

$stmt = $connection->prepare("SELECT id, refno, applicant_name, request_type, child_name, child_age, child_address, request_date, RequestStatus FROM guardianshiptbl WHERE id = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed']);
    $connection->close();
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    // Normalize keys to match frontend expectations
    $data = [
        'id' => $row['id'],
        'refno' => $row['refno'],
        'applicant_name' => $row['applicant_name'],
        'request_type' => $row['request_type'],
        'child_name' => $row['child_name'],
        'child_age' => $row['child_age'],
        'child_address' => $row['child_address'],
        'request_date' => $row['request_date'],
        'RequestStatus' => $row['RequestStatus'],
    ];
    echo json_encode(['data' => $data]);
} else {
    echo json_encode(['error' => 'Record not found']);
}

$stmt->close();
$connection->close();
?>
