<?php
// filepath: d:\xampp\htdocs\BarangaySampaguita\BarangaySystem\Process\blotter\create_hearing.php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');
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

// Get next hearing_no for this blotter
$sql = "SELECT MAX(hearing_no) AS max_no FROM blotter_hearingstbl WHERE blotter_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$stmt->bind_result($max_no);
$stmt->fetch();
$stmt->close();

$hearing_no = ($max_no === null) ? 1 : ($max_no + 1);

// Generate hearing_id in the format blotter_id-h{hearing_no}
$hearing_id = $blotter_id . '-h' . $hearing_no;

// Insert hearing with hearing_id
$sql = "INSERT INTO blotter_hearingstbl (hearing_id, blotter_id, hearing_no, schedule_start, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $hearing_id, $blotter_id, $hearing_no, $hearing_datetime);
if ($stmt->execute()) {
    // Optionally update blotter status
    $conn->query("UPDATE blottertbl SET status='hearing_scheduled' WHERE blotter_id='$blotter_id'");
    echo json_encode(['success' => true, 'hearing_no' => $hearing_no, 'hearing_id' => $hearing_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save hearing']);
}
$stmt->close();
// Singleton connection closed by PHP
?>
