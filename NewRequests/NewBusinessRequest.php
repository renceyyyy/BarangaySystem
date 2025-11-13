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
    $check_sql = "SELECT * FROM businesstbl WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending' LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("si", $updateRefNo, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
        } else {
            $_SESSION['error_message'] = "Invalid update request or request is no longer pending.";
            header("Location: ../Pages/UserReports.php");
            exit();
        }
        $check_stmt->close();
    }
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$user_data = [];

// If update mode, use pending request data
if ($isUpdateMode && $pendingRequest) {
    $ownerName = $pendingRequest['OwnerName'] ?? '';
    $firstname = explode(' ', $ownerName)[0] ?? '';
    $lastname = explode(' ', $ownerName, 2)[1] ?? '';
    $contactNo = $pendingRequest['OwnerContact'] ?? '';
    $address = $pendingRequest['BusinessLoc'] ?? '';
    $businessName = $pendingRequest['BusinessName'] ?? '';
    $purpose = $pendingRequest['Purpose'] ?? '';
    $requestType = $pendingRequest['RequestType'] ?? '';
    $closureDate = $pendingRequest['ClosureDate'] ?? '';
} else {
    // Fetch from user profile
    $user_sql = "SELECT Firstname, Lastname, ContactNo, Address FROM userloginfo WHERE UserID = ?";
    $user_stmt = $conn->prepare($user_sql);
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();

            $firstname = $user_data['Firstname'] ?? '';
            $lastname = $user_data['Lastname'] ?? '';
            $contactNo = $user_data['ContactNo'] ?? '';
            $address = $user_data['Address'] ?? '';

            if ($firstname === 'uncompleted')
                $firstname = '';
            if ($lastname === 'uncompleted')
                $lastname = '';
            if ($contactNo === '0')
                $contactNo = '';
            if ($address === 'uncompleted')
                $address = '';

            $ownerName = trim($firstname . ' ' . $lastname);
        }
        $user_stmt->close();
    }
    // Initialize form variables for new request
    $businessName = '';
    $purpose = '';
    $requestType = '';
    $closureDate = '';
}

