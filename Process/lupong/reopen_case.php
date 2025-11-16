<?php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lupong') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once '../db_connection.php';
$conn = getDBConnection();

$blotter_id = $_POST['blotter_id'] ?? '';

if (empty($blotter_id)) {
    echo json_encode(['success' => false, 'error' => 'Blotter ID is required']);
    exit;
}

$sql = "UPDATE blottertbl SET status = 'lupong_in_progress' WHERE blotter_id = ? AND escalated_to_lupong = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $blotter_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Case re-opened for Lupong proceedings']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to re-open case or case not found']);
}

$stmt->close();
?>