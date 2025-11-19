<?php
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
$lupong_hearing_id = $_POST['lupong_hearing_id'] ?? '';
$mediator_name = $_POST['mediator_name'] ?? '';
$hearing_notes = $_POST['hearing_notes'] ?? '';
$outcome = $_POST['outcome'] ?? '';

if (!$blotter_id || !$lupong_hearing_id || !$mediator_name || !$hearing_notes || !$outcome) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Update hearing record
$sql = "UPDATE lupong_hearingstbl 
        SET mediator_name = ?, hearing_notes = ?, outcome = ?, updated_at = NOW() 
        WHERE lupong_hearing_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $mediator_name, $hearing_notes, $outcome, $lupong_hearing_id);

if ($stmt->execute()) {

    // if hearing outcome is "amicably_settled", update blotter status to "lupong_resolved"
    if ($outcome === 'amicably_settled') {
        $update_sql = "UPDATE blottertbl SET status = 'lupong_resolved', resolution_notes = ?, closed_at = NOW() WHERE blotter_id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {
             $update_stmt->bind_param("ss", $hearing_notes, $blotter_id);
            if (!$update_stmt->execute()) {
                error_log("Failed to update blottertbl status for blotter_id={$blotter_id}: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            error_log("Failed to update blotter status prepare statement for blotter_id={$blotter_id}: " . $conn->error);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Hearing recorded successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to record hearing']);
}
$stmt->close();
?>