// Handle business request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["business_request"])) {
    $errors = [];

    // Get request type early for validation
    $requestTypeToSubmit = trim($_POST["RequestType"] ?? '');

    // NEW FEATURE: Check for pending request of SPECIFIC request type before allowing submission
    if (!$isUpdateMode && !empty($requestTypeToSubmit)) {
        $pending_check = "SELECT refno FROM businesstbl WHERE UserId = ? AND RequestType = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pending_check_stmt = $conn->prepare($pending_check);
        if ($pending_check_stmt) {
            $pending_check_stmt->bind_param("is", $user_id, $requestTypeToSubmit);
            $pending_check_stmt->execute();
            $pending_check_result = $pending_check_stmt->get_result();
            if ($pending_check_result->num_rows > 0) {
                $pending_data = $pending_check_result->fetch_assoc();
                $requestTypeLabel = ($requestTypeToSubmit === 'permit') ? 'Business Permit' : 'Business Closure';
                $errors[] = "You have a pending " . $requestTypeLabel . " request (Ref: " . htmlspecialchars($pending_data['refno']) . "). Please wait for approval or update your existing request before submitting a new one for this request type.";
            }
            $pending_check_stmt->close();
        }
    }

    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }

    // Enable detailed error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        // Generate reference number
        $refno = date('Ymd') . rand(1000, 9999);

        // Validate and sanitize inputs
        $requiredFields = [
            'RequestType' => 'Request type',
            'BusinessName' => 'Business name',
            'BusinessLoc' => 'Business location',
            'Purpose' => 'Purpose',
            'OwnerName' => 'Owner name',
            'OwnerContact' => 'Owner contact'
        ];

        $data = [];

        foreach ($requiredFields as $field => $name) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[] = "$name is required";
            } else {
                $data[$field] = trim($_POST[$field]);
            }
        }

        // Get UserId from session
        $data['UserId'] = $_SESSION['user_id'];

        // Validate closure date if needed
        if (isset($data['RequestType']) && $data['RequestType'] === 'closure') {
            if (empty($_POST['ClosureDate'])) {
                $errors[] = "Closure date is required for business closure";
            } elseif (!strtotime($_POST['ClosureDate'])) {
                $errors[] = "Invalid closure date format";
            } else {
                $data['ClosureDate'] = date('Y-m-d H:i:s', strtotime($_POST['ClosureDate']));
            }
        }

        // Validate file upload
        $proofContent = null;
        $hasNewFile = false;

        if (!empty($_FILES['businessProof']['tmp_name'])) {
            $hasNewFile = true;
            $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($fileInfo, $_FILES['businessProof']['tmp_name']);
            finfo_close($fileInfo);

            $fileSize = $_FILES['businessProof']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Only JPG, PNG, and PDF files are allowed";
            } elseif ($fileSize > $maxSize) {
                $errors[] = "File size must be less than 2MB";
            } else {
                $proofContent = file_get_contents($_FILES['businessProof']['tmp_name']);
                if ($proofContent === false) {
                    $errors[] = "Failed to read the uploaded file";
                }
            }
        } elseif (!$isUpdateMode) {
            $errors[] = "Proof document is required";
        }

        // If no errors, process the request
        if (empty($errors)) {
            // Sanitize data
            foreach ($data as $key => $value) {
                if ($key !== 'UserId') {
                    $data[$key] = mysqli_real_escape_string($conn, $value);
                }
            }

            if ($isUpdateMode) {
                // UPDATE existing pending request
                if ($data['RequestType'] === 'permit') {
                    if ($hasNewFile && $proofContent) {
                        $sql = "UPDATE businesstbl SET 
                            BusinessName = ?, BusinessLoc = ?, OwnerName = ?, Purpose = ?, 
                            OwnerContact = ?, RequestType = ?, ProofPath = ?
                            WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $null = NULL;
                        $stmt->bind_param(
                            "sssssssbsi",
                            $data['BusinessName'],
                            $data['BusinessLoc'],
                            $data['OwnerName'],
                            $data['Purpose'],
                            $data['OwnerContact'],
                            $data['RequestType'],
                            $null,
                            $updateRefNo,
                            $data['UserId']
                        );
                        $stmt->send_long_data(6, $proofContent);
                    } else {
                        $sql = "UPDATE businesstbl SET 
                            BusinessName = ?, BusinessLoc = ?, OwnerName = ?, Purpose = ?, 
                            OwnerContact = ?, RequestType = ?
                            WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $stmt->bind_param(
                            "sssssssi",
                            $data['BusinessName'],
                            $data['BusinessLoc'],
                            $data['OwnerName'],
                            $data['Purpose'],
                            $data['OwnerContact'],
                            $data['RequestType'],
                            $updateRefNo,
                            $data['UserId']
                        );
                    }
                } else {
                    if ($hasNewFile && $proofContent) {
                        $sql = "UPDATE businesstbl SET 
                            BusinessName = ?, BusinessLoc = ?, OwnerName = ?, Purpose = ?, 
                            ClosureDate = ?, OwnerContact = ?, RequestType = ?, ProofPath = ?
                            WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $null = NULL;
                        $stmt->bind_param(
                            "ssssssssbsi",
                            $data['BusinessName'],
                            $data['BusinessLoc'],
                            $data['OwnerName'],
                            $data['Purpose'],
                            $data['ClosureDate'],
                            $data['OwnerContact'],
                            $data['RequestType'],
                            $null,
                            $updateRefNo,
                            $data['UserId']
                        );
                        $stmt->send_long_data(7, $proofContent);
                    } else {
                        $sql = "UPDATE businesstbl SET 
                            BusinessName = ?, BusinessLoc = ?, OwnerName = ?, Purpose = ?, 
                            ClosureDate = ?, OwnerContact = ?, RequestType = ?
                            WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";

                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }

                        $stmt->bind_param(
                            "sssssssssi",
                            $data['BusinessName'],
                            $data['BusinessLoc'],
                            $data['OwnerName'],
                            $data['Purpose'],
                            $data['ClosureDate'],
                            $data['OwnerContact'],
                            $data['RequestType'],
                            $updateRefNo,
                            $data['UserId']
                        );
                    }
                }

                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $updateRefNo;
                    $isUpdateSuccess = true; // Flag to show update success message

                    // Log user activity for update
                    logUserActivity(
                        'Business request updated',
                        'business_request_update',
                        [
                            'business_name' => $data['BusinessName'],
                            'request_type' => $data['RequestType'],
                            'reference_no' => $updateRefNo,
                            'action' => 'update'
                        ]
                    );
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } else {
                // INSERT new request
                $refno = date('Ymd') . rand(1000, 9999);

                if ($data['RequestType'] === 'permit') {
                    $sql = "INSERT INTO businesstbl (
                        UserId, refno, BusinessName, BusinessLoc, OwnerName, Purpose, OwnerContact, RequestType, ProofPath
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $null = NULL;
                    $stmt->bind_param(
                        "isssssssb",
                        $data['UserId'],
                        $refno,
                        $data['BusinessName'],
                        $data['BusinessLoc'],
                        $data['OwnerName'],
                        $data['Purpose'],
                        $data['OwnerContact'],
                        $data['RequestType'],
                        $null
                    );
                    $stmt->send_long_data(8, $proofContent);
                } else {
                    $sql = "INSERT INTO businesstbl (
                        UserId, refno, BusinessName, BusinessLoc, OwnerName, Purpose, ClosureDate, OwnerContact, RequestType, ProofPath
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $null = NULL;
                    $stmt->bind_param(
                        "issssssssb",
                        $data['UserId'],
                        $refno,
                        $data['BusinessName'],
                        $data['BusinessLoc'],
                        $data['OwnerName'],
                        $data['Purpose'],
                        $data['ClosureDate'],
                        $data['OwnerContact'],
                        $data['RequestType'],
                        $null
                    );
                    $stmt->send_long_data(9, $proofContent);
                }

                // Execute query
                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $refno;

                    // Log user activity
                    logUserActivity(
                        'Business request submitted',
                        'business_request',
                        [
                            'business_name' => $data['BusinessName'],
                            'request_type' => $data['RequestType'],
                            'reference_no' => $refno
                        ]
                    );

                    // Reset form but keep user data
                    $businessName = $businessLoc = $purpose = $closureDate = '';
                    $requestType = 'permit';
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $stmt->close();
            }
        }

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Business Permit / Closure</title>
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

        .request-type-options {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }

        .request-type-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .request-type-option.selected {
            border-color: #4CAF50;
            background-color: #f0fff0;
        }

        .request-type-option input[type="radio"] {
            display: none;
        }

        .closure-date-field {
            display: none;
        }

        .closure-date-field.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Business Request Submitted Successfully!</h3>
        <p>Your business <?php echo isset($requestType) && $requestType === 'permit' ? 'permit' : 'closure'; ?> request
            has been received.</p>
        <p>Reference Number: <strong id="refNo"></strong></p>
        <p>Please keep this reference number for tracking your request.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>

    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Business Request' : 'Business Permit / Closure Request'; ?></h1>

        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff;">
                <strong>Update Mode:</strong> You are updating your pending request (Reference:
                <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your
                request.
            </div>
        <?php else: ?>
            <div class="user-info-note">
                <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and
                update if necessary. payment for your request is due within 7 days.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" id="businessForm">
            <input type="hidden" name="business_request" value="1">

            <div class="form-section">
                <h2>Request Type</h2>

                <div class="form-group">
                    <label>Select Request Type <span class="required">*</span></label>
                    <div class="request-type-options">
                        <div class="request-type-option <?php echo (isset($requestType) && $requestType === 'permit') ? 'selected' : 'selected'; ?>"
                            id="permitOption">
                            <input type="radio" id="request_type_permit" name="RequestType" value="permit" <?php echo (isset($requestType) && $requestType === 'permit') ? 'checked' : 'checked'; ?>>
                            <label for="request_type_permit">
                                <strong>Business Permit</strong><br>
                                <small>Apply for a new business permit</small>
                            </label>
                        </div>
                        <div class="request-type-option <?php echo (isset($requestType) && $requestType === 'closure') ? 'selected' : ''; ?>"
                            id="closureOption">
                            <input type="radio" id="request_type_closure" name="RequestType" value="closure" <?php echo (isset($requestType) && $requestType === 'closure') ? 'checked' : ''; ?>>
                            <label for="request_type_closure">
                                <strong>Business Closure</strong><br>
                                <small>Request to close an existing business</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="closureDateField"
                    class="form-group closure-date-field <?php echo (isset($requestType) && $requestType === 'closure') ? 'active' : ''; ?>">
                    <label for="ClosureDate">Closure Date <span class="required">*</span></label>
                    <input type="date" id="ClosureDate" name="ClosureDate"
                        value="<?php echo htmlspecialchars($closureDate ?? ''); ?>">
                    <div class="file-info">Select the date when the business will be closed</div>
                </div>
            </div>

            <div class="form-section">
                <h2>Business Information</h2>

                <div class="form-group">
                    <label for="BusinessName">Business Name <span class="required">*</span></label>
                    <input type="text" id="BusinessName" name="BusinessName"
                        value="<?php echo htmlspecialchars($businessName ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="BusinessLoc">Business Location <span class="required">*</span></label>
                    <textarea id="BusinessLoc" name="BusinessLoc"
                        required><?php echo htmlspecialchars($businessLoc ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="OwnerName">Owner Name <span class="required">*</span></label>
                    <input type="text" id="OwnerName" name="OwnerName"
                        value="<?php echo htmlspecialchars($ownerName ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="OwnerContact">Owner Contact Number <span class="required">*</span></label>
                    <input type="tel" id="OwnerContact" name="OwnerContact"
                        value="<?php echo htmlspecialchars($contactNo ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="Purpose">Purpose of Request <span class="required">*</span></label>
                    <textarea id="Purpose" name="Purpose" placeholder="Please specify the purpose for this request..."
                        required><?php echo htmlspecialchars($purpose ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Required Documents</h2>

                <div class="form-group" id="businessProofGroup">
                    <label for="businessProof" class="form-label">
                        <strong>DTI Business Permit</strong>
                        <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?>
                        <br>
                        <small class="text-muted">
                            Please upload a valid DTI Permit as proof of business registration.
                        </small>
                    </label>

                    <input type="file" id="businessProof" name="businessProof" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.pdf">

                    <div class="file-info text-muted">
                        <?php if ($isUpdateMode): ?>
                            <small>Upload a new DTI Permit (optional) — JPG, PNG, or PDF (Max: 2MB). Leave empty to keep the
                                current document.</small>
                        <?php else: ?>
                            <small>Upload your DTI Permit — JPG, PNG, or PDF (Max: 2MB)</small>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('businessForm'); ?>

            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                <a href="../Pages/landingpage.php" class="btn btn-secondary"
                    style="background-color: #6c757d; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn"
                    id="submitBtn"><?php echo $isUpdateMode ? 'Update Request' : 'Submit Request'; ?></button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const permitOption = document.getElementById('permitOption');
            const closureOption = document.getElementById('closureOption');
            const requestTypePermit = document.getElementById('request_type_permit');
            const requestTypeClosure = document.getElementById('request_type_closure');
            const closureDateField = document.getElementById('closureDateField');
            const closureDateInput = document.getElementById('ClosureDate');
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('businessForm');

            // Validation functions
            function validateBusinessName() {
                const businessName = document.getElementById('BusinessName');
                const value = businessName.value.trim();
                
                if (value.length < 3) {
                    businessName.setCustomValidity('Business name must be at least 3 characters long');
                    return false;
                } else if (value.length > 100) {
                    businessName.setCustomValidity('Business name must not exceed 100 characters');
                    return false;
                } else {
                    businessName.setCustomValidity('');
                    return true;
                }
            }

            function validateOwnerName() {
                const ownerName = document.getElementById('OwnerName');
                const value = ownerName.value.trim();
                const namePattern = /^[a-zA-Z\s\-.]+$/;
                
                if (value.length < 2) {
                    ownerName.setCustomValidity('Owner name must be at least 2 characters long');
                    return false;
                } else if (!namePattern.test(value)) {
                    ownerName.setCustomValidity('Owner name can only contain letters, spaces, hyphens, and periods');
                    return false;
                } else {
                    ownerName.setCustomValidity('');
                    return true;
                }
            }

            function validateContactNumber() {
                const contactNo = document.getElementById('OwnerContact');
                const value = contactNo.value.trim();
                // Philippine mobile number format: 09XX-XXX-XXXX or 639XXXXXXXXX or 11 digits
                const phonePattern = /^(09|\+639|639)\d{9}$/;
                
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

            function validateBusinessLocation() {
                const businessLoc = document.getElementById('BusinessLoc');
                const value = businessLoc.value.trim();
                
                if (value.length < 10) {
                    businessLoc.setCustomValidity('Business location must be at least 10 characters long');
                    return false;
                } else if (value.length > 500) {
                    businessLoc.setCustomValidity('Business location must not exceed 500 characters');
                    return false;
                } else {
                    businessLoc.setCustomValidity('');
                    return true;
                }
            }

            function validatePurpose() {
                const purpose = document.getElementById('Purpose');
                const value = purpose.value.trim();
                
                if (value.length < 10) {
                    purpose.setCustomValidity('Purpose must be at least 10 characters long');
                    return false;
                } else if (value.length > 500) {
                    purpose.setCustomValidity('Purpose must not exceed 500 characters');
                    return false;
                } else {
                    purpose.setCustomValidity('');
                    return true;
                }
            }

            function validateClosureDate() {
                if (requestTypeClosure.checked) {
                    const closureDate = document.getElementById('ClosureDate');
                    const value = closureDate.value;
                    
                    if (!value) {
                        closureDate.setCustomValidity('Closure date is required for business closure');
                        return false;
                    }
                    
                    const selectedDate = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate < today) {
                        closureDate.setCustomValidity('Closure date cannot be in the past');
                        return false;
                    } else {
                        closureDate.setCustomValidity('');
                        return true;
                    }
                }
                return true;
            }

            function validateFileUpload() {
                const fileInput = document.getElementById('businessProof');
                const isUpdateMode = <?php echo $isUpdateMode ? 'true' : 'false'; ?>;
                
                if (!isUpdateMode && !fileInput.files.length) {
                    fileInput.setCustomValidity('Proof document is required');
                    return false;
                }
                
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    
                    if (!allowedTypes.includes(file.type)) {
                        fileInput.setCustomValidity('Only JPG, PNG, and PDF files are allowed');
                        return false;
                    }
                    
                    if (file.size > maxSize) {
                        fileInput.setCustomValidity('File size must be less than 2MB');
                        return false;
                    }
                    
                    fileInput.setCustomValidity('');
                }
                return true;
            }

            // Add real-time validation
            document.getElementById('BusinessName').addEventListener('input', validateBusinessName);
            document.getElementById('BusinessName').addEventListener('blur', validateBusinessName);
            
            document.getElementById('OwnerName').addEventListener('input', validateOwnerName);
            document.getElementById('OwnerName').addEventListener('blur', validateOwnerName);
            
            document.getElementById('OwnerContact').addEventListener('input', validateContactNumber);
            document.getElementById('OwnerContact').addEventListener('blur', validateContactNumber);
            
            document.getElementById('BusinessLoc').addEventListener('input', validateBusinessLocation);
            document.getElementById('BusinessLoc').addEventListener('blur', validateBusinessLocation);
            
            document.getElementById('Purpose').addEventListener('input', validatePurpose);
            document.getElementById('Purpose').addEventListener('blur', validatePurpose);
            
            document.getElementById('ClosureDate').addEventListener('change', validateClosureDate);
            document.getElementById('businessProof').addEventListener('change', validateFileUpload);

            function toggleRequestType() {
                if (requestTypePermit.checked) {
                    permitOption.classList.add('selected');
                    closureOption.classList.remove('selected');
                    closureDateField.classList.remove('active');
                    closureDateInput.required = false;
                } else {
                    permitOption.classList.remove('selected');
                    closureOption.classList.add('selected');
                    closureDateField.classList.add('active');
                    closureDateInput.required = true;
                }
                validateClosureDate();
                validateForm();
            }

            function validateForm() {
                let isValid = true;

                // Run all validation functions
                isValid = validateBusinessName() && isValid;
                isValid = validateOwnerName() && isValid;
                isValid = validateContactNumber() && isValid;
                isValid = validateBusinessLocation() && isValid;
                isValid = validatePurpose() && isValid;
                isValid = validateClosureDate() && isValid;
                isValid = validateFileUpload() && isValid;

                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim() && field.offsetParent !== null) {
                        isValid = false;
                    }
                });

                submitBtn.disabled = !isValid;
            }

            // Event listeners for request type change
            permitOption.addEventListener('click', function () {
                requestTypePermit.checked = true;
                toggleRequestType();
            });

            closureOption.addEventListener('click', function () {
                requestTypeClosure.checked = true;
                toggleRequestType();
            });

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Form submission validation
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    alert('Please correct the errors in the form before submitting.');
                    return false;
                }
            });

            // Initialize
            toggleRequestType();
            validateForm();

            // Show success message if submission was successful
            <?php if (isset($success) && $success): ?>
                document.getElementById('refNo').textContent = '<?php echo $success_ref_no; ?>';
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your business request has been updated.';
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