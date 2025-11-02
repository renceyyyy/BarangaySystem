<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

$id = (int) $_GET['id'];

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

$stmt = $connection->prepare("SELECT BsnssID, BusinessName, BusinessLoc, OwnerName, RequestType, refno, RequestedDate, RequestStatus, ProofPath FROM businesstbl WHERE BsnssID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // normalize date
    if (!empty($row['RequestedDate'])) {
        $row['RequestedDate'] = date('Y-m-d', strtotime($row['RequestedDate']));
    }

    // convert ProofPath blob to data URI if present
    if (!empty($row['ProofPath'])) {
        $blob = $row['ProofPath'];
        // detect mime type if possible
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($blob) ?: 'image/jpeg';
        } else {
            $mime = 'image/jpeg';
        }
        $row['ProofPath'] = 'data:' . $mime . ';base64,' . base64_encode($blob);
    } else {
        $row['ProofPath'] = null;
    }

    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['error' => 'Record not found']);
}

$stmt->close();
$connection->close();
?>