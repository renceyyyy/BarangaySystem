<?php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');
require_once '../db_connection.php';

$conn = getDBConnection();

$blotter_id = $_GET['id'] ?? $_GET['blotter_id'] ?? '';

if (!$blotter_id) {
    echo json_encode(['error' => 'Missing blotter ID']);
    exit;
}

// Get all lupong hearings for this blotter
$sql = "SELECT lupong_hearing_id, hearing_no, schedule_start, mediator_name, hearing_notes, outcome 
        FROM lupong_hearingstbl 
        WHERE blotter_id = ? 
        ORDER BY hearing_no ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$result = $stmt->get_result();

$hearings = [];
$current_hearing = null;

while ($row = $result->fetch_assoc()) {
    $hearings[] = $row;
    // The latest hearing without mediator/notes/outcome is the current one
    if (!$row['mediator_name'] && !$current_hearing) {
        $current_hearing = $row;
    }
}

echo json_encode([
    'hearings' => $hearings,
    'current_hearing' => $current_hearing
]);

$stmt->close();
?>