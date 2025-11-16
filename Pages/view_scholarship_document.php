<?php
session_start();
require_once '../Process/db_connection.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Validate input
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$applicationId = intval($_GET['id']);
$documentType = $_GET['type'];

// Validate document type
$validTypes = ['school_id', 'barangay_id', 'cor', 'parents_id', 'birth_certificate', 'reason_file'];
if (!in_array($documentType, $validTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid document type']);
    exit();
}

// Map document type to database column
$columnMap = [
    'school_id' => 'SchoolID',
    'barangay_id' => 'BaranggayID',
    'cor' => 'COR',
    'parents_id' => 'ParentsID',
    'birth_certificate' => 'BirthCertificate',
    'reason_file' => 'ReasonFile'
];

$column = $columnMap[$documentType];

try {
    $conn = getDBConnection();

    // Fetch the document from database
    $sql = "SELECT $column FROM scholarship WHERE ApplicationID = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit();
    }

    $row = $result->fetch_assoc();
    $documentBlob = $row[$column];

    if (empty($documentBlob)) {
        echo json_encode(['success' => false, 'message' => 'Document is empty']);
        exit();
    }

    // Detect MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($documentBlob);

    // Determine if it's a PDF or image
    $isPdf = ($mimeType === 'application/pdf');

    // Convert blob to base64 data URL
    $base64 = base64_encode($documentBlob);
    $dataUrl = "data:$mimeType;base64,$base64";

    echo json_encode([
        'success' => true,
        'dataUrl' => $dataUrl,
        'isPdf' => $isPdf,
        'mimeType' => $mimeType
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Error viewing document: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
