<?php
require_once __DIR__ . '/../config/session_resident.php';
require_once '../Process/db_connection.php';
require_once '../Process/user_activity_logger.php';
require_once './Terms&Conditions/Terms&Conditons.php';
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

// NEW FEATURE: Check for existing pending request and handle update mode
$user_id = $_SESSION['user_id'];
$isUpdateMode = false;
$updateRefNo = '';
$pendingRequest = null;
$errors = []; // Initialize errors array
$isUpdateSuccess = false; // Track if update was successful

// Check if this is update mode
if (isset($_GET['update'])) {
    $updateRefNo = trim($_GET['update']);
    $isUpdateMode = true;

    // Fetch the pending request data
    $check_sql = "SELECT * FROM docsreqtbl WHERE refno = ? AND Userid = ? AND RequestStatus = 'Pending' LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("si", $updateRefNo, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
        } else {
            // Invalid update request
            $_SESSION['error_message'] = "Invalid update request or request is no longer pending.";
            header("Location: ../Pages/UserReports.php");
            exit();
        }
        $check_stmt->close();
    }
}

// Get all pending document types for this user
$pending_doc_types = [];
$blocked_doc_types = []; // For one-time only documents

if (!$isUpdateMode) {
    // For new requests: get all pending document types
    $pending_check_sql = "SELECT DISTINCT DocuType FROM docsreqtbl WHERE Userid = ? AND RequestStatus = 'Pending'";
    $pending_stmt = $conn->prepare($pending_check_sql);
    if ($pending_stmt) {
        $pending_stmt->bind_param("i", $user_id);
        $pending_stmt->execute();
        $pending_result = $pending_stmt->get_result();
        while ($pending_row = $pending_result->fetch_assoc()) {
            $pending_doc_types[] = $pending_row['DocuType'];
        }
        $pending_stmt->close();
    }
} else {
    // For update mode: get pending document types excluding the current request being updated
    $pending_check_sql = "SELECT DISTINCT DocuType FROM docsreqtbl WHERE Userid = ? AND RequestStatus = 'Pending' AND refno != ?";
    $pending_stmt = $conn->prepare($pending_check_sql);
    if ($pending_stmt) {
        $pending_stmt->bind_param("is", $user_id, $updateRefNo);
        $pending_stmt->execute();
        $pending_result = $pending_stmt->get_result();
        while ($pending_row = $pending_result->fetch_assoc()) {
            $pending_doc_types[] = $pending_row['DocuType'];
        }
        $pending_stmt->close();
    }
}

if (!$isUpdateMode) {
    // Check for First Time Job Seeker requests (any status except declined) - one time only
    $ftjs_check_sql = "SELECT DISTINCT DocuType FROM docsreqtbl WHERE Userid = ? AND DocuType = 'First Time Job Seeker' AND RequestStatus != 'Declined'";
    $ftjs_stmt = $conn->prepare($ftjs_check_sql);
    if ($ftjs_stmt) {
        $ftjs_stmt->bind_param("i", $user_id);
        $ftjs_stmt->execute();
        $ftjs_result = $ftjs_stmt->get_result();
        while ($ftjs_row = $ftjs_result->fetch_assoc()) {
            $blocked_doc_types[] = $ftjs_row['DocuType'];
        }
        $ftjs_stmt->close();
    }
}

