<?php
require_once '../Process/db_connection.php';
require_once './Terms&Conditions/Terms&Conditons.php';
session_start();
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

// Fetch user data from database
$user_id = $_SESSION['user_id'];
$user_data = [];

$user_sql = "SELECT Firstname, Lastname, Gender, ContactNo, Address, CivilStatus FROM userloginfo WHERE UserID = ?";
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
    }
    $user_stmt->close();
}

// Handle document request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["doc_request"])) {
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

    // Handle file upload
    $certificateImage = null;
    $file_upload_error = '';
    if (isset($_FILES['certificateImage'])) {
        if ($_FILES['certificateImage']['error'] === UPLOAD_ERR_OK) {
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
        } else {
            $errors[] = "Certificate proof image is required.";
        }
    } else {
        $errors[] = "Certificate proof image is required.";
    }

    // If no errors, process the request
    if (empty($errors)) {
        // Generate reference number
        $refno = date('Ymd') . rand(1000, 9999);
        $successCount = 0;

        // Start transaction
        $conn->begin_transaction();

        try {
            foreach ($doctypes as $doctype) {
                $doctype = trim($doctype);

                $sql = "INSERT INTO docsreqtbl (
                    Userid, DocuType, Firstname, Lastname,
                    Gender, ContactNO, ReqPurpose, Address, refno, CertificateImage, 
                    YearsOfResidency, CivilStatus, DateRequested
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $null = null;
                $stmt->bind_param(
                    "issssssssbis",
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
                    $civilStatus
                );

                // Send long blob data
                $stmt->send_long_data(9, $certificateImage);

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

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
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Failed to request documents: " . $e->getMessage();
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
        <h1>Government Document Request Form</h1>

        <div class="user-info-note">
            <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and
            update if necessary.
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
                            <option value="Male" <?php echo (isset($gender) && $gender === 'Male') ? 'selected' : ''; ?>>Male</option>
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
                    <label for="yearsOfResidency">Years of Residency in this Barangay <span class="required">*</span></label>
                    <input type="number" id="yearsOfResidency" name="yearsOfResidency" 
                        value="<?php echo htmlspecialchars($yearsOfResidency ?? ''); ?>" 
                        min="0" max="100" step="1" 
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

                <div class="form-group">
                    <label>Select Document Type(s) <span class="required">*</span></label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="cedula" name="doctype[]" value="Cedula" <?php echo (isset($doctypes) && in_array('Cedula', $doctypes)) ? 'checked' : ''; ?>>
                            <label for="cedula">Cedula</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="barangay_certificate" name="doctype[]"
                                value="Barangay Certificate" <?php echo (isset($doctypes) && in_array('Barangay Certificate', $doctypes)) ? 'checked' : ''; ?>>
                            <label for="barangay_certificate">Barangay Certificate</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="employment_form" name="doctype[]" value="Employment Form" <?php echo (isset($doctypes) && in_array('Employment Form', $doctypes)) ? 'checked' : ''; ?>>
                            <label for="employment_form">Employment Form</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="first_time_job_seeker" name="doctype[]"
                                value="First Time Job Seeker" <?php echo (isset($doctypes) && in_array('First Time Job Seeker', $doctypes)) ? 'checked' : ''; ?>>
                            <label for="first_time_job_seeker">First Time Job Seeker</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="indigency_form" name="doctype[]" value="Indigency Form" <?php echo (isset($doctypes) && in_array('Indigency Form', $doctypes)) ? 'checked' : ''; ?>>
                            <label for="indigency_form">Indigency Form</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reqPurpose">Purpose of Request <span class="required">*</span></label>
                    <textarea id="reqPurpose" name="reqPurpose"
                        placeholder="Please specify the purpose for requesting these documents..."
                        required><?php echo htmlspecialchars($reqPurpose ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>Required Proof</h2>

                <div class="form-group">
                    <label for="certificateImage">Certificate Proof Image <span class="required">*</span></label>
                    <input type="file" id="certificateImage" name="certificateImage" class="file-input" required
                        accept=".jpg,.jpeg,.png,.gif,.pdf">
                    <div class="file-info">Upload a valid proof document (JPG, PNG, GIF, PDF - Max: 5MB)</div>
                </div>
            </div>

            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('governmentDocsForm'); ?>

            <div class="form-group">
                <button type="submit" class="btn" id="submitBtn">Submit Request</button>
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

            function validateForm() {
                let isValid = true;

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

                // Validate years of residency is a number
                const yearsOfResidency = document.getElementById('yearsOfResidency');
                if (yearsOfResidency.value && isNaN(yearsOfResidency.value)) {
                    isValid = false;
                }

                submitBtn.disabled = !isValid;
            }

            // Real-time form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);

            // Initialize
            validateForm();

            // Show success message if submission was successful
            <?php if (isset($success) && $success): ?>
                document.getElementById('refNo').textContent = '<?php echo $success_ref_no; ?>';
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