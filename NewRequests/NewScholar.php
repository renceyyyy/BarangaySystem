<?php 
require_once '../Process/db_connection.php';
require_once './Terms&Conditions/Terms&Conditons.php';
session_start();
$conn = getDBConnection();

$firstname = $lastname = $email = $contact_no = $address = $reason = "";
$reason_type = "text";
$errors = [];
$success = false;
$success_ref_id = "";
$isUpdateSuccess = false; // Track if update was successful

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

// Check if we're in update mode
$isUpdateMode = false;
$updateRefNo = null;
$pendingRequest = null;

if (isset($_GET['update'])) {
    $updateRefNo = $_GET['update'];
    $isUpdateMode = true;
    
    // Fetch pending request data
    $pendingCheckSql = "SELECT * FROM scholarship WHERE ID = ? AND UserID = ? AND RequestStatus = 'Pending'";
    $pendingStmt = $conn->prepare($pendingCheckSql);
    if ($pendingStmt) {
        $pendingStmt->bind_param("si", $updateRefNo, $_SESSION['user_id']);
        $pendingStmt->execute();
        $result = $pendingStmt->get_result();
        
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
        } else {
            // Invalid update request
            header("Location: ../Pages/UserReports.php");
            exit();
        }
        $pendingStmt->close();
    }
}

$user_id = $_SESSION['user_id'];
$user_data = [];