// For update mode, check if updating First Time Job Seeker and user already has approved/completed one
$update_blocked_doc_types = [];
if ($isUpdateMode) {
    // Check if user has any approved/completed First Time Job Seeker requests (excluding the current pending one being updated)
    $ftjs_update_check_sql = "SELECT COUNT(*) as count FROM docsreqtbl WHERE Userid = ? AND DocuType = 'First Time Job Seeker' AND RequestStatus IN ('Approved', 'Completed', 'Released') AND refno != ?";
    $ftjs_update_stmt = $conn->prepare($ftjs_update_check_sql);
    if ($ftjs_update_stmt) {
        $ftjs_update_stmt->bind_param("is", $user_id, $updateRefNo);
        $ftjs_update_stmt->execute();
        $ftjs_update_result = $ftjs_update_stmt->get_result();
        $ftjs_update_row = $ftjs_update_result->fetch_assoc();

        if ($ftjs_update_row['count'] > 0) {
            $update_blocked_doc_types[] = 'First Time Job Seeker';
        }
        $ftjs_update_stmt->close();
    }
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$user_data = [];

// If update mode, use pending request data
if ($isUpdateMode && $pendingRequest) {
    $firstname = $pendingRequest['Firstname'] ?? '';
    $lastname = $pendingRequest['Lastname'] ?? '';
    $gender = $pendingRequest['Gender'] ?? '';
    $contactNo = $pendingRequest['ContactNo'] ?? ($pendingRequest['ContactNO'] ?? ''); // Handle both cases
    $address = $pendingRequest['Address'] ?? '';
    $civilStatus = $pendingRequest['CivilStatus'] ?? '';
    $reqPurpose = $pendingRequest['ReqPurpose'] ?? '';
    $yearsOfResidency = $pendingRequest['YearsOfResidency'] ?? '';
    $selectedDocType = $pendingRequest['DocuType'] ?? '';
    $birthdate = $pendingRequest['BirthDate'] ?? '';

    // FIXED: In update mode, only show the single document type from this pending request
    // The user can check this one to keep it, or uncheck it and check others to replace/add
    $doctypes = !empty($selectedDocType) ? [$selectedDocType] : [];

    // Handle empty birthdate in update mode - fallback to user profile
    if (empty($birthdate) || $birthdate === '0000-00-00' || $birthdate === '0') {
        // Fetch birthdate from user profile
        $user_birthdate_sql = "SELECT Birthdate FROM userloginfo WHERE UserID = ?";
        $user_birthdate_stmt = $conn->prepare($user_birthdate_sql);
        if ($user_birthdate_stmt) {
            $user_birthdate_stmt->bind_param("i", $user_id);
            $user_birthdate_stmt->execute();
            $user_birthdate_result = $user_birthdate_stmt->get_result();
            if ($user_birthdate_result->num_rows > 0) {
                $user_birthdate_data = $user_birthdate_result->fetch_assoc();
                $birthdate = $user_birthdate_data['Birthdate'] ?? '';
                if ($birthdate === '0000-00-00' || $birthdate === '0') {
                    $birthdate = '';
                }
            }
            $user_birthdate_stmt->close();
        }
    }
} else {
    // Fetch from user profile
    $user_sql = "SELECT Firstname, Lastname, Gender, ContactNo, Address, CivilStatus, Birthdate FROM userloginfo WHERE UserID = ?";
    $user_stmt = $conn->prepare($user_sql);
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();

            // Pre-populate form fields with user data
            $firstname = $user_data['Firstname'] ?? '';
            $lastname = $user_data['Lastname'] ?? '';
            $gender = $user_data['Gender'] ?? '';
            $contactNo = $user_data['ContactNo'] ?? '';
            $address = $user_data['Address'] ?? '';
            $civilStatus = $user_data['CivilStatus'] ?? '';
            $birthdate = $user_data['Birthdate'] ?? '';

            // Check for default values and replace them with empty strings
            if ($firstname === 'uncompleted')
                $firstname = '';
            if ($lastname === 'uncompleted')
                $lastname = '';
            if ($gender === 'uncompleted')
                $gender = '';
            if ($contactNo === '0')
                $contactNo = '';
            if ($address === 'uncompleted')
                $address = '';
            if ($civilStatus === 'uncompleted')
                $civilStatus = '';
            if ($birthdate === '0000-00-00' || $birthdate === '0')
                $birthdate = '';
        }
        $user_stmt->close();
    }
    // Initialize form variables for new request
    $reqPurpose = '';
    $yearsOfResidency = '';
    $selectedDocType = '';
    $doctypes = []; // Initialize empty array for new requests
    $birthdate = $user_data['Birthdate'] ?? '';
    if ($birthdate === '0000-00-00' || $birthdate === '0') {
        $birthdate = '';
    }
}

