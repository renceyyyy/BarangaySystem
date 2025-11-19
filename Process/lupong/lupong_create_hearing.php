<?php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
require_once '../db_connection.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$blotter_id = $_POST['blotter_id'] ?? '';
$hearing_datetime = $_POST['hearing_datetime'] ?? '';

if (!$blotter_id || !$hearing_datetime) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Validate hearing_datetime is not in the past
$selected_ts = strtotime($hearing_datetime);
if ($selected_ts === false || $selected_ts < time()) {
    echo json_encode(['success' => false, 'error' => 'Selected date/time cannot be in the past.']);
    exit;
}

// Get next hearing_no for this blotter
$sql = "SELECT MAX(hearing_no) AS max_no FROM lupong_hearingstbl WHERE blotter_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$stmt->bind_result($max_no);
$stmt->fetch();
$stmt->close();

$hearing_no = ($max_no === null) ? 1 : ($max_no + 1);

// Generate lupong_hearing_id in format blotter_id-lh{hearing_no}
$lupong_hearing_id = $blotter_id . '-lh' . $hearing_no;

// Insert hearing
$sql = "INSERT INTO lupong_hearingstbl (lupong_hearing_id, blotter_id, hearing_no, schedule_start, created_at) 
        VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $lupong_hearing_id, $blotter_id, $hearing_no, $hearing_datetime);

if ($stmt->execute()) {
    // Update blotter status to lupong_in_progress
    $conn->query("UPDATE blottertbl SET status='lupong_in_progress' WHERE blotter_id='$blotter_id'");
    echo json_encode(['success' => true, 'message' => 'Hearing scheduled successfully', 'hearing_no' => $hearing_no, 'lupong_hearing_id' => $lupong_hearing_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to schedule hearing']);
}
$stmt->close();
?>