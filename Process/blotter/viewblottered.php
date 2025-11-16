<?php
session_name('BarangayStaffSession');
session_start();
require_once '../db_connection.php';
$conn = getDBConnection();

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(['error' => 'Missing participant ID']);
    exit;
}

// Fetch participant info
$stmt = $conn->prepare("SELECT * FROM blotter_participantstbl WHERE blotter_participant_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$participant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$participant) {
    echo json_encode(['error' => 'Participant not found']);
    exit;
}

// Fetch files for this blotter_id
$stmt = $conn->prepare("SELECT * FROM blotter_filestbl WHERE blotter_id = ?");
$stmt->bind_param("s", $participant['blotter_id']);
$stmt->execute();
$files = [];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $files[] = $row;
$stmt->close();

// Singleton connection closed by PHP

echo json_encode([
    'participant' => $participant,
    'files' => $files
]);
exit;
?>
