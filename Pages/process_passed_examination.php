<?php
session_start();
require_once '../Process/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit();
    }
    header("Location: ../Login/login.php");
    exit();
}

// Determine if this is AJAX POST (with file) or legacy GET request
$isAjax = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($isAjax) {
    // Handle AJAX POST with file upload
    if (!isset($_POST['id']) || !isset($_POST['grant']) || !isset($_POST['level'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $applicationId = intval($_POST['id']);
    $grantAmount = floatval($_POST['grant']);
    $educationLevel = $_POST['level'];
} else {
    // Handle legacy GET request (for JHS/SHS without file upload)
    if (!isset($_GET['id']) || !isset($_GET['grant']) || !isset($_GET['level'])) {
        header("Location: SKpage.php");
        exit();
    }

    $applicationId = intval($_GET['id']);
    $grantAmount = floatval($_GET['grant']);
    $educationLevel = $_GET['level'];
}

// Log received parameters
error_log("=== Process Passed Examination ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Application ID: $applicationId");
error_log("Grant Amount: $grantAmount");
error_log("Education Level: $educationLevel");

$conn = getDBConnection();

// Validate education level and grant amount
$validCombinations = [
    'Junior High School' => 1000,
    'Senior High School' => 1200,
    'College A' => 3000,
    'College B' => 1500
];

error_log("Valid combinations: " . print_r($validCombinations, true));
error_log("Checking if '$educationLevel' exists with grant $grantAmount");

if (!isset($validCombinations[$educationLevel]) || $validCombinations[$educationLevel] != $grantAmount) {
    error_log("VALIDATION FAILED: Invalid combination");
    $errorMsg = "Invalid education level and grant combination. Received: $educationLevel with ₱$grantAmount";

    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit();
    } else {
        $_SESSION['error_message'] = $errorMsg;
        header("Location: SKpage.php");
        exit();
    }
}

error_log("Validation PASSED");

// Handle file upload for College A/B
$verificationDocPath = null;
if ($isAjax && isset($_FILES['verification_doc']) && $_FILES['verification_doc']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['verification_doc'];
    $uploadDir = '../uploads/college_verification/';

    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file type
    $allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $fileType = mime_content_type($file['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, JPG, PNG, DOC, DOCX are allowed.']);
        exit();
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
        exit();
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFilename = 'college_verify_' . $applicationId . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFilename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $verificationDocPath = $uniqueFilename;
        error_log("File uploaded successfully: $uploadPath");
    } else {
        error_log("File upload failed");
        echo json_encode(['success' => false, 'message' => 'Failed to upload verification document.']);
        exit();
    }
}

// Determine the actual education level to store (College A/B should be stored as "College")
$actualEducationLevel = $educationLevel;
if ($educationLevel === 'College A' || $educationLevel === 'College B') {
    $actualEducationLevel = 'College';
}

// Begin transaction
$conn->begin_transaction();

try {
    // First, check if PassedNotified column exists
    $checkCol = $conn->query("SHOW COLUMNS FROM scholarship LIKE 'PassedNotified'");
    $hasPassedNotified = ($checkCol && $checkCol->num_rows > 0);
    error_log("PassedNotified column exists: " . ($hasPassedNotified ? 'YES' : 'NO'));

    // Check if VerificationDocument column exists
    $checkVerDoc = $conn->query("SHOW COLUMNS FROM scholarship LIKE 'VerificationDocument'");
    $hasVerificationDoc = ($checkVerDoc && $checkVerDoc->num_rows > 0);
    error_log("VerificationDocument column exists: " . ($hasVerificationDoc ? 'YES' : 'NO'));

    // Build the UPDATE query based on available columns
    $sql = "UPDATE scholarship SET RequestStatus = 'Approved', ScholarshipGrant = ?";
    $params = [$grantAmountInt = (int)$grantAmount];
    $types = "i";

    if ($hasPassedNotified) {
        $sql .= ", PassedNotified = 0";
    }

    if ($hasVerificationDoc && $verificationDocPath !== null) {
        $sql .= ", VerificationDocument = ?";
        $params[] = $verificationDocPath;
        $types .= "s";
    }

    $sql .= " WHERE ApplicationID = ? AND RequestStatus = 'For Examination'";
    $params[] = $applicationId;
    $types .= "i";

    error_log("SQL Query: $sql");
    error_log("Binding params: " . print_r($params, true));

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    error_log("Affected rows: " . $stmt->affected_rows);

    if ($stmt->affected_rows === 0) {
        throw new Exception("No rows updated. Application may not be in 'For Examination' status.");
    }

    $stmt->close();

    error_log("UPDATE successful!");

    // Log activity
    $logSql = "INSERT INTO activity_logs (UserID, Action, Details, Timestamp) VALUES (?, 'Scholarship Approved - Passed Examination', ?, NOW())";
    $logStmt = $conn->prepare($logSql);
    if ($logStmt) {
        $details = "Approved scholarship application ID {$applicationId} with grant amount ₱{$grantAmount} for {$educationLevel}";
        if ($verificationDocPath) {
            $details .= " (Verification document: $verificationDocPath)";
        }
        $logStmt->bind_param("is", $_SESSION['user_id'], $details);
        $logStmt->execute();
        $logStmt->close();
    }

    // Commit transaction
    $conn->commit();

    $successMsg = "Scholarship application approved successfully! Grant: ₱" . number_format($grantAmount, 2);

    if ($isAjax) {
        echo json_encode([
            'success' => true,
            'message' => $successMsg,
            'applicationId' => $applicationId,
            'grantAmount' => $grantAmount,
            'educationLevel' => $educationLevel
        ]);
    } else {
        $_SESSION['success_message'] = $successMsg;
        // Add cache-busting parameter to force fresh page load
        header("Location: SKpage.php?refresh=" . time());
    }
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $errorMsg = "Error approving scholarship: " . $e->getMessage();
    error_log("ERROR: $errorMsg");

    // Delete uploaded file if transaction failed
    if ($verificationDocPath && file_exists('../uploads/college_verification/' . $verificationDocPath)) {
        unlink('../uploads/college_verification/' . $verificationDocPath);
        error_log("Deleted uploaded file due to transaction failure");
    }

    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        $_SESSION['error_message'] = $errorMsg;
        header("Location: SKpage.php?refresh=" . time());
    }
}

$conn->close();

if (!$isAjax) {
    exit();
}
