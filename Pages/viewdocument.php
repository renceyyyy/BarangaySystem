<?php

// ...existing code...
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

// adjust columns to match your docsreqtbl schema
$stmt = $connection->prepare("
    SELECT ReqId, Firstname, Lastname, Gender, ContactNo, Address, refno, Docutype, DateRequested, RequestStatus, CertificateImage
    FROM docsreqtbl
    WHERE ReqId = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (!empty($row['DateRequested'])) {
        $row['DateRequested'] = date('Y-m-d', strtotime($row['DateRequested']));
    }

    // convert CertificateImage blob to data URI if present
    if (!empty($row['CertificateImage'])) {
        $blob = $row['CertificateImage'];
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($blob) ?: 'image/jpeg';
        } else {
            $mime = 'image/jpeg';
        }
        $row['CertificateImage'] = 'data:' . $mime . ';base64,' . base64_encode($blob);
    } else {
        $row['CertificateImage'] = null;
    }

    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['error' => 'Record not found']);
}

$stmt->close();
$connection->close();
// ...existing code...
?>