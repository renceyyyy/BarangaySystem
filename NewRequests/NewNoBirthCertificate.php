<?php
session_start();
require_once '../Process/db_connection.php';
require_once '../Process/user_activity_logger.php';
require_once './Terms&Conditions/Terms&Conditons.php';
$conn = getDBConnection();

// Initialize variables
$requestorName = $requestorBirthday = $requestorAddress = $purpose = "";
$errors = [];
$success = false;
$success_ref_no = "";
$isUpdateSuccess = false; // Track if update was successful

// Check if user is logged in
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
    $pendingCheckSql = "SELECT * FROM no_birthcert_tbl WHERE refno = ? AND user_id = ? AND RequestStatus = 'Pending'";
    $pendingStmt = $conn->prepare($pendingCheckSql);
    if ($pendingStmt) {
        $pendingStmt->bind_param("si", $updateRefNo, $_SESSION['user_id']);
        $pendingStmt->execute();
        $result = $pendingStmt->get_result();
        
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
            // Pre-fill form with pending request data
            $requestorName = $pendingRequest['requestor_name'] ?? '';
            $requestorBirthday = $pendingRequest['requestor_birthday'] ?? '';
            $requestorAddress = $pendingRequest['requestor_address'] ?? '';
            $purpose = $pendingRequest['purpose'] ?? '';
        } else {
            // Invalid update request
            header("Location: ../Pages/UserReports.php");
            exit();
        }
        $pendingStmt->close();
    }
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$user_data = [];

