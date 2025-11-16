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

// Check if we're in update mode
$isUpdateMode = false;
$updateRefNo = null;
$pendingRequest = null;
$isUpdateSuccess = false; // Track if update was successful

if (isset($_GET['update'])) {
    $updateRefNo = $_GET['update'];
    $isUpdateMode = true;
    
    // Fetch pending request data
    $pendingCheckSql = "SELECT * FROM unemploymenttbl WHERE refno = ? AND user_id = ? AND RequestStatus = 'Pending'";
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

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$user_data = [];

$user_sql = "SELECT Firstname, Lastname, Age, Address FROM userloginfo WHERE UserID = ?";
$user_stmt = $conn->prepare($user_sql);
if ($user_stmt) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        
        // Use pending request data if in update mode, otherwise use profile data
        if ($isUpdateMode && $pendingRequest) {
            $fullname = $pendingRequest['fullname'] ?? '';
            $age = $pendingRequest['age'] ?? '';
            $address = $pendingRequest['address'] ?? '';
            $certificateType = $pendingRequest['certificate_type'] ?? '';
            $purpose = $pendingRequest['purpose'] ?? '';
            $unemployedSince = $pendingRequest['unemployed_since'] ?? '';
            $noFixedIncomeSince = $pendingRequest['no_fixed_income_since'] ?? '';
        } else {
            // Pre-populate form fields with user data
            $firstname = $user_data['Firstname'] ?? '';
            $lastname = $user_data['Lastname'] ?? '';
            $age = $user_data['Age'] ?? '';
            $address = $user_data['Address'] ?? '';
            
            // Check for default values and replace them with empty strings
            if ($firstname === 'uncompleted') $firstname = '';
            if ($lastname === 'uncompleted') $lastname = '';
            if ($age === '0') $age = '';
            if ($address === 'uncompleted') $address = '';
            
            // Combine first and last name for fullname
            $fullname = trim($firstname . ' ' . $lastname);
            $certificateType = '';
            $purpose = '';
            $unemployedSince = '';
            $noFixedIncomeSince = '';
        }
    }
    $user_stmt->close();
}

