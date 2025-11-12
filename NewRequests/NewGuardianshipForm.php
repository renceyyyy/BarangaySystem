<?php
require_once __DIR__ . '/../config/session_resident.php';
require_once '../Process/db_connection.php';
require_once '../Process/user_activity_logger.php';
require_once './Terms&Conditions/Terms&Conditons.php';
$conn = getDBConnection();

// Initialize variables
$requestType = $childName = $childAge = $childAddress = $purpose = $applicantName = $applicantRelationship = "";
$guardianshipSince = $soloParentSince = "";
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
    $pendingCheckSql = "SELECT * FROM guardianshiptbl WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";
    $pendingStmt = $conn->prepare($pendingCheckSql);
    if ($pendingStmt) {
        $pendingStmt->bind_param("si", $updateRefNo, $_SESSION['user_id']);
        $pendingStmt->execute();
        $result = $pendingStmt->get_result();
        
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
            // Pre-fill form with pending request data
            $requestType = $pendingRequest['request_type'] ?? '';
            $childName = $pendingRequest['child_name'] ?? '';
            $childAge = $pendingRequest['child_age'] ?? '';
            $childAddress = $pendingRequest['child_address'] ?? '';
            $purpose = $pendingRequest['purpose'] ?? '';
            $applicantName = $pendingRequest['applicant_name'] ?? '';
            $applicantRelationship = $pendingRequest['applicant_relationship'] ?? '';
            $guardianshipSince = $pendingRequest['guardianship_since'] ?? '';
            $soloParentSince = $pendingRequest['solo_parent_since'] ?? '';
        } else {
            // Invalid update request
            header("Location: ../Pages/UserReports.php");
            exit();
        }
        $pendingStmt->close();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardianship_request"])) {
    // Get request type early for pending check
    $requestType = trim($_POST["requestType"] ?? '');
    
    // Check for existing pending request of SPECIFIC type (only for new submissions)
    if (!$isUpdateMode && !empty($requestType)) {
        $pendingCheckSql = "SELECT refno FROM guardianshiptbl WHERE UserId = ? AND request_type = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pendingStmt = $conn->prepare($pendingCheckSql);
        if ($pendingStmt) {
            $pendingStmt->bind_param("is", $_SESSION['user_id'], $requestType);
            $pendingStmt->execute();
            $pendingResult = $pendingStmt->get_result();
            
            if ($pendingResult->num_rows > 0) {
                $pendingData = $pendingResult->fetch_assoc();
                $errors[] = "You have a pending " . htmlspecialchars($requestType) . " request (Ref: " . htmlspecialchars($pendingData['refno']) . "). Please wait for approval or update your existing request before submitting a new one for this request type.";
            }
            $pendingStmt->close();
        }
    }
    
    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    // Get form data
    $childName = trim($_POST["childName"] ?? '');
    $childAge = trim($_POST["childAge"] ?? '');
    $childAddress = trim($_POST["childAddress"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');
    $applicantName = trim($_POST["applicantName"] ?? '');
    $applicantRelationship = trim($_POST["applicantRelationship"] ?? '');
    
    // Set date fields based on request type
    if ($requestType === 'Guardianship') {
        $guardianshipSince = trim($_POST["guardianshipSince"] ?? '');
        $soloParentSince = '';
    } elseif ($requestType === 'Solo Parent') {
        $guardianshipSince = '';
        $soloParentSince = trim($_POST["soloParentSince"] ?? '');
    } else {
        $guardianshipSince = '';
        $soloParentSince = '';
    }

    // Basic validation
    if (empty($requestType)) {
        $errors[] = "Request type is required";
    }
    
    if (empty($childName)) {
        $errors[] = "Child's name is required";
    }
    
    if (empty($childAge) || !is_numeric($childAge) || $childAge < 0 || $childAge > 18) {
        $errors[] = "Valid child age (0-21) is required";
    }
    
    if (empty($childAddress)) {
        $errors[] = "Child's address is required";
    }
    
    if (empty($purpose)) {
        $errors[] = "Purpose is required";
    }
    
    if (empty($applicantName)) {
        $errors[] = "Applicant's name is required";
    }
    
    if (empty($applicantRelationship)) {
        $errors[] = "Relationship to child is required";
    }
    
    if ($requestType === 'Guardianship' && empty($guardianshipSince)) {
        $errors[] = "Guardian since date is required";
    }
    
    if ($requestType === 'Solo Parent' && empty($soloParentSince)) {
        $errors[] = "Solo parent since date is required";
    }

    // If no errors, process the form
    if (empty($errors)) {
        // Convert empty date strings to NULL
        $guardianshipSince = (!empty($guardianshipSince)) ? $guardianshipSince : NULL;
        $soloParentSince = (!empty($soloParentSince)) ? $soloParentSince : NULL;

        try {
            // Get user ID
            $userId = $_SESSION['user_id'];
            
            if ($isUpdateMode) {
                // UPDATE existing pending request
                $sql = "UPDATE guardianshiptbl SET 
                        request_type = ?, child_name = ?, child_age = ?, child_address = ?, 
                        guardianship_since = ?, solo_parent_since = ?, purpose = ?,
                        applicant_name = ?, applicant_relationship = ?
                        WHERE refno = ? AND user_id = ? AND RequestStatus = 'Pending'";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "ssissssssi",
                    $requestType,
                    $childName,
                    $childAge,
                    $childAddress,
                    $guardianshipSince,
                    $soloParentSince,
                    $purpose,
                    $applicantName,
                    $applicantRelationship,
                    $updateRefNo,
                    $userId
                );
                
                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $updateRefNo;
                    $isUpdateSuccess = true; // Flag to show update success message
                    
                    // Log user activity for update
                    logUserActivity(
                        'Guardianship request updated',
                        'guardianship_request_update',
                        [
                            'request_type' => $requestType,
                            'child_name' => $childName,
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
                $table_check = $conn->query("SHOW TABLES LIKE 'guardianshiptbl'");
                if ($table_check->num_rows == 0) {
                    $create_table = "CREATE TABLE guardianshiptbl (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        refno VARCHAR(50) NOT NULL,
                        request_type ENUM('Guardianship','Solo Parent') NOT NULL,
                        child_name VARCHAR(100) NOT NULL,
                        child_age INT(3) NOT NULL,
                        child_address TEXT NOT NULL,
                        guardianship_since DATE NULL,
                        solo_parent_since DATE NULL,
                        purpose TEXT NOT NULL,
                        applicant_name VARCHAR(100) NOT NULL,
                        applicant_relationship VARCHAR(50) NOT NULL,
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
                $sql = "INSERT INTO guardianshiptbl (
                    refno, request_type, child_name, child_age, child_address, 
                    guardianship_since, solo_parent_since, purpose,
                    applicant_name, applicant_relationship, request_date, user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "sssissssssi",
                    $refno,
                    $requestType,
                    $childName,
                    $childAge,
                    $childAddress,
                    $guardianshipSince,
                    $soloParentSince,
                    $purpose,
                    $applicantName,
                    $applicantRelationship,
                    $userId
                );

                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $refno;
                    
                    // Log user activity
                    logUserActivity(
                        'Guardianship/Solo Parent request submitted',
                        'guardianship_request',
                        [
                            'request_type' => $requestType,
                            'child_name' => $childName,
                            'reference_no' => $refno
                        ]
                    );
                    
                    // Reset form
                    $requestType = $childName = $childAge = $childAddress = $purpose = $applicantName = $applicantRelationship = "";
                    $guardianshipSince = $soloParentSince = "";
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
    <title>Guardianship / Solo Parent Application</title>
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
        input[type="number"],
        input[type="date"],
        select,
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
        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .radio-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        .radio-option.selected {
            border-color: #4CAF50;
            background-color: #f0fff0;
        }
        .radio-option input[type="radio"] {
            display: none;
        }
        .conditional-field {
            display: none;
        }
        .conditional-field.active {
            display: block;
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
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Application Submitted Successfully!</h3>
        <p>Your <?php echo htmlspecialchars($requestType); ?> application has been received.</p>
        <p>Reference Number: <strong id="refNo"><?php echo htmlspecialchars($success_ref_no); ?></strong></p>
        <p>Please save this reference number for tracking your application.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>
    
    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Guardianship Application' : 'Guardianship / Solo Parent Application'; ?></h1>
        
        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px;">
                <strong>Update Mode:</strong> You are updating your pending request (Reference: <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your request.
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
        
        <form method="POST" action="" id="guardianshipForm">
            <input type="hidden" name="guardianship_request" value="1">
            
            <div class="form-section">
                <h2>Application Type</h2>
                
                <div class="radio-group">
                    <div class="radio-option <?php echo $requestType === 'Guardianship' ? 'selected' : ''; ?>" id="guardianshipOption">
                        <input type="radio" id="requestType_guardianship" name="requestType" value="Guardianship" <?php echo $requestType === 'Guardianship' ? 'checked' : ''; ?>>
                        <label for="requestType_guardianship">Guardianship</label>
                        <div class="info-text">For legal guardians of a child</div>
                    </div>
                    <div class="radio-option <?php echo $requestType === 'Solo Parent' ? 'selected' : ''; ?>" id="soloParentOption">
                        <input type="radio" id="requestType_solo" name="requestType" value="Solo Parent" <?php echo $requestType === 'Solo Parent' ? 'checked' : ''; ?>>
                        <label for="requestType_solo">Solo Parent</label>
                        <div class="info-text">For single parents</div>
                    </div>
                </div>
                
                <div id="guardianshipSinceField" class="conditional-field <?php echo $requestType === 'Guardianship' ? 'active' : ''; ?>">
                    <div class="form-group">
                        <label for="guardianshipSince">Guardian Since <span class="required">*</span></label>
                        <input type="date" id="guardianshipSince" name="guardianshipSince" value="<?php echo htmlspecialchars($guardianshipSince); ?>">
                        <div class="info-text">Date when you became the child's guardian</div>
                    </div>
                </div>
                
                <div id="soloParentSinceField" class="conditional-field <?php echo $requestType === 'Solo Parent' ? 'active' : ''; ?>">
                    <div class="form-group">
                        <label for="soloParentSince">Solo Parent Since <span class="required">*</span></label>
                        <input type="date" id="soloParentSince" name="soloParentSince" value="<?php echo htmlspecialchars($soloParentSince); ?>">
                        <div class="info-text">Date when you became a solo parent</div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Child Information</h2>
                
                <div class="form-group">
                    <label for="childName">Child's Full Name <span class="required">*</span></label>
                    <input type="text" id="childName" name="childName" value="<?php echo htmlspecialchars($childName); ?>" placeholder="Enter child's full name">
                </div>
                
                <div class="form-group">
                    <label for="childAge">Child's Age <span class="required">*</span></label>
                    <input type="number" id="childAge" name="childAge" value="<?php echo htmlspecialchars($childAge); ?>" min="0" max="18" placeholder="Enter age (0-18)">
                    <div class="info-text">Age must be between 0 and 18 years</div>
                </div>
                
                <div class="form-group">
                    <label for="childAddress">Child's Address <span class="required">*</span></label>
                    <textarea id="childAddress" name="childAddress" placeholder="Enter complete address of the child"><?php echo htmlspecialchars($childAddress); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Applicant Information</h2>
                
                <div class="form-group">
                    <label for="applicantName">Your Full Name <span class="required">*</span></label>
                    <input type="text" id="applicantName" name="applicantName" value="<?php echo htmlspecialchars($applicantName); ?>" placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="applicantRelationship">Relationship to Child <span class="required">*</span></label>
                    <input type="text" id="applicantRelationship" name="applicantRelationship" value="<?php echo htmlspecialchars($applicantRelationship); ?>" placeholder="e.g., Mother, Father, Grandparent, Aunt, etc.">
                </div>
                
                <div class="form-group">
                    <label for="purpose">Purpose of Application <span class="required">*</span></label>
                    <textarea id="purpose" name="purpose" placeholder="Explain why you are applying for guardianship/solo parent status"><?php echo htmlspecialchars($purpose); ?></textarea>
                    <div class="info-text">Please provide detailed explanation for your application</div>
                </div>
            </div>
            
            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('guardianshipForm'); ?>
            
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
            const guardianshipOption = document.getElementById('guardianshipOption');
            const soloParentOption = document.getElementById('soloParentOption');
            const guardianshipSinceField = document.getElementById('guardianshipSinceField');
            const soloParentSinceField = document.getElementById('soloParentSinceField');
            const guardianshipRadio = document.getElementById('requestType_guardianship');
            const soloParentRadio = document.getElementById('requestType_solo');
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('guardianshipForm');

            function toggleRequestType() {
                if (guardianshipRadio.checked) {
                    guardianshipOption.classList.add('selected');
                    soloParentOption.classList.remove('selected');
                    guardianshipSinceField.classList.add('active');
                    soloParentSinceField.classList.remove('active');
                } else if (soloParentRadio.checked) {
                    guardianshipOption.classList.remove('selected');
                    soloParentOption.classList.add('selected');
                    guardianshipSinceField.classList.remove('active');
                    soloParentSinceField.classList.add('active');
                } else {
                    guardianshipOption.classList.remove('selected');
                    soloParentOption.classList.remove('selected');
                    guardianshipSinceField.classList.remove('active');
                    soloParentSinceField.classList.remove('active');
                }
                validateForm();
            }

            function validateForm() {
                let isValid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (field.type === 'radio') {
                        const radioGroup = form.querySelectorAll(`input[name="${field.name}"]`);
                        const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                        if (!isChecked) {
                            isValid = false;
                        }
                    } else {
                        if (!field.value.trim()) {
                            isValid = false;
                        }
                    }
                });

                // Additional validation for conditional fields
                if (guardianshipRadio.checked && !document.getElementById('guardianshipSince').value) {
                    isValid = false;
                }
                if (soloParentRadio.checked && !document.getElementById('soloParentSince').value) {
                    isValid = false;
                }

                // Age validation
                const childAge = document.getElementById('childAge').value;
                if (childAge && (childAge < 0 || childAge > 18)) {
                    isValid = false;
                }

                submitBtn.disabled = !isValid;
            }

            // Event listeners
            guardianshipOption.addEventListener('click', function() {
                guardianshipRadio.checked = true;
                toggleRequestType();
            });

            soloParentOption.addEventListener('click', function() {
                soloParentRadio.checked = true;
                toggleRequestType();
            });

            guardianshipRadio.addEventListener('change', toggleRequestType);
            soloParentRadio.addEventListener('change', toggleRequestType);

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Initialize
            toggleRequestType();
            validateForm();

            // Show success message if submission was successful
            <?php if ($success): ?>
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your guardianship request has been updated.';
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

            // Set maximum date for date fields to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('guardianshipSince').max = today;
            document.getElementById('soloParentSince').max = today;
        });
    </script>
</body>
</html>