$user_sql = "SELECT Firstname, Lastname, Middlename, Birthdate, Address FROM userloginfo WHERE UserID = ?";
$user_stmt = $conn->prepare($user_sql);
if ($user_stmt) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();

        // Only pre-populate if NOT in update mode
        if (!$isUpdateMode) {
            // Pre-populate form fields with user data
            $firstname = $user_data['Firstname'] ?? '';
            $lastname = $user_data['Lastname'] ?? '';
            $middlename = $user_data['Middlename'] ?? '';
            $birthdate = $user_data['Birthdate'] ?? '';
            $address = $user_data['Address'] ?? '';

            // Check for default values and replace them with empty strings
            if ($firstname === 'uncompleted') $firstname = '';
            if ($lastname === 'uncompleted') $lastname = '';
            if ($middlename === 'uncompleted') $middlename = '';
            if ($birthdate === '0') $birthdate = '';
            if ($address === 'uncompleted') $address = '';

            // Build requestor name from user data
            $requestorName = trim("$firstname " . ($middlename ? "$middlename " : "") . "$lastname");
            
            // Set other fields from user data
            $requestorBirthday = $birthdate;
            $requestorAddress = $address;
        }
    }
    $user_stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["no_birthcert_request"])) {
    // Check for existing pending request (only for new submissions)
    if (!$isUpdateMode) {
        $pendingCheckSql = "SELECT refno FROM no_birthcert_tbl WHERE user_id = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pendingStmt = $conn->prepare($pendingCheckSql);
        if ($pendingStmt) {
            $pendingStmt->bind_param("i", $user_id);
            $pendingStmt->execute();
            $pendingResult = $pendingStmt->get_result();
            
            if ($pendingResult->num_rows > 0) {
                $pendingData = $pendingResult->fetch_assoc();
                $errors[] = "You have a pending No Birth Certificate request (Ref: " . htmlspecialchars($pendingData['refno']) . "). Please wait for approval or update your existing request before submitting a new one.";
            }
            $pendingStmt->close();
        }
    }
    
    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    // Get form data
    $requestorName = trim($_POST["requestorName"] ?? '');
    $requestorBirthday = trim($_POST["requestorBirthday"] ?? '');
    $requestorAddress = trim($_POST["requestorAddress"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');

    // Basic validation
    if (empty($requestorName)) {
        $errors[] = "Requestor's name is required";
    }
    
    if (empty($requestorBirthday)) {
        $errors[] = "Requestor's birthday is required";
    }
    
    if (empty($requestorAddress)) {
        $errors[] = "Requestor's address is required";
    }
    
    if (empty($purpose)) {
        $errors[] = "Purpose is required";
    }

    // Validate birthday format
    if (!empty($requestorBirthday)) {
        $date = DateTime::createFromFormat('Y-m-d', $requestorBirthday);
        if (!$date || $date->format('Y-m-d') !== $requestorBirthday) {
            $errors[] = "Invalid birthday format (YYYY-MM-DD)";
        }
    }

    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Get user ID
            $userId = $_SESSION['user_id'];
            
            if ($isUpdateMode) {
                // UPDATE existing pending request
                $sql = "UPDATE no_birthcert_tbl SET 
                        requestor_name = ?, requestor_birthday = ?, 
                        requestor_address = ?, purpose = ?
                        WHERE refno = ? AND user_id = ? AND RequestStatus = 'Pending'";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "sssssi",
                    $requestorName,
                    $requestorBirthday,
                    $requestorAddress,
                    $purpose,
                    $updateRefNo,
                    $userId
                );
                
                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $updateRefNo;
                    $isUpdateSuccess = true; // Flag to show update success message
                    
                    // Log user activity for update
                    logUserActivity(
                        'No birth certificate request updated',
                        'no_birth_certificate_update',
                        [
                            'requestor_name' => $requestorName,
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
                // Generate reference number
                $refno = date('Ymd') . rand(1000, 9999);

                // Check if table exists, create if not
                $table_check = $conn->query("SHOW TABLES LIKE 'no_birthcert_tbl'");
                if ($table_check->num_rows == 0) {
                    $create_table = "CREATE TABLE no_birthcert_tbl (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        refno VARCHAR(50) NOT NULL,
                        requestor_name VARCHAR(100) NOT NULL,
                        requestor_birthday DATE NOT NULL,
                        requestor_address TEXT NOT NULL,
                        purpose TEXT NOT NULL,
                        request_date DATETIME NOT NULL,
                        RequestStatus ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
                        user_id INT NULL,
                        UNIQUE KEY (refno)
                    )";

                    if (!$conn->query($create_table)) {
                        throw new Exception("Error creating table: " . $conn->error);
                    }
                }

                // Insert into database
                $sql = "INSERT INTO no_birthcert_tbl (
                    refno, requestor_name, requestor_birthday, 
                    requestor_address, purpose, request_date, user_id
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?)";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "sssssi",
                    $refno,
                    $requestorName,
                    $requestorBirthday,
                    $requestorAddress,
                    $purpose,
                    $userId
                );

                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $refno;
                    
                    // Log user activity
                    logUserActivity(
                        'No birth certificate request submitted',
                        'no_birth_certificate_request',
                        [
                            'requestor_name' => $requestorName,
                            'reference_no' => $refno
                        ]
                    );
                    
                    // Reset form but keep user data for future applications
                    $purpose = "";
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $stmt->close();
            }
        } catch (Exception $e) {
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
    <title>No Birth Certificate Application</title>
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
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .required {
            color: red;
        }
        .error {
            color: red;
            background-color: #ffe6e6;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid red;
        }
        .success {
            color: green;
            background-color: #e6ffe6;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid green;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
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
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 6px;
            border-left: 4px solid #4CAF50;
        }
        .form-section h2 {
            color: #444;
            margin-bottom: 15px;
            margin-top: 0;
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
        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .form-description {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        .form-description p {
            margin: 0;
            color: #333;
        }
        .user-info-note {
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        .user-info-note strong {
            color: #2b2a2aff;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Application Submitted Successfully!</h3>
        <p>Your No Birth Certificate application has been received.</p>
        <p>Reference Number: <strong id="refNo"><?php echo htmlspecialchars($success_ref_no); ?></strong></p>
        <p>Please save this reference number for tracking your application.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>
    
    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update No Birth Certificate Application' : 'No Birth Certificate Application'; ?></h1>
        
        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff;">
                <strong>Update Mode:</strong> You are updating your pending request (Reference: <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your request.
            </div>
        <?php else: ?>
            <div class="user-info-note">
                <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and update if necessary.
            </div>
        <?php endif; ?>
        
        <div class="form-description">
            <p><strong>Purpose:</strong> Use this form to apply for documentation when you don't have a birth certificate. This application helps establish your identity and personal details for official purposes.</p>
        </div>
        
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
        
        <form method="POST" action="" id="birthCertForm">
            <input type="hidden" name="no_birthcert_request" value="1">
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="requestorName">Full Name <span class="required">*</span></label>
                    <input type="text" id="requestorName" name="requestorName" value="<?php echo htmlspecialchars($requestorName); ?>" placeholder="Enter your complete full name" required>
                    <div class="info-text">Enter your full name as it should appear on official documents</div>
                </div>
                
                <div class="form-group">
                    <label for="requestorBirthday">Date of Birth <span class="required">*</span></label>
                    <input type="date" id="requestorBirthday" name="requestorBirthday" value="<?php echo htmlspecialchars($requestorBirthday); ?>" required>
                    <div class="info-text">Your date of birth in YYYY-MM-DD format</div>
                </div>
                
                <div class="form-group">
                    <label for="requestorAddress">Complete Address <span class="required">*</span></label>
                    <textarea id="requestorAddress" name="requestorAddress" placeholder="Enter your complete current address including street, barangay, city, and province" required><?php echo htmlspecialchars($requestorAddress); ?></textarea>
                    <div class="info-text">Include complete address details for verification purposes</div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Application Details</h2>
                
                <div class="form-group">
                    <label for="purpose">Purpose of Application <span class="required">*</span></label>
                    <textarea id="purpose" name="purpose" placeholder="Explain why you need this documentation and what it will be used for" required><?php echo htmlspecialchars($purpose); ?></textarea>
                    <div class="info-text">Please provide detailed explanation of why you need this documentation (e.g., for school enrollment, employment, government ID application, etc.)</div>
                </div>
            </div>
            
            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('birthCertForm'); ?>
            
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                <a href="../Pages/landingpage.php" class="btn btn-secondary" style="background-color: #6c757d; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn" id="submitBtn"><?php echo $isUpdateMode ? 'Update Application' : 'Submit Application'; ?></button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('birthCertForm');

            function validateForm() {
                let isValid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                    }
                });

                // Additional validation for date field
                const birthday = document.getElementById('requestorBirthday').value;
                if (birthday) {
                    const today = new Date().toISOString().split('T')[0];
                    if (birthday > today) {
                        isValid = false;
                        // You can add a specific error message here if needed
                    }
                }

                submitBtn.disabled = !isValid;
            }

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Initialize
            validateForm();

            // Set maximum date for birthday field to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('requestorBirthday').max = today;

            // Show success message if submission was successful
            <?php if ($success): ?>
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your no birth certificate request has been updated.';
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

