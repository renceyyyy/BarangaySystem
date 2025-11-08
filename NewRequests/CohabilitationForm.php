<?php
require_once '../Process/db_connection.php';
require_once './Terms&Conditions/Terms&Conditons.php';
session_start();
$conn = getDBConnection();

// Initialize variables
$firstname = $lastname = $name = $partnersince = $purpose = "";
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
    $pendingCheckSql = "SELECT * FROM cohabitationtbl WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";
    $pendingStmt = $conn->prepare($pendingCheckSql);
    if ($pendingStmt) {
        $pendingStmt->bind_param("si", $updateRefNo, $_SESSION['user_id']);
        $pendingStmt->execute();
        $result = $pendingStmt->get_result();
        
        if ($result->num_rows > 0) {
            $pendingRequest = $result->fetch_assoc();
            // Pre-fill form with pending request data
            $name = $pendingRequest['Name'] ?? '';
            $partnersince = $pendingRequest['Partnersince'] ?? '';
            $purpose = $pendingRequest['Purpose'] ?? '';
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

$user_sql = "SELECT Firstname, Lastname, Gender, ContactNo, Address FROM userloginfo WHERE UserID = ?";
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
            $name = trim($firstname . ' ' . $lastname);
            
            // Check for default values and replace them with empty strings
            if ($firstname === 'uncompleted') $firstname = '';
            if ($lastname === 'uncompleted') $lastname = '';
            if (strpos($name, 'uncompleted') !== false) $name = '';
        }
    }
    $user_stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cohabitation_request"])) {
    // Check for existing pending request (only for new submissions)
    if (!$isUpdateMode) {
        $pendingCheckSql = "SELECT refno FROM cohabitationtbl WHERE UserId = ? AND RequestStatus = 'Pending' LIMIT 1";
        $pendingStmt = $conn->prepare($pendingCheckSql);
        if ($pendingStmt) {
            $pendingStmt->bind_param("i", $user_id);
            $pendingStmt->execute();
            $pendingResult = $pendingStmt->get_result();
            
            if ($pendingResult->num_rows > 0) {
                $pendingData = $pendingResult->fetch_assoc();
                $errors[] = "You have a pending Cohabitation Certificate request (Ref: " . htmlspecialchars($pendingData['refno']) . "). Please wait for approval or update your existing request before submitting a new one.";
            }
            $pendingStmt->close();
        }
    }
    
    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    // Get form data
    $name = trim($_POST["name"] ?? '');
    $partnersince = trim($_POST["partnersince"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');

    // Basic validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($partnersince)) {
        $errors[] = "Partner since date is required";
    }
    
    if (empty($purpose)) {
        $errors[] = "Purpose is required";
    }

    // Validate partner since date format
    if (!empty($partnersince)) {
        $date = DateTime::createFromFormat('Y-m-d', $partnersince);
        if (!$date || $date->format('Y-m-d') !== $partnersince) {
            $errors[] = "Invalid partner since date format (YYYY-MM-DD)";
        }
    }

    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Get user ID
            $userId = $_SESSION['user_id'];
            
            if ($isUpdateMode) {
                // UPDATE existing pending request
                $sql = "UPDATE cohabitationtbl SET 
                        Name = ?, Partnersince = ?, Purpose = ?
                        WHERE refno = ? AND UserId = ? AND RequestStatus = 'Pending'";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "ssssi",
                    $name,
                    $partnersince,
                    $purpose,
                    $updateRefNo,
                    $userId
                );
                
                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $updateRefNo;
                    $isUpdateSuccess = true; // Flag to show update success message
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $stmt->close();
            } else {
                // INSERT new request
                // Generate reference number
                $refno = date('Ymd') . rand(1000, 9999);

                // Check if table exists, create if not
                $table_check = $conn->query("SHOW TABLES LIKE 'cohabitationtbl'");
                if ($table_check->num_rows == 0) {
                    $create_table = "CREATE TABLE cohabitationtbl (
                        ReqID INT AUTO_INCREMENT PRIMARY KEY,
                        UserId INT NOT NULL,
                        Name VARCHAR(255) NOT NULL,
                        Partnersince VARCHAR(255) NOT NULL,
                        Purpose VARCHAR(255) NOT NULL,
                        refno VARCHAR(255) NOT NULL UNIQUE,
                        Reason VARCHAR(255),
                        DocuType VARCHAR(255) NOT NULL DEFAULT 'Cohabitation Form',
                        RequestStatus VARCHAR(50) DEFAULT 'Pending',
                        DateRequested TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";

                    if (!$conn->query($create_table)) {
                        throw new Exception("Error creating table: " . $conn->error);
                    }
                }

                // Insert into database (without reason - admin will add that)
                $sql = "INSERT INTO cohabitationtbl (
                    UserId, Name, Partnersince, Purpose, refno, DocuType, RequestStatus
                ) VALUES (?, ?, ?, ?, ?, 'Cohabitation Form', 'Pending')";

                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    "issss",
                    $userId,
                    $name,
                    $partnersince,
                    $purpose,
                    $refno
                );

                if ($stmt->execute()) {
                    $success = true;
                    $success_ref_no = $refno;
                    // Reset form
                    $name = $partnersince = $purpose = "";
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $stmt->close();
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cohabitation Form</title>
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

        .text-small {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: block;
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
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Cohabitation Form Submitted Successfully!</h3>
        <p>Your cohabitation form has been received.</p>
        <p>Reference Number: <strong id="refNo"></strong></p>
        <p>Please keep this reference number for tracking your request.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>

    <div class="container">
        <h1><?php echo $isUpdateMode ? 'Update Cohabitation Form' : 'Cohabitation Form'; ?></h1>

        <?php if ($isUpdateMode): ?>
            <div class="user-info-note" style="background-color: #e6f7ff; border-left-color: #1890ff;">
                <strong>Update Mode:</strong> You are updating your pending request (Reference: <?php echo htmlspecialchars($updateRefNo); ?>). Modify the information below and submit to update your request.
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

        <form method="POST" action="" id="cohabitationForm">
            <input type="hidden" name="cohabitation_request" value="1">
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="name">Your Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter your complete full name" required>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Cohabitation Details</h2>
                
                <div class="form-group">
                    <label for="partnersince">Partner Since <span class="required">*</span></label>
                    <input type="date" id="partnersince" name="partnersince" value="<?php echo htmlspecialchars($partnersince); ?>" required>
                    <span class="text-small">Date when you started living together with your partner</span>
                </div>
                
                <div class="form-group">
                    <label for="purpose">Purpose of Application <span class="required">*</span></label>
                    <select id="purpose" name="purpose" required>
                        <option value="">Select Purpose</option>
                        <option value="Legal Documentation" <?php echo $purpose === 'Legal Documentation' ? 'selected' : ''; ?>>Legal Documentation</option>
                        <option value="Government Requirements" <?php echo $purpose === 'Government Requirements' ? 'selected' : ''; ?>>Government Requirements</option>
                        <option value="Employment Requirements" <?php echo $purpose === 'Employment Requirements' ? 'selected' : ''; ?>>Employment Requirements</option>
                        <option value="Housing Application" <?php echo $purpose === 'Housing Application' ? 'selected' : ''; ?>>Housing Application</option>
                        <option value="Bank Requirements" <?php echo $purpose === 'Bank Requirements' ? 'selected' : ''; ?>>Bank Requirements</option>
                        <option value="Insurance Purposes" <?php echo $purpose === 'Insurance Purposes' ? 'selected' : ''; ?>>Insurance Purposes</option>
                        <option value="Other" <?php echo $purpose === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <span class="text-small">Select the main purpose for requesting this cohabitation form</span>
                </div>
            </div>

            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('cohabitation'); ?>
            
            <div class="form-group" style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn" id="submitBtn"><?php echo $isUpdateMode ? 'Update Application' : 'Submit Application'; ?></button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const form = document.getElementById('cohabitationForm');
            const submitBtn = document.getElementById('submitBtn');

            function validateForm() {
                let isValid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                    }
                });

                // Check if terms are agreed
                const agreeTerms = document.querySelector('input[name="agreeTerms"]');
                if (agreeTerms && !agreeTerms.checked) {
                    isValid = false;
                }

                // Additional validation for date field
                const partnerSince = document.getElementById('partnersince').value;
                if (partnerSince) {
                    const today = new Date().toISOString().split('T')[0];
                    if (partnerSince > today) {
                        isValid = false;
                    }
                }

                submitBtn.disabled = !isValid;
            }

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Initialize
            validateForm();

            // Set maximum date for partner since field to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('partnersince').max = today;

            // Show success message if submission was successful
            <?php if ($success): ?>
                document.getElementById('refNo').textContent = '<?php echo $success_ref_no; ?>';
                <?php if (isset($isUpdateSuccess) && $isUpdateSuccess): ?>
                    document.querySelector('.success-message h3').textContent = 'Request Updated Successfully!';
                    document.querySelector('.success-message p:nth-of-type(1)').textContent = 'Your cohabitation certificate request has been updated.';
                <?php endif; ?>
                successMessage.classList.add('show');
                overlay.classList.add('show');
            <?php endif; ?>

            // Close success message
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