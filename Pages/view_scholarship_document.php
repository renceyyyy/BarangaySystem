<?php
// Initialize session based on role (check if staff session exists first)
if (session_status() === PHP_SESSION_NONE) {
    // Try staff session first, then resident session, then default
    if (isset($_COOKIE['BarangayStaffSession'])) {
        session_name('BarangayStaffSession');
    } elseif (isset($_COOKIE['BarangayResidentSession'])) {
        session_name('BarangayResidentSession');
    }
    session_start();
}
require_once '../Process/db_connection.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in (either as staff or resident)
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
    'reason_file' => 'Reason'  // Reason field stores the file path
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
    $documentData = $row[$column];

    if (empty($documentData)) {
        echo json_encode(['success' => false, 'message' => 'Document is empty']);
        exit();
    }

    // Check if this is a file path (for reason_file) or BLOB data (for other documents)
    if ($documentType === 'reason_file') {
        // Reason is stored as a file path
        $filePath = $documentData;

        // Construct the full path to the file
        if (strpos($filePath, '../uploads/') === 0 || strpos($filePath, '/uploads/') === 0) {
            // Path is already relative from Pages directory
            $fullPath = __DIR__ . '/' . ltrim($filePath, '/');
        } else {
            // Assume it's stored as absolute path from webroot
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
        }

        // Check if file exists
        if (!file_exists($fullPath)) {
            echo json_encode(['success' => false, 'message' => 'File not found on server: ' . basename($filePath)]);
            exit();
        }

        // Read file content
        $documentBlob = file_get_contents($fullPath);

        if ($documentBlob === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to read file']);
            exit();
        }
    } else {
        // For other documents, data is stored as BLOB
        $documentBlob = $documentData;
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
