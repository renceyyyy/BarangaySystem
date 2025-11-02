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

$user_sql = "SELECT Firstname, Lastname FROM userloginfo WHERE UserID = ?";
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
        
        // Check for default values and replace them with empty strings
        if ($firstname === 'uncompleted') $firstname = '';
        if ($lastname === 'uncompleted') $lastname = '';
    }
    $user_stmt->close();
}

// Handle complaint request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complaint_request"])) {
    // Validate terms and conditions agreement
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        $errors[] = "You must agree to the terms and conditions to proceed.";
    }
    
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $errors = [];
    $success = false;
    $success_ref_no = '';

    // Validate required fields
    $required = [
        'firstname' => 'First Name',
        'lastname' => 'Last Name', 
        'complain' => 'Complaint Details'
    ];
    
    foreach ($required as $field => $fieldName) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = "$fieldName is required!";
        }
    }

    // Process file upload as MEDIUMBLOB
    $evidencePicData = null;
    if (isset($_FILES['evidence_image']) && $_FILES['evidence_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['evidence_image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed";
        }

        // Validate file size (5MB limit)
        if ($_FILES['evidence_image']['size'] > 5242880) {
            $errors[] = "File size must be less than 5MB";
        }

        // Read the file content
        if (empty($errors)) {
            $evidencePicData = file_get_contents($_FILES['evidence_image']['tmp_name']);
        }
    }

    // If no errors, process the complaint
    if (empty($errors)) {
        // Generate reference number
        $refno = (int)(date('Ymd') . rand(1000, 9999));

        // Get user ID
        $userId = $_SESSION['user_id'];

        // Sanitize input data
        $firstname = mysqli_real_escape_string($conn, trim($_POST['firstname']));
        $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
        $complain = mysqli_real_escape_string($conn, trim($_POST['complain']));

        // Insert into database
        $sql = "INSERT INTO complaintbl (Firstname, Lastname, Complain, Evidencepic, refno, Userid) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $errors[] = "Database error: " . $conn->error;
        } else {
            // Bind parameters
            $null = null;
            if ($evidencePicData !== null) {
                $stmt->bind_param("sssbii", 
                    $firstname,
                    $lastname,
                    $complain,
                    $null,
                    $refno,
                    $userId
                );
                $stmt->send_long_data(3, $evidencePicData); // Parameter index 3 is Evidencepic
            } else {
                $stmt->bind_param("ssssii", 
                    $firstname,
                    $lastname,
                    $complain,
                    $null,
                    $refno,
                    $userId
                );
            }

            if ($stmt->execute()) {
                $success = true;
                $success_ref_no = $refno;
                
                // Reset form but keep user data
                $complain = '';
            } else {
                $errors[] = "Error submitting complaint: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File a Complaint</title>
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
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 150px;
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
        .file-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .complain-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .category-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .category-item input[type="radio"] {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="success-message" id="successMessage">
        <h3>Complaint Submitted Successfully!</h3>
        <p>Your complaint has been received and will be reviewed.</p>
        <p>Reference Number: <strong id="refNo"></strong></p>
        <p>Please keep this reference number for tracking your complaint.</p>
        <button id="closeSuccessMessage">OK</button>
    </div>
    
    <div class="container">
        <h1>Complain</h1>
        
        <div class="user-info-note">
            <strong>Note:</strong> Your personal information has been pre-filled from your profile. Please review and update if necessary.
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
        
        <form method="POST" action="" enctype="multipart/form-data" id="complaintForm">
            <input type="hidden" name="complaint_request" value="1">
            
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="firstname">First Name <span class="required">*</span></label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Complaint Details</h2>
                
                <div class="form-group">
                    <label for="complain">Complaint Details <span class="required">*</span></label>
                    <textarea id="complain" name="complain" placeholder="Please provide detailed information about your complaint..." required><?php echo htmlspecialchars($complain ?? ''); ?></textarea>
                    <div class="file-info">Be specific about the issue, include dates, times, and any relevant details</div>
                </div>
                
                <div class="form-group">
                    <label for="evidence_image">Evidence (Optional)</label>
                    <input type="file" id="evidence_image" name="evidence_image" class="file-input" accept=".jpg,.jpeg,.png,.gif">
                    <div class="file-info">Upload supporting evidence (JPG, PNG, GIF - Max: 5MB)</div>
                    <img id="evidence_preview" class="file-preview" alt="Evidence preview">
                </div>
            </div>
            
            <!-- Terms and Conditions Section -->
            <?php echo displayTermsAndConditions('complainForm'); ?>
            
            <div class="form-group">
                <button type="submit" class="btn" id="submitBtn">Submit Complaint</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const overlay = document.getElementById('overlay');
            const closeSuccessMessage = document.getElementById('closeSuccessMessage');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('complaintForm');
            const evidenceInput = document.getElementById('evidence_image');
            const evidencePreview = document.getElementById('evidence_preview');

            function validateForm() {
                let isValid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                    }
                });
                
                submitBtn.disabled = !isValid;
            }

            // File preview for evidence
            evidenceInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            evidencePreview.src = e.target.result;
                            evidencePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        evidencePreview.style.display = 'none';
                    }
                } else {
                    evidencePreview.style.display = 'none';
                }
            });

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