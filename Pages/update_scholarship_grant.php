<?php
// Minimal endpoint to add/update ScholarshipGrant for an application
header('Content-Type: application/json');

$connection = new mysqli("localhost", "root", "", "barangaydb");
if ($connection->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit();
}

$applicationId = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
$amountRaw = isset($_POST['amount']) ? trim($_POST['amount']) : '';
if ($applicationId <= 0 || $amountRaw === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

// Normalize amount
$amount = floatval(str_replace(',', '', $amountRaw));

// Ensure ScholarshipGrant column exists (add if missing)
$colCheck = $connection->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'scholarship' AND COLUMN_NAME = 'ScholarshipGrant'");
$colExists = false;
if ($colCheck && $rc = $colCheck->fetch_assoc()) {
    $colExists = ($rc['cnt'] > 0);
}

if (!$colExists) {
    // Add column as DECIMAL
    $alter = "ALTER TABLE scholarship ADD COLUMN ScholarshipGrant DECIMAL(12,2) NULL AFTER RequestStatus";
    if (!$connection->query($alter)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed adding column: ' . $connection->error]);
        exit();
    }
}

// Update the scholarship row
// First, fetch current RequestStatus so we can give a clearer error if needed
$checkStmt = $connection->prepare("SELECT RequestStatus FROM scholarship WHERE ApplicationID = ? LIMIT 1");
if (!$checkStmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed (status check): ' . $connection->error]);
    exit();
}
$checkStmt->bind_param('i', $applicationId);
$checkStmt->execute();
$checkStmt->bind_result($currentStatus);
if ($checkStmt->fetch() === null) {
    // no row
    echo json_encode(['status' => 'error', 'message' => 'Record not found']);
    $checkStmt->close();
    $connection->close();
    exit();
}
$checkStmt->close();

// If the record exists but is not Approved, return the current status to help debugging
if (strtolower(trim($currentStatus)) !== 'approved') {
    echo json_encode(['status' => 'error', 'message' => 'Cannot set grant: application status is not Approved', 'current_status' => $currentStatus]);
    $connection->close();
    exit();
}

// Proceed to update since the application is Approved
$stmt = $connection->prepare("UPDATE scholarship SET ScholarshipGrant = ? WHERE ApplicationID = ? AND RequestStatus = 'Approved'");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $connection->error]);
    exit();
}
$stmt->bind_param('di', $amount, $applicationId);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        // This should be rare now because we already checked status, but keep a defensive message
        echo json_encode(['status' => 'error', 'message' => 'No rows updated (record not found or already unchanged)']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$connection->close();

?>
