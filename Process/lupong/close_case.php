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
$outcome = strtolower(trim($_POST['outcome'] ?? ''));

if (empty($blotter_id) || !in_array($outcome, ['resolved', 'unresolved'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$status = ($outcome === 'resolved') ? 'lupong_resolved' : 'lupong_unresolved';

$sql = "UPDATE blottertbl SET status = ?, closed_at = NOW() WHERE blotter_id = ? AND escalated_to_lupong = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $status, $blotter_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => "Case closed as $outcome"]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to close case or case not found']);
}

$stmt->close();
?>