$user_sql = "SELECT Firstname, Lastname, Email, ContactNo, Address FROM userloginfo WHERE UserID = ?";
$user_stmt = $conn->prepare($user_sql);
if ($user_stmt) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        
        // Use pending request data if in update mode, otherwise use profile data
        if ($isUpdateMode && $pendingRequest) {
            $firstname = $pendingRequest['Firstname'] ?? '';
            $lastname = $pendingRequest['Lastname'] ?? '';
            $email = $pendingRequest['Email'] ?? '';
            $contact_no = $pendingRequest['ContactNo'] ?? '';
            $address = $pendingRequest['Address'] ?? '';
            $reason = $pendingRequest['Reason'] ?? '';
            // Determine if reason is a file path or text
            $reason_type = (strpos($reason, '../uploads/scholarship/') === 0) ? 'file' : 'text';
        } else {
            $firstname = $user_data['Firstname'] ?? '';
            $lastname = $user_data['Lastname'] ?? '';
            $email = $user_data['Email'] ?? '';
            $contact_no = $user_data['ContactNo'] ?? '';
            $address = $user_data['Address'] ?? '';
            
            if ($firstname === 'uncompleted') $firstname = '';
            if ($lastname === 'uncompleted') $lastname = '';
            if ($email === 'uncompleted') $email = '';
            if ($contact_no === '0') $contact_no = '';
            if ($address === 'uncompleted') $address = '';
        }
    }
    $user_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["scholar_request"])) {
    // Check for existing pending scholarship request (only for new submissions)
    if (!$isUpdateMode) {
        $pendingCheckSql = "SELECT ID FROM scholarship WHERE UserID = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pendingStmt = $conn->prepare($pendingCheckSql);
        if ($pendingStmt) {
            $pendingStmt->bind_param("i", $user_id);
            $pendingStmt->execute();
            $pendingResult = $pendingStmt->get_result();
            
            if ($pendingResult->num_rows > 0) {
                $pendingData = $pendingResult->fetch_assoc();
                $errors[] = "You have a pending Scholar Grant Application (Ref: " . htmlspecialchars($pendingData['ID']) . "). Please wait for approval or update your existing request before submitting a new one.";
            }
            $pendingStmt->close();
        }
    }
    
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $reason_type = $_POST['reason_type'] ?? 'text';
    $reason = "";
    
    if (empty($firstname)) $errors[] = "First name is required";
    if (empty($lastname)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($contact_no)) $errors[] = "Contact number is required";
    if (empty($address)) $errors[] = "Address is required";
    
    if ($reason_type === 'text') {
        $reason = trim($_POST['reason'] ?? '');
        if (empty($reason)) $errors[] = "Reason for applying is required when typing";
    } else {
        if (!isset($_FILES['reason_file']) || $_FILES['reason_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Reason document is required when file option is selected";
        } elseif ($_FILES['reason_file']['size'] > 5242880) {
            $errors[] = "Reason document must be smaller than 5MB";
        } else {
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $tmp_name = $_FILES['reason_file']['tmp_name'];
            $mime = mime_content_type($tmp_name);
            if (!in_array($mime, $allowed_mime_types)) {
                $errors[] = "Reason document must be a JPG, PNG, GIF, or PDF file";
            }
        }
    }
    
    $required_files = [
        'school_id' => 'School ID',
        'barangay_id' => 'Barangay ID',
        'cor' => 'Certificate of Registration',
        'parents_id' => 'Parents ID',
        'birth_certificate' => 'Birth Certificate'
    ];
    
    foreach ($required_files as $field => $label) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            // Only require files for new submissions
            if (!$isUpdateMode) {
                $errors[] = "$label is required";
            }
        } elseif (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            if ($_FILES[$field]['size'] > 5242880) {
                $errors[] = "$label must be smaller than 5MB";
            } else {
                $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                $tmp_name = $_FILES[$field]['tmp_name'];
                $mime = mime_content_type($tmp_name);
                if (!in_array($mime, $allowed_mime_types)) {
                    $errors[] = "$label must be a JPG, PNG, GIF, or PDF file";
                }
            }
        }
    }
    
    if (empty($errors)) {
        $upload_dir = '../uploads/scholarship/' . $user_id . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_paths = [];
        
        // Use existing file paths from pending request if in update mode
        if ($isUpdateMode && $pendingRequest) {
            $file_paths['school_id'] = $pendingRequest['SchoolID'];
            $file_paths['barangay_id'] = $pendingRequest['BaranggayID'];
            $file_paths['cor'] = $pendingRequest['COR'];
            $file_paths['parents_id'] = $pendingRequest['ParentsID'];
            $file_paths['birth_certificate'] = $pendingRequest['BirthCertificate'];
        }
        
        $all_files_valid = true;
        
        // Upload new files if provided
        foreach ($required_files as $field => $label) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file_name = $field . '_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $file_path)) {
                    $file_paths[$field] = $file_path;
                } else {
                    $errors[] = "Failed to upload $label";
                    $all_files_valid = false;
                }
            }
        }
        
        if ($reason_type === 'file') {
            if (isset($_FILES['reason_file']) && $_FILES['reason_file']['error'] === UPLOAD_ERR_OK) {
                $reason_file_name = 'reason_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['reason_file']['name'], PATHINFO_EXTENSION);
                $reason_file_path = $upload_dir . $reason_file_name;
                
                if (move_uploaded_file($_FILES['reason_file']['tmp_name'], $reason_file_path)) {
                    $reason = $reason_file_path;
                } else {
                    $errors[] = "Failed to upload reason document";
                    $all_files_valid = false;
                }
            } elseif ($isUpdateMode && $pendingRequest && strpos($pendingRequest['Reason'], '../uploads/scholarship/') === 0) {
                // Keep existing reason file
                $reason = $pendingRequest['Reason'];
            }
        } else {
            $reason = mysqli_real_escape_string($conn, $reason);
        }
        
        if ($all_files_valid && empty($errors)) {
            $firstname = mysqli_real_escape_string($conn, $firstname);
            $lastname = mysqli_real_escape_string($conn, $lastname);
            $email = mysqli_real_escape_string($conn, $email);
            $contact_no = mysqli_real_escape_string($conn, $contact_no);
            $address = mysqli_real_escape_string($conn, $address);
            
            $conn->query("SET FOREIGN_KEY_CHECKS=0");
            
            if ($isUpdateMode) {
                // UPDATE existing pending request
                $sql = "UPDATE scholarship SET 
                        Firstname = ?, Lastname = ?, Email = ?, ContactNo = ?, 
                        Address = ?, Reason = ?, SchoolID = ?, BaranggayID = ?, 
                        COR = ?, ParentsID = ?, BirthCertificate = ?
                        WHERE ID = ? AND UserID = ? AND RequestStatus = 'Pending'";
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param(
                        "ssssssssssssi",
                        $firstname,
                        $lastname,
                        $email,
                        $contact_no,
                        $address,
                        $reason,
                        $file_paths['school_id'],
                        $file_paths['barangay_id'],
                        $file_paths['cor'],
                        $file_paths['parents_id'],
                        $file_paths['birth_certificate'],
                        $updateRefNo,
                        $_SESSION['user_id']
                    );
                    
                    if ($stmt->execute()) {
                        $success = true;
                        $success_ref_id = $updateRefNo;
                        $isUpdateSuccess = true; // Flag to show update success message
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Database error: " . $conn->error;
                }
            } else {
                // INSERT new request
                $sql = "INSERT INTO scholarship 
                        (UserID, Firstname, Lastname, Email, ContactNo, Address, Reason,
                         SchoolID, BaranggayID, COR, ParentsID, BirthCertificate, 
                         RequestStatus, DateApplied)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param(
                        "isssssssssss",
                        $_SESSION['user_id'],
                        $firstname,
                        $lastname,
                        $email,
                        $contact_no,
                        $address,
                        $reason,
                        $file_paths['school_id'],
                        $file_paths['barangay_id'],
                        $file_paths['cor'],
                        $file_paths['parents_id'],
                        $file_paths['birth_certificate']
                    );
                    
                    if ($stmt->execute()) {
                        $success = true;
                        $success_ref_id = $stmt->insert_id;
                        $reason = "";
                        $reason_type = "text";
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Database error: " . $conn->error;
                }
            }
            
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Application</title>
    <link rel="stylesheet" href="./Style/Applications&RequestStyle.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        input[type="email"],
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
        .reason-options {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        .reason-option {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        .reason-option.selected {
            border-color: #4CAF50;
            background-color: #f0fff0;
        }
        .reason-option input[type="radio"] {
            display: none;
        }
        .reason-textarea, .reason-file {
            display: none;
        }
        .reason-textarea.active, .reason-file.active {
            display: block;
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
        .file-info {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        .file-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .user-info-note {
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Application Submitted Successfully!</h3>
        <p>Your scholarship application has been received.</p>
        <p>Reference ID: <strong id="refId"></strong></p>
        <p>Please wait for confirmation via email.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>
    
    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Scholarship Application' : 'Scholarship Application Form'; ?></h1>
        
        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff;">
                <strong>Update Mode:</strong> You are updating your pending application (Reference: <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your application.
            </div>
        <?php else: ?>
            <div class="user-info-note">
                <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and update if necessary.
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
        
        <form method="POST" action="" enctype="multipart/form-data" id="applicationForm">
            <input type="hidden" name="scholar_request" value="1">
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="firstname">First Name <span class="required">*</span></label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_no">Contact Number <span class="required">*</span></label>
                    <input type="text" id="contact_no" name="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Scholarship Details</h2>
                
                <div class="form-group">
                    <label>Reason for Applying <span class="required">*</span></label>
                    <div class="reason-options">
                        <div class="reason-option <?php echo $reason_type === 'text' ? 'selected' : ''; ?>" id="textOption">
                            <input type="radio" id="reason_type_text" name="reason_type" value="text" <?php echo $reason_type === 'text' ? 'checked' : ''; ?>>
                            <label for="reason_type_text">Type Reason</label>
                        </div>
                        <div class="reason-option <?php echo $reason_type === 'file' ? 'selected' : ''; ?>" id="fileOption">
                            <input type="radio" id="reason_type_file" name="reason_type" value="file" <?php echo $reason_type === 'file' ? 'checked' : ''; ?>>
                            <label for="reason_type_file">Upload Handwritten Document</label>
                        </div>
                    </div>
                    
                    <div id="reason_text_area" class="reason-textarea <?php echo $reason_type === 'text' ? 'active' : ''; ?>">
                        <textarea id="reason" name="reason" placeholder="Explain why you are applying for this scholarship..."><?php echo htmlspecialchars($reason); ?></textarea>
                        <div class="file-info">Type your reason for applying</div>
                    </div>
                    
                    <div id="reason_file_area" class="reason-file <?php echo $reason_type === 'file' ? 'active' : ''; ?>">
                        <input type="file" id="reason_file" name="reason_file" class="file-input" accept=".jpg,.jpeg,.png,.gif,.pdf">
                        <div class="file-info">Upload a scanned copy or photo of your handwritten reason (JPG, PNG, GIF, PDF - Max: 5MB)</div>
                        <img id="reason_file_preview" class="file-preview" alt="File preview">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Required Documents</h2>
                <p>Please upload the following documents (Max file size: 5MB each)<?php echo $isUpdateMode ? '. Leave empty to keep current documents.' : ''; ?>:</p>
                
                <div class="form-group">
                    <label for="school_id">School ID <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?></label>
                    <input type="file" id="school_id" name="school_id" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">JPG, PNG, GIF, PDF<?php echo $isUpdateMode ? ' (optional - current file will be kept if not uploaded)' : ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="barangay_id">Barangay ID <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?></label>
                    <input type="file" id="barangay_id" name="barangay_id" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">JPG, PNG, GIF, PDF<?php echo $isUpdateMode ? ' (optional - current file will be kept if not uploaded)' : ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="cor">Certificate of Registration <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?></label>
                    <input type="file" id="cor" name="cor" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">JPG, PNG, GIF, PDF<?php echo $isUpdateMode ? ' (optional - current file will be kept if not uploaded)' : ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="parents_id">Parents ID <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?></label>
                    <input type="file" id="parents_id" name="parents_id" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">JPG, PNG, GIF, PDF<?php echo $isUpdateMode ? ' (optional - current file will be kept if not uploaded)' : ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="birth_certificate">Birth Certificate <?php echo $isUpdateMode ? '' : '<span class="required">*</span>'; ?></label>
                    <input type="file" id="birth_certificate" name="birth_certificate" class="file-input" <?php echo $isUpdateMode ? '' : 'required'; ?> accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">JPG, PNG, GIF, PDF<?php echo $isUpdateMode ? ' (optional - current file will be kept if not uploaded)' : ''; ?></div>
                </div>
            </div>
            
            <?php echo displayTermsAndConditions('scholarForm'); ?>
            
            <div class="form-group">
                <button type="submit" class="btn" id="submitBtn"><?php echo $isUpdateMode ? 'Update Application' : 'Submit Application'; ?></button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reasonTypeText = document.getElementById('reason_type_text');
            const reasonTypeFile = document.getElementById('reason_type_file');
            const reasonTextArea = document.getElementById('reason_text_area');
            const reasonFileArea = document.getElementById('reason_file_area');
            const textOption = document.getElementById('textOption');
            const fileOption = document.getElementById('fileOption');
            const reasonFileInput = document.getElementById('reason_file');
            const reasonFilePreview = document.getElementById('reason_file_preview');
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('applicationForm');

            function toggleReasonInput() {
                if (reasonTypeText.checked) {
                    reasonTextArea.classList.add('active');
                    reasonFileArea.classList.remove('active');
                    textOption.classList.add('selected');
                    fileOption.classList.remove('selected');
                    document.getElementById('reason').required = true;
                    reasonFileInput.required = false;
                } else {
                    reasonTextArea.classList.remove('active');
                    reasonFileArea.classList.add('active');
                    textOption.classList.remove('selected');
                    fileOption.classList.add('selected');
                    document.getElementById('reason').required = false;
                    reasonFileInput.required = true;
                }
                validateForm();
            }

            function validateForm() {
                let isValid = true;
                
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim() && field.offsetParent !== null) {
                        isValid = false;
                    }
                });
                
                const fileInputs = form.querySelectorAll('input[type="file"][required]');
                fileInputs.forEach(input => {
                    if (input.offsetParent !== null && (!input.files || input.files.length === 0)) {
                        isValid = false;
                    }
                });
                
                submitBtn.disabled = !isValid;
            }

            reasonFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            reasonFilePreview.src = e.target.result;
                            reasonFilePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        reasonFilePreview.style.display = 'none';
                    }
                } else {
                    reasonFilePreview.style.display = 'none';
                }
                validateForm();
            });

            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            toggleReasonInput();
            validateForm();

            textOption.addEventListener('click', function() {
                reasonTypeText.checked = true;
                toggleReasonInput();
            });

            fileOption.addEventListener('click', function() {
                reasonTypeFile.checked = true;
                toggleReasonInput();
            });

            <?php if ($success): ?>
                document.getElementById('refId').textContent = '<?php echo $success_ref_id; ?>';
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Application Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your scholarship application has been updated.';
                <?php endif; ?>
                successMessage.classList.add('show');
                overlay.classList.add('show');
            <?php endif; ?>
            
            if (closeSuccessMessage) {
                closeSuccessMessage.addEventListener('click', function() {
                    successMessage.classList.remove('show');
                    overlay.classList.remove('show');
                    window.location.href = '../Pages/landingpage.php';
                });
            }
        });
    </script>
</body>
</html>