// Handle unemployment request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["unemployment_request"])) {
    $errors = [];
    
    // Get certificate type early for pending check
    $certificateType = trim($_POST["certificateType"] ?? '');
    
    // Check for existing pending request of SPECIFIC type (only for new submissions)
    if (!$isUpdateMode && !empty($certificateType)) {
        $pendingCheckSql = "SELECT refno FROM unemploymenttbl WHERE user_id = ? AND certificate_type = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pendingStmt = $conn->prepare($pendingCheckSql);
        if ($pendingStmt) {
            $pendingStmt->bind_param("is", $user_id, $certificateType);
            $pendingStmt->execute();
            $pendingResult = $pendingStmt->get_result();
            
            if ($pendingResult->num_rows > 0) {
                $pendingData = $pendingResult->fetch_assoc();
                $errors[] = "You have a pending " . htmlspecialchars($certificateType) . " Certificate request (Ref: " . htmlspecialchars($pendingData['refno']) . "). Please wait for approval or update your existing request before submitting a new one for this certificate type.";
            }
            $pendingStmt->close();
        }
    }
    
    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    $success = false;
    $success_ref_no = '';

    $certificateType = trim($_POST["certificateType"] ?? '');
    $fullname = trim($_POST["fullname"] ?? '');
    $age = trim($_POST["age"] ?? '');
    $address = trim($_POST["address"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');
    $unemployedSince = ($certificateType === 'No Income') ? trim($_POST["unemployedSince"] ?? '') : null;
    $noFixedIncomeSince = ($certificateType === 'No Fixed Income') ? trim($_POST["noFixedIncomeSince"] ?? '') : null;

    // Basic validation
    if (empty($certificateType))
        $errors[] = "Certificate type is required";
    if (empty($fullname))
        $errors[] = "Full name is required";
    if (empty($age) || !is_numeric($age) || $age < 18 || $age > 99)
        $errors[] = "Valid age (18-99) is required";
    if (empty($address))
        $errors[] = "Address is required";
    if (empty($purpose))
        $errors[] = "Purpose is required";
    if ($certificateType === 'No Income' && empty($unemployedSince))
        $errors[] = "Unemployed since date is required";
    if ($certificateType === 'No Fixed Income' && empty($noFixedIncomeSince))
        $errors[] = "No fixed income since date is required";

    // If no errors, process the request
    if (empty($errors)) {
        try {
            if ($isUpdateMode) {
                // UPDATE existing pending request
                $sql = "UPDATE unemploymenttbl SET 
                        certificate_type = ?, fullname = ?, age = ?, address = ?, 
                        unemployed_since = ?, no_fixed_income_since = ?, purpose = ?
                        WHERE refno = ? AND user_id = ? AND RequestStatus = 'Pending'";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "ssisssssi",
                    $certificateType,
                    $fullname,
                    $age,
                    $address,
                    $unemployedSince,
                    $noFixedIncomeSince,
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
                        'Unemployment certificate request updated',
                        'unemployment_certificate_update',
                        [
                            'certificate_type' => $certificateType,
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
                $table_check = $conn->query("SHOW TABLES LIKE 'unemploymenttbl'");
                if ($table_check->num_rows == 0) {
                    $create_table = "CREATE TABLE unemploymenttbl (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        refno VARCHAR(50) NOT NULL,
                        certificate_type ENUM('No Income','No Fixed Income') NOT NULL,
                        fullname VARCHAR(100) NOT NULL,
                        age INT(3) NOT NULL,
                        address TEXT NOT NULL,
                        unemployed_since DATE NULL,
                        no_fixed_income_since DATE NULL,
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

                // Get user ID
                $userId = $_SESSION['user_id'];

                // Insert into database
                $sql = "INSERT INTO unemploymenttbl (
                    refno, certificate_type, fullname, age, address, 
                    unemployed_since, no_fixed_income_since, purpose, 
                    request_date, user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "sssissssi",
                    $refno,
                    $certificateType,
                    $fullname,
                    $age,
                    $address,
                    $unemployedSince,
                    $noFixedIncomeSince,
                    $purpose,
                    $userId
                );

                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $refno;
                    
                    // Log user activity
                    logUserActivity(
                        'Unemployment certificate requested',
                        'unemployment_request',
                        [
                            'certificate_type' => $certificateType,
                            'reference_no' => $refno
                        ]
                    );
                    
                    // Reset form but keep user data
                    $purpose = '';
                    $unemployedSince = '';
                    $noFixedIncomeSince = '';
                    $certificateType = '';
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
    <title>No Fixed Income Certificate Request</title>
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
        .certificate-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .certificate-option {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .certificate-option.selected {
            border-color: #4CAF50;
            background-color: #f0fff0;
        }
        .certificate-option input[type="radio"] {
            display: none;
        }
        .date-field {
            display: none;
        }
        .date-field.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Certificate Request Submitted Successfully!</h3>
        <p>Your <?php echo isset($certificateType) ? $certificateType : ''; ?> certificate request has been received.</p>
        <p>Reference Number: <strong id="refNo"></strong></p>
        <p>Please keep this reference number for tracking your request.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>
    
    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Certificate Request' : 'No Fixed Income Certificate Request'; ?></h1>
        
        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff;">
                <strong>Update Mode:</strong> You are updating your pending request (Reference: <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your request.
            </div>
        <?php else: ?>
            <div class="user-info-note">
                <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and update if necessary. payment for your request is due within 7 days.
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
        
        <form method="POST" action="" id="unemploymentForm">
            <input type="hidden" name="unemployment_request" value="1">
            
            <div class="form-section">
                <h2>Certificate Type</h2>
                
                <div class="form-group">
                    <label>Select Certificate Type <span class="required">*</span></label>
                    <div class="certificate-options">
                        <div class="certificate-option <?php echo (isset($certificateType) && $certificateType === 'No Income') ? 'selected' : ''; ?>" id="noIncomeOption">
                            <input type="radio" id="certificate_type_no_income" name="certificateType" value="No Income" <?php echo (isset($certificateType) && $certificateType === 'No Income') ? 'checked' : ''; ?>>
                            <label for="certificate_type_no_income">
                                <strong>No Income Certificate</strong><br>
                                <small>For individuals who are currently unemployed</small>
                            </label>
                        </div>
                        <div class="certificate-option <?php echo (isset($certificateType) && $certificateType === 'No Fixed Income') ? 'selected' : ''; ?>" id="noFixedIncomeOption">
                            <input type="radio" id="certificate_type_no_fixed_income" name="certificateType" value="No Fixed Income" <?php echo (isset($certificateType) && $certificateType === 'No Fixed Income') ? 'checked' : ''; ?>>
                            <label for="certificate_type_no_fixed_income">
                                <strong>No Fixed Income Certificate</strong><br>
                                <small>For individuals with irregular or variable income</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div id="unemployedSinceField" class="form-group date-field <?php echo (isset($certificateType) && $certificateType === 'No Income') ? 'active' : ''; ?>">
                    <label for="unemployedSince">Unemployed Since <span class="required">*</span></label>
                    <input type="date" id="unemployedSince" name="unemployedSince" value="<?php echo htmlspecialchars($unemployedSince ?? ''); ?>">
                </div>
                
                <div id="noFixedIncomeSinceField" class="form-group date-field <?php echo (isset($certificateType) && $certificateType === 'No Fixed Income') ? 'active' : ''; ?>">
                    <label for="noFixedIncomeSince">No Fixed Income Since <span class="required">*</span></label>
                    <input type="date" id="noFixedIncomeSince" name="noFixedIncomeSince" value="<?php echo htmlspecialchars($noFixedIncomeSince ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="fullname">Full Name <span class="required">*</span></label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="age">Age <span class="required">*</span></label>
                    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age ?? ''); ?>" min="18" max="99" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <textarea id="address" name="address" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Request Details</h2>
                
                <div class="form-group">
                    <label for="purpose">Purpose of Request <span class="required">*</span></label>
                    <textarea id="purpose" name="purpose" placeholder="Please specify the purpose for requesting this certificate..." required><?php echo htmlspecialchars($purpose ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('unemploymentForm'); ?>
            
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                <a href="../Pages/landingpage.php" class="btn btn-secondary" style="background-color: #6c757d; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn" id="submitBtn"><?php echo $isUpdateMode ? 'Update Request' : 'Submit Request'; ?></button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const noIncomeOption = document.getElementById('noIncomeOption');
            const noFixedIncomeOption = document.getElementById('noFixedIncomeOption');
            const certificateTypeNoIncome = document.getElementById('certificate_type_no_income');
            const certificateTypeNoFixedIncome = document.getElementById('certificate_type_no_fixed_income');
            const unemployedSinceField = document.getElementById('unemployedSinceField');
            const noFixedIncomeSinceField = document.getElementById('noFixedIncomeSinceField');
            const unemployedSinceInput = document.getElementById('unemployedSince');
            const noFixedIncomeSinceInput = document.getElementById('noFixedIncomeSince');
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('unemploymentForm');

            // Toggle certificate type function

            function toggleCertificateType() {
                if (certificateTypeNoIncome.checked) {
                    noIncomeOption.classList.add('selected');
                    noFixedIncomeOption.classList.remove('selected');
                    unemployedSinceField.classList.add('active');
                    noFixedIncomeSinceField.classList.remove('active');
                    unemployedSinceInput.required = true;
                    noFixedIncomeSinceInput.required = false;
                } else if (certificateTypeNoFixedIncome.checked) {
                    noIncomeOption.classList.remove('selected');
                    noFixedIncomeOption.classList.add('selected');
                    unemployedSinceField.classList.remove('active');
                    noFixedIncomeSinceField.classList.add('active');
                    unemployedSinceInput.required = false;
                    noFixedIncomeSinceInput.required = true;
                } else {
                    unemployedSinceField.classList.remove('active');
                    noFixedIncomeSinceField.classList.remove('active');
                    unemployedSinceInput.required = false;
                    noFixedIncomeSinceInput.required = false;
                }
            }

            // Event listeners for certificate type change
            noIncomeOption.addEventListener('click', function() {
                certificateTypeNoIncome.checked = true;
                toggleCertificateType();
            });

            noFixedIncomeOption.addEventListener('click', function() {
                certificateTypeNoFixedIncome.checked = true;
                toggleCertificateType();
            });

            // Initialize
            toggleCertificateType();

            // Set maximum date for date fields to today
            const today = new Date().toISOString().split('T')[0];
            unemployedSinceInput.max = today;
            noFixedIncomeSinceInput.max = today;

            // Show success message if submission was successful
            <?php if (isset($success) && $success): ?>
                document.getElementById('refNo').textContent = '<?php echo $success_ref_no; ?>';
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your unemployment certificate request has been updated.';
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