// Handle document request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["doc_request"])) {
    // Get document types early so we can validate them
    $doctypes_to_submit = isset($_POST["doctype"]) ? (array) $_POST["doctype"] : [];

    // Check for First Time Job Seeker restriction in update mode
    if ($isUpdateMode && !empty($doctypes_to_submit)) {
        foreach ($doctypes_to_submit as $doctype_check) {
            $doctype_check = trim($doctype_check);
            if ($doctype_check === 'First Time Job Seeker' && in_array('First Time Job Seeker', $update_blocked_doc_types)) {
                $errors[] = "You cannot select 'First Time Job Seeker' because you already have an approved/completed request for this document type. This is a one-time only request.";
            }
        }
    }

    // Check for pending requests of SPECIFIC document types - applies to both new and update modes
    if (!empty($doctypes_to_submit)) {
        foreach ($doctypes_to_submit as $doctype_check) {
            $doctype_check = trim($doctype_check);

            if ($isUpdateMode) {
                // For update mode: Check if there are OTHER pending requests for the same document type (excluding current request)
                $pending_check = "SELECT refno FROM docsreqtbl WHERE Userid = ? AND DocuType = ? AND RequestStatus = 'Pending' AND refno != ? LIMIT 1";
                $pending_check_stmt = $conn->prepare($pending_check);
                if ($pending_check_stmt) {
                    $pending_check_stmt->bind_param("iss", $user_id, $doctype_check, $updateRefNo);
                    $pending_check_stmt->execute();
                    $pending_check_result = $pending_check_stmt->get_result();
                    if ($pending_check_result->num_rows > 0) {
                        $pending_data = $pending_check_result->fetch_assoc();
                        $errors[] = "You cannot select '" . htmlspecialchars($doctype_check) . "' because you already have another pending request for this document type (Ref: " . htmlspecialchars($pending_data['refno']) . "). You cannot have multiple pending requests for the same document type.";
                    }
                    $pending_check_stmt->close();
                }
            } else {
                // For new requests: Check if there are ANY pending requests for the document type
                $pending_check = "SELECT refno FROM docsreqtbl WHERE Userid = ? AND DocuType = ? AND RequestStatus = 'Pending' LIMIT 1";
                $pending_check_stmt = $conn->prepare($pending_check);
                if ($pending_check_stmt) {
                    $pending_check_stmt->bind_param("is", $user_id, $doctype_check);
                    $pending_check_stmt->execute();
                    $pending_check_result = $pending_check_stmt->get_result();
                    if ($pending_check_result->num_rows > 0) {
                        $pending_data = $pending_check_result->fetch_assoc();
                        $errors[] = "You have a pending request for " . htmlspecialchars($doctype_check) . " (Ref: " . htmlspecialchars($pending_data['refno']) . "). Please wait for approval or update your existing request before submitting a new one for this document type.";
                    }
                    $pending_check_stmt->close();
                }
            }
        }
    }

    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }

    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Get document types
    $doctypes = isset($_POST["doctype"]) ? (array) $_POST["doctype"] : [];

    if (empty($doctypes)) {
        $errors[] = "Please select at least one document type!";
    }

    // Prepare and sanitize input fields
    $firstname = trim($_POST["firstname"] ?? '');
    $lastname = trim($_POST["lastname"] ?? '');
    $gender = trim($_POST["gender"] ?? '');
    $contactNo = trim($_POST["contactNo"] ?? '');
    $address = trim($_POST["address"] ?? '');
    $reqPurpose = trim($_POST["reqPurpose"] ?? '');
    $yearsOfResidency = trim($_POST["yearsOfResidency"] ?? '');
    $civilStatus = trim($_POST["civilStatus"] ?? '');
    $birthdate = trim($_POST["birthdate"] ?? '');

    // Validate required fields
    $required = [
        'First Name' => $firstname,
        'Last Name' => $lastname,
        'Gender' => $gender,
        'Contact Number' => $contactNo,
        'Address' => $address,
        'Purpose' => $reqPurpose,
        'Years of Residency' => $yearsOfResidency,
        'Civil Status' => $civilStatus
    ];

    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        $errors[] = "Missing required fields: " . implode(", ", $missing);
    }

    // Validate years of residency is a number
    if (!empty($yearsOfResidency) && !is_numeric($yearsOfResidency)) {
        $errors[] = "Years of residency must be a number.";
    }

    // Check if "No Birth Certificate" is selected and validate birthdate
    if (in_array('No Birth Certificate', $doctypes) && empty($birthdate)) {
        $errors[] = "Birth Date is required when requesting No Birth Certificate.";
    }

    // Check if ONLY "No Birth Certificate" is selected (no image required)
    $onlyNoBirthCert = (count($doctypes) === 1 && in_array('No Birth Certificate', $doctypes));
    $requiresImage = !$onlyNoBirthCert; // Image not required if only No Birth Certificate

    // Handle file upload
    $certificateImage = null;
    $file_upload_error = '';
    $hasNewFile = false;

    if (isset($_FILES['certificateImage'])) {
        if ($_FILES['certificateImage']['error'] === UPLOAD_ERR_OK) {
            $hasNewFile = true;
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $fileType = $_FILES['certificateImage']['type'];

            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid file type. Only JPEG, PNG, GIF, and PDF are allowed.";
            }

            // Validate file size (5MB limit)
            if ($_FILES['certificateImage']['size'] > 5242880) {
                $errors[] = "File size must be less than 5MB.";
            }

            // Read file content
            if (empty($errors)) {
                $certificateImage = file_get_contents($_FILES['certificateImage']['tmp_name']);
            }
        } elseif ($_FILES['certificateImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "File upload error occurred.";
        } elseif (!$isUpdateMode && $requiresImage) {
            // Only require file for new requests AND if not "No Birth Certificate" only
            $errors[] = "Certificate proof image is required.";
        }
    } elseif (!$isUpdateMode && $requiresImage) {
        // Only require file if image is needed
        $errors[] = "Certificate proof image is required.";
    }

    // If no errors, process the request
    if (empty($errors)) {
        $successCount = 0;

        // Start transaction
        $conn->begin_transaction();

        try {
            if ($isUpdateMode) {
                // FIXED: Update mode - handle multiple document types correctly
                // Get the original document type from the pending request
                $originalDocType = $pendingRequest['DocuType'] ?? '';

                // Check if user is updating to a different document type or adding new ones
                $newDocTypes = [];
                $updateExisting = false;

                foreach ($doctypes as $doctype) {
                    $doctype = trim($doctype);
                    if ($doctype === $originalDocType) {
                        $updateExisting = true; // Keep updating the original record
                    } else {
                        $newDocTypes[] = $doctype; // New document type to create separate request
                    }
                }

                // If original doc type is still selected, update it
                if ($updateExisting) {
                    if ($hasNewFile && $certificateImage) {
                        // Update with new image
                        $sql = "UPDATE docsreqtbl SET 
                            Firstname = ?, Lastname = ?,
                            Gender = ?, ContactNO = ?, ReqPurpose = ?, 
                            Address = ?, CertificateImage = ?, 
                            YearsOfResidency = ?, CivilStatus = ?, BirthDate = ?
                            WHERE refno = ? AND Userid = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $stmt->bind_param(
                            "ssssssbiissi",
                            $firstname,
                            $lastname,
                            $gender,
                            $contactNo,
                            $reqPurpose,
                            $address,
                            $certificateImage,
                            $yearsOfResidency,
                            $civilStatus,
                            $birthdate,
                            $updateRefNo,
                            $userId
                        );

                        // Send long blob data
                        $stmt->send_long_data(6, $certificateImage);
                    } else {
                        // Update without changing image
                        $sql = "UPDATE docsreqtbl SET 
                            Firstname = ?, Lastname = ?,
                            Gender = ?, ContactNO = ?, ReqPurpose = ?, 
                            Address = ?, YearsOfResidency = ?, CivilStatus = ?, BirthDate = ?
                            WHERE refno = ? AND Userid = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $stmt->bind_param(
                            "ssssssisssi",
                            $firstname,
                            $lastname,
                            $gender,
                            $contactNo,
                            $reqPurpose,
                            $address,
                            $yearsOfResidency,
                            $civilStatus,
                            $birthdate,
                            $updateRefNo,
                            $userId
                        );
                    }

                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }

                    $successCount++;
                    $stmt->close();

                    // Log user activity for update
                    logUserActivity(
                        'Government document request updated',
                        'government_document_update',
                        [
                            'document_type' => $originalDocType,
                            'reference_no' => $updateRefNo,
                            'action' => 'update'
                        ]
                    );
                }

                // If user added NEW document types, create separate requests for them
                if (!empty($newDocTypes)) {
                    // IMPROVED: Handle images for new document types intelligently
                    $imageToUse = null;

                    if ($hasNewFile && $certificateImage) {
                        // User uploaded a new image - use it for new documents
                        $imageToUse = $certificateImage;
                    } else {
                        // No new image uploaded - reuse the existing image from pending request
                        // This is helpful so users don't need to upload the same proof again
                        $imageToUse = $pendingRequest['CertificateImage'] ?? null;
                    }

                    foreach ($newDocTypes as $newDocType) {
                        // Generate new reference number for each new document type
                        $newRefNo = date('Ymd') . rand(1000, 9999);

                        // Check if this is "First Time Job Seeker" and user already has one
                        if ($newDocType === "First Time Job Seeker") {
                            $check_dup_sql = "SELECT COUNT(*) as count FROM docsreqtbl WHERE Userid = ? AND DocuType = ?";
                            $check_dup_stmt = $conn->prepare($check_dup_sql);
                            if ($check_dup_stmt) {
                                $check_dup_stmt->bind_param("is", $userId, $newDocType);
                                $check_dup_stmt->execute();
                                $check_dup_result = $check_dup_stmt->get_result();
                                $check_dup_row = $check_dup_result->fetch_assoc();

                                if ($check_dup_row['count'] > 0) {
                                    throw new Exception("You've already requested a First Time Job Seeker certificate. This is a one-time request only.");
                                }
                                $check_dup_stmt->close();
                            }
                        }

                        // Insert new request for the new document type
                        $sql = "INSERT INTO docsreqtbl (
                            Userid, DocuType, Firstname, Lastname,
                            Gender, ContactNO, ReqPurpose, Address, refno, CertificateImage, 
                            YearsOfResidency, CivilStatus, BirthDate, DateRequested
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $null = null;
                        $stmt->bind_param(
                            "issssssssbiss",
                            $userId,
                            $newDocType,
                            $firstname,
                            $lastname,
                            $gender,
                            $contactNo,
                            $reqPurpose,
                            $address,
                            $newRefNo,
                            $null,
                            $yearsOfResidency,
                            $civilStatus,
                            $birthdate
                        );

                        // Send long blob data
                        if ($imageToUse) {
                            $stmt->send_long_data(9, $imageToUse);
                        }

                        if (!$stmt->execute()) {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }

                        // Log user activity for new request
                        logUserActivity(
                            'Government document requested (added during update)',
                            'document_request',
                            [
                                'document_type' => $newDocType,
                                'reference_no' => $newRefNo,
                                'action' => 'new_from_update'
                            ]
                        );

                        $successCount++;
                        $stmt->close();
                    }
                }

                // If user removed the original document type and only selected new ones
                if (!$updateExisting && !empty($newDocTypes)) {
                    // Delete the original pending request since it's being replaced
                    $delete_sql = "DELETE FROM docsreqtbl WHERE refno = ? AND Userid = ? AND RequestStatus = 'Pending'";
                    $delete_stmt = $conn->prepare($delete_sql);
                    if ($delete_stmt) {
                        $delete_stmt->bind_param("si", $updateRefNo, $userId);
                        $delete_stmt->execute();
                        $delete_stmt->close();

                        logUserActivity(
                            'Government document request replaced',
                            'government_document_delete',
                            [
                                'document_type' => $originalDocType,
                                'reference_no' => $updateRefNo,
                                'action' => 'replaced_with_new'
                            ]
                        );
                    }
                }

                $conn->commit();

                if ($successCount > 0) {
                    $success = true;
                    $success_ref_no = $updateRefNo;
                    $isUpdateSuccess = true; // Flag to show update success message
                }
            } else {
                // INSERT new request
                $refno = date('Ymd') . rand(1000, 9999);

                foreach ($doctypes as $doctype) {
                    $doctype = trim($doctype);

                    // Check if this is "First Time Job Seeker" and user already has one
                    if ($doctype === "First Time Job Seeker") {
                        $check_dup_sql = "SELECT COUNT(*) as count FROM docsreqtbl WHERE Userid = ? AND DocuType = ?";
                        $check_dup_stmt = $conn->prepare($check_dup_sql);
                        if ($check_dup_stmt) {
                            $check_dup_stmt->bind_param("is", $userId, $doctype);
                            $check_dup_stmt->execute();
                            $check_dup_result = $check_dup_stmt->get_result();
                            $check_dup_row = $check_dup_result->fetch_assoc();

                            if ($check_dup_row['count'] > 0) {
                                // User already has a First Time Job Seeker request
                                throw new Exception("You've already requested a First Time Job Seeker certificate. This is a one-time request only. For further information, please visit the barangay or contact us at Telephone: 86380301.");
                            }
                            $check_dup_stmt->close();
                        }
                    }

                    $sql = "INSERT INTO docsreqtbl (
                        Userid, DocuType, Firstname, Lastname,
                        Gender, ContactNO, ReqPurpose, Address, refno, CertificateImage, 
                        YearsOfResidency, CivilStatus, BirthDate, DateRequested
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $null = null;
                    $stmt->bind_param(
                        "issssssssbiss",
                        $userId,
                        $doctype,
                        $firstname,
                        $lastname,
                        $gender,
                        $contactNo,
                        $reqPurpose,
                        $address,
                        $refno,
                        $null,
                        $yearsOfResidency,
                        $civilStatus,
                        $birthdate
                    );

                    // Send long blob data
                    $stmt->send_long_data(9, $certificateImage);

                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }

                    // Log user activity
                    logUserActivity(
                        'Government document requested',
                        'document_request',
                        [
                            'document_type' => $doctype,
                            'reference_no' => $refno
                        ]
                    );

                    $successCount++;
                    $stmt->close();
                }

                $conn->commit();

                if ($successCount > 0) {
                    $success = true;
                    $success_ref_no = $refno;

                    // Reset form but keep user data
                    $reqPurpose = "";
                    $doctypes = [];
                    $yearsOfResidency = "";
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Government Documents</title>
    <link rel="stylesheet" href="./Style/Applications&RequestStyle.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .file-input {
            margin-top: 5px;
        }

        .required {
            color: red;
        }

        .error {
            color: red;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            color: green;
            background-color: #e6ffe6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .form-section h2 {
            color: #444;
            margin-bottom: 15px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            margin: 0;
        }

        .checkbox-item input[type="checkbox"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .checkbox-item input[type="checkbox"]:disabled+label {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .file-info {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        .user-info-note {
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 10000;
            display: none;
            min-width: 300px;
        }

        .success-message.show {
            display: block;
        }

        .success-message h3 {
            color: #4CAF50;
            margin-top: 0;
        }

        .success-message p {
            color: #666;
            margin: 10px 0;
        }

        .success-message #closeSuccessMessage {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }

        .success-message #closeSuccessMessage:hover {
            background-color: #45a049;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
        }

        .overlay.show {
            display: block;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Document Request Submitted Successfully!</h3>
        <p>Your document request has been received.</p>
        <p>Reference Number: <strong id="refNo"></strong></p>
        <p>Please keep this reference number for tracking your request.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>

    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Government Document Request' : 'Government Document Request Form'; ?>
        </h1>

        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460;">
                <strong>Update Mode:</strong> You are updating your pending request (Ref:
                <?php echo htmlspecialchars($updateRefNo); ?>).
                Modify the information below and click "Update Request" to save changes.
            </div>
        <?php else: ?>
            <div class="user-info-note">
                <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and
                update if necessary.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>Notice:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" id="documentForm">
            <input type="hidden" name="doc_request" value="1">

            <div class="form-section">
                <h2>Personal Information</h2>

                <div class="form-group">
                    <label for="firstname">First Name <span class="required">*</span></label>
                    <input type="text" id="firstname" name="firstname"
                        value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastname" name="lastname"
                        value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($gender) && $gender === 'Male') ? 'selected' : ''; ?>>
                                Male</option>
                            <option value="Female" <?php echo (isset($gender) && $gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($gender) && $gender === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="civilStatus">Civil Status <span class="required">*</span></label>
                        <select id="civilStatus" name="civilStatus" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single" <?php echo (isset($civilStatus) && $civilStatus === 'Single') ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo (isset($civilStatus) && $civilStatus === 'Married') ? 'selected' : ''; ?>>Married</option>
                            <option value="Divorced" <?php echo (isset($civilStatus) && $civilStatus === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo (isset($civilStatus) && $civilStatus === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            <option value="Separated" <?php echo (isset($civilStatus) && $civilStatus === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="yearsOfResidency">Years of Residency in this Barangay <span
                            class="required">*</span></label>
                    <input type="number" id="yearsOfResidency" name="yearsOfResidency"
                        value="<?php echo htmlspecialchars($yearsOfResidency ?? ''); ?>" min="0" max="100" step="1"
                        placeholder="Enter number of years" required>
                </div>

                <div class="form-group">
                    <label for="contactNo">Contact Number <span class="required">*</span></label>
                    <input type="tel" id="contactNo" name="contactNo"
                        value="<?php echo htmlspecialchars($contactNo ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <textarea id="address" name="address"
                        required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Document Details</h2>

                <?php if (!empty($pending_doc_types)): ?>
                    <div
                        style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 10px 15px; margin-bottom: 15px; font-size: 0.9em;">
                        <i class="fas fa-info-circle" style="color: #856404; margin-right: 8px;"></i>
                        <strong style="color: #856404;">Note:</strong>
                        <span style="color: #856404;">
                            <?php if ($isUpdateMode): ?>
                                Some document types are disabled because you have other pending requests for them. You cannot
                                have multiple pending requests for the same document type.
                            <?php else: ?>
                                Some document types are disabled because you already have pending requests for them.
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Select Document Type(s) <span class="required">*</span></label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="cedula" name="doctype[]" value="Cedula" <?php
                            $checked = (isset($doctypes) && in_array('Cedula', $doctypes)) ? 'checked' : '';
                            $disabled = in_array('Cedula', $pending_doc_types) ? 'disabled' : '';
                            echo $checked . ' ' . $disabled;
                            ?>>
                            <label for="cedula">
                                Cedula
                                <?php if (in_array('Cedula', $pending_doc_types)): ?>
                                    <span style="color: #d9534f; font-size: 0.9em;"> (Already have pending request)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="barangay_certificate" name="doctype[]"
                                value="Barangay Certificate" <?php
                                $checked = (isset($doctypes) && in_array('Barangay Certificate', $doctypes)) ? 'checked' : '';
                                $disabled = in_array('Barangay Certificate', $pending_doc_types) ? 'disabled' : '';
                                echo $checked . ' ' . $disabled;
                                ?>>
                            <label for="barangay_certificate">
                                Barangay Certificate
                                <?php if (in_array('Barangay Certificate', $pending_doc_types)): ?>
                                    <span style="color: #d9534f; font-size: 0.9em;"> (Already have pending request)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="employment_form" name="doctype[]" value="Employment Form" <?php
                            $checked = (isset($doctypes) && in_array('Employment Form', $doctypes)) ? 'checked' : '';
                            $disabled = in_array('Employment Form', $pending_doc_types) ? 'disabled' : '';
                            echo $checked . ' ' . $disabled;
                            ?>>
                            <label for="employment_form">
                                Employment Form
                                <?php if (in_array('Employment Form', $pending_doc_types)): ?>
                                    <span style="color: #d9534f; font-size: 0.9em;"> (Already have pending request)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="first_time_job_seeker" name="doctype[]"
                                value="First Time Job Seeker" <?php
                                $checked = (isset($doctypes) && in_array('First Time Job Seeker', $doctypes)) ? 'checked' : '';
                                $disabled = '';
                                $message = '';

                                if (in_array('First Time Job Seeker', $pending_doc_types)) {
                                    $disabled = 'disabled';
                                    $message = ' (Already have pending request)';
                                } elseif (!$isUpdateMode && in_array('First Time Job Seeker', $blocked_doc_types)) {
                                    $disabled = 'disabled';
                                    $message = ' (Already approved/completed - one time only)';
                                } elseif ($isUpdateMode && in_array('First Time Job Seeker', $update_blocked_doc_types)) {
                                    $disabled = 'disabled';
                                    $message = ' (You already have an approved/completed request - one time only)';
                                }

                                echo $checked . ' ' . $disabled;
                                ?>>
                            <label for="first_time_job_seeker">
                                First Time Job Seeker
                                <?php if (!empty($message)): ?>
                                    <span
                                        style="color: #d9534f; font-size: 0.9em;"><?php echo htmlspecialchars($message); ?></span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="no_birth_certificate" name="doctype[]"
                                value="No Birth Certificate" <?php
                                $checked = (isset($doctypes) && in_array('No Birth Certificate', $doctypes)) ? 'checked' : '';
                                $disabled = in_array('No Birth Certificate', $pending_doc_types) ? 'disabled' : '';
                                echo $checked . ' ' . $disabled;
                                ?>>
                            <label for="no_birth_certificate">
                                No Birth Certificate
                                <?php if (in_array('No Birth Certificate', $pending_doc_types)): ?>
                                    <span style="color: #d9534f; font-size: 0.9em;"> (Already have pending request)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reqPurpose">Purpose of Request <span class="required">*</span></label>
                    <textarea id="reqPurpose" name="reqPurpose"
                        placeholder="Please specify the purpose for requesting these documents..."
                        required><?php echo htmlspecialchars($reqPurpose ?? ''); ?></textarea>
                </div>

                <!-- Birth Date field - only shows when No Birth Certificate is selected -->
                <div class="form-group" id="birthdateGroup" style="display: none;">
                    <label for="birthdate">Birth Date <span class="required">*</span></label>
                    <input type="date" id="birthdate" name="birthdate"
                        value="<?php echo htmlspecialchars($birthdate ?? ''); ?>">
                    <div class="file-info">
                        This field is automatically filled from your profile. You can modify it if needed.
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Required Proof</h2>

                <div class="form-group" id="certificateImageGroup">
                    <label for="certificateImage" class="form-label">
                        <strong>Valid Identification or Supporting Document</strong>
                        <span class="required" id="imageRequired">*</span>
                        <br>
                        <small class="text-muted">
                            Please upload any document that clearly shows your <em>name</em> and <em>address</em> within
                            the barangay.
                        </small>
                    </label>

                    <input type="file" id="certificateImage" name="certificateImage" class="file-input"
                        accept=".jpg,.jpeg,.png,.gif,.pdf">

                    <div class="file-info text-muted">
                        <small>Supported formats: JPG, PNG, GIF, PDF &nbsp;|&nbsp; Max file size: 5MB</small>
                    </div>
                </div>

            </div>

            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('governmentDocsForm'); ?>

            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                <a href="../Pages/landingpage.php" class="btn btn-secondary"
                    style="background-color: #6c757d; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn" id="submitBtn">
                    <?php echo $isUpdateMode ? 'Update Request' : 'Submit Request'; ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('documentForm');
            const certificateImageGroup = document.getElementById('certificateImageGroup');
            const certificateImageInput = document.getElementById('certificateImage');
            const imageRequired = document.getElementById('imageRequired');

            // Validation functions
            function validateName(fieldId) {
                const field = document.getElementById(fieldId);
                const value = field.value.trim();
                const namePattern = /^[a-zA-Z\s\-.]+$/;
                
                if (value.length < 2) {
                    field.setCustomValidity('Name must be at least 2 characters long');
                    return false;
                } else if (value.length > 50) {
                    field.setCustomValidity('Name must not exceed 50 characters');
                    return false;
                } else if (!namePattern.test(value)) {
                    field.setCustomValidity('Name can only contain letters, spaces, hyphens, and periods');
                    return false;
                } else {
                    field.setCustomValidity('');
                    return true;
                }
            }

            function validateContactNumber() {
                const contactNo = document.getElementById('contactNo');
                const value = contactNo.value.trim();
                
                if (value.length < 10) {
                    contactNo.setCustomValidity('Contact number must be at least 10 digits');
                    return false;
                } else if (value.length > 13) {
                    contactNo.setCustomValidity('Contact number must not exceed 13 characters');
                    return false;
                } else if (!/^\+?[0-9]+$/.test(value)) {
                    contactNo.setCustomValidity('Contact number can only contain numbers and optional + prefix');
                    return false;
                } else {
                    contactNo.setCustomValidity('');
                    return true;
                }
            }

            function validateAddress() {
                const address = document.getElementById('address');
                const value = address.value.trim();
                
                if (value.length < 10) {
                    address.setCustomValidity('Address must be at least 10 characters long');
                    return false;
                } else if (value.length > 200) {
                    address.setCustomValidity('Address must not exceed 200 characters');
                    return false;
                } else {
                    address.setCustomValidity('');
                    return true;
                }
            }

            function validateYearsOfResidency() {
                const years = document.getElementById('yearsOfResidency');
                const value = parseFloat(years.value);
                
                if (isNaN(value)) {
                    years.setCustomValidity('Years of residency must be a valid number');
                    return false;
                } else if (value < 0) {
                    years.setCustomValidity('Years of residency cannot be negative');
                    return false;
                } else if (value > 150) {
                    years.setCustomValidity('Years of residency must be reasonable (max 150 years)');
                    return false;
                } else {
                    years.setCustomValidity('');
                    return true;
                }
            }

            function validateBirthdate() {
                const birthdateInput = document.getElementById('birthdate');
                const noBirthCertSelected = document.getElementById('no_birth_certificate').checked && !document.getElementById('no_birth_certificate').disabled;
                
                if (!noBirthCertSelected) {
                    birthdateInput.setCustomValidity('');
                    return true;
                }
                
                const value = birthdateInput.value;
                
                if (!value) {
                    birthdateInput.setCustomValidity('Birthdate is required when requesting No Birth Certificate');
                    return false;
                }
                
                const birthdate = new Date(value);
                const today = new Date();
                const minDate = new Date();
                minDate.setFullYear(today.getFullYear() - 150);
                
                if (birthdate > today) {
                    birthdateInput.setCustomValidity('Birthdate cannot be in the future');
                    return false;
                } else if (birthdate < minDate) {
                    birthdateInput.setCustomValidity('Birthdate must be within the last 150 years');
                    return false;
                } else {
                    birthdateInput.setCustomValidity('');
                    return true;
                }
            }

            function validatePurpose() {
                const purpose = document.getElementById('reqPurpose');
                const value = purpose.value.trim();
                
                if (value.length < 5) {
                    purpose.setCustomValidity('Purpose must be at least 5 characters long');
                    return false;
                } else if (value.length > 300) {
                    purpose.setCustomValidity('Purpose must not exceed 300 characters');
                    return false;
                } else {
                    purpose.setCustomValidity('');
                    return true;
                }
            }

            function validateImageUpload() {
                const imageInput = document.getElementById('certificateImage');
                const isUpdateMode = <?php echo $isUpdateMode ? 'true' : 'false'; ?>;
                
                if (!imageInput.required && !imageInput.files.length) {
                    imageInput.setCustomValidity('');
                    return true;
                }
                
                if (imageInput.required && !imageInput.files.length) {
                    imageInput.setCustomValidity('Image of valid ID is required');
                    return false;
                }
                
                if (imageInput.files.length > 0) {
                    const file = imageInput.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    
                    if (!allowedTypes.includes(file.type)) {
                        imageInput.setCustomValidity('Only JPG and PNG image files are allowed');
                        return false;
                    }
                    
                    if (file.size > maxSize) {
                        imageInput.setCustomValidity('Image size must be less than 2MB');
                        return false;
                    }
                    
                    imageInput.setCustomValidity('');
                }
                return true;
            }

            // Add real-time validation
            document.getElementById('firstname').addEventListener('input', function() { validateName('firstname'); validateForm(); });
            document.getElementById('firstname').addEventListener('blur', function() { validateName('firstname'); });
            
            document.getElementById('lastname').addEventListener('input', function() { validateName('lastname'); validateForm(); });
            document.getElementById('lastname').addEventListener('blur', function() { validateName('lastname'); });
            
            document.getElementById('contactNo').addEventListener('input', function() { validateContactNumber(); validateForm(); });
            document.getElementById('contactNo').addEventListener('blur', validateContactNumber);
            
            document.getElementById('address').addEventListener('input', function() { validateAddress(); validateForm(); });
            document.getElementById('address').addEventListener('blur', validateAddress);
            
            document.getElementById('yearsOfResidency').addEventListener('input', function() { validateYearsOfResidency(); validateForm(); });
            document.getElementById('yearsOfResidency').addEventListener('blur', validateYearsOfResidency);
            
            document.getElementById('birthdate').addEventListener('change', function() { validateBirthdate(); validateForm(); });
            
            document.getElementById('reqPurpose').addEventListener('input', function() { validatePurpose(); validateForm(); });
            document.getElementById('reqPurpose').addEventListener('blur', validatePurpose);
            
            document.getElementById('certificateImage').addEventListener('change', function() { validateImageUpload(); validateForm(); });

            function validateForm() {
                let isValid = true;

                // Run all validation functions
                isValid = validateName('firstname') && isValid;
                isValid = validateName('lastname') && isValid;
                isValid = validateContactNumber() && isValid;
                isValid = validateAddress() && isValid;
                isValid = validateYearsOfResidency() && isValid;
                isValid = validateBirthdate() && isValid;
                isValid = validatePurpose() && isValid;
                isValid = validateImageUpload() && isValid;

                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                    }
                });

                // Check if at least one document type is selected
                const documentTypes = form.querySelectorAll('input[name="doctype[]"]:checked');
                if (documentTypes.length === 0) {
                    isValid = false;
                }

                submitBtn.disabled = !isValid;
            }

            // Handle document type checkbox changes to show/hide image field
            function updateImageFieldVisibility() {
                const documentTypes = form.querySelectorAll('input[name="doctype[]"]:checked:not(:disabled)');
                const checkedTypes = Array.from(documentTypes).map(cb => cb.value);

                // Check if ONLY "No Birth Certificate" is selected
                const onlyNoBirthCert = checkedTypes.length === 1 && checkedTypes.includes('No Birth Certificate');

                if (onlyNoBirthCert) {
                    // Hide image field and make it optional
                    certificateImageGroup.style.display = 'none';
                    certificateImageInput.required = false;
                    imageRequired.style.display = 'none';
                } else {
                    // Show image field and make it required (except in update mode with existing image)
                    certificateImageGroup.style.display = 'block';
                    <?php if (!$isUpdateMode): ?>
                        certificateImageInput.required = true;
                        imageRequired.style.display = 'inline';
                    <?php else: ?>
                        certificateImageInput.required = false;
                        imageRequired.style.display = 'none';
                    <?php endif; ?>
                }

                validateForm();
            }

            // Handle No Birth Certificate checkbox toggle
            const noBirthCertCheckbox = document.getElementById('no_birth_certificate');
            const birthdateGroup = document.getElementById('birthdateGroup');
            const birthdateInput = document.getElementById('birthdate');

            function toggleBirthdateField() {
                if (noBirthCertCheckbox && !noBirthCertCheckbox.disabled && noBirthCertCheckbox.checked) {
                    birthdateGroup.style.display = 'block';
                    birthdateInput.required = true;
                } else {
                    birthdateGroup.style.display = 'none';
                    birthdateInput.required = false;
                }
                updateImageFieldVisibility(); // Also check image field visibility
            }

            if (noBirthCertCheckbox) {
                noBirthCertCheckbox.addEventListener('change', toggleBirthdateField);
                // Initialize on page load
                toggleBirthdateField();
            }

            // Add change listener to all document type checkboxes
            const allDocTypeCheckboxes = document.querySelectorAll('input[name="doctype[]"]');
            allDocTypeCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateImageFieldVisibility);

                if (checkbox.disabled) {
                    checkbox.checked = false;

                    // Add click event to show message if user tries to click disabled checkbox
                    checkbox.addEventListener('click', function (e) {
                        if (this.disabled) {
                            e.preventDefault();
                            let message = 'This document type is not available.';

                            if (this.value === 'First Time Job Seeker') {
                                message = 'You can only request First Time Job Seeker certificate once, and you already have a previous request.';
                            } else {
                                message = 'You already have a pending request for ' + this.value + '. Please wait for approval or update your existing request.';
                            }

                            alert(message);
                            return false;
                        }
                    });
                }
            });

            // Initialize image field visibility on page load
            updateImageFieldVisibility();

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Form submission validation
            form.addEventListener('submit', function (e) {
                // Run all validations before submit
                if (!validateForm()) {
                    e.preventDefault();
                    alert('Please correct the errors in the form before submitting.');
                    return false;
                }
                
                // Additional check for disabled checkboxes that might be checked
                const disabledChecked = form.querySelectorAll('input[name="doctype[]"]:disabled:checked');
                if (disabledChecked.length > 0) {
                    e.preventDefault();
                    alert('Please unselect disabled document types before submitting.');
                    return false;
                }
            });

            // Initialize
            validateForm();

            // Show success message if submission was successful
            <?php if (isset($success) && $success): ?>
                document.getElementById('refNo').textContent = '<?php echo $success_ref_no; ?>';
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your document request has been updated.';
                <?php endif; ?>
                successMessage.classList.add('show');
                overlay.classList.add('show');
            <?php endif; ?>

            // Close success message
            if (closeSuccessMessage) {
                closeSuccessMessage.addEventListener('click', function () {
                    successMessage.classList.remove('show');
                    overlay.classList.remove('show');
                    window.location.href = '../Pages/landingpage.php';
                });
            }
        });
    </script>
</body>

</html>