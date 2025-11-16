<?php
// Initialize session based on role (check if staff session exists first)
if (session_status() === PHP_SESSION_NONE) {
    // Try staff session first, then resident session, then default
    if (isset($_COOKIE['BarangayStaffSession'])) {
        session_name('BarangayStaffSession');
    } elseif (isset($_COOKIE['BarangayResidentSession'])) {
        session_name('BarangayResidentSession');
    }
    session_start();
}
// Include database connection module
require_once 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in (for actions that require authentication)
if (isset($_POST["doc_request"])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login.php");
        exit();
    }
}

// Handle sign up request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["signup_request"])) {
    // Store old form data in case of error
    $_SESSION['old_data'] = $_POST;

    // Validate and sanitize input
    $required_fields = [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'middle_name' => 'Middle name',
        'contact_no' => 'Contact number',
        'birthday' => 'Birthday',
        'gender' => 'Gender',
        'address' => 'Address',
        'birthplace' => 'Birthplace',
        'civil_status' => 'Civil status',
        'nationality' => 'Nationality'
    ];

    $errors = [];

    foreach ($required_fields as $field => $name) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = "$name is required";
        }
    }

    // Calculate age from birthday
    if (!empty($_POST['birthday'])) {
        $birthday = new DateTime($_POST['birthday']);
        $today = new DateTime();
        $age = $today->diff($birthday)->y;
    } else {
        $age = 0; // Will be caught by required field validation
    }

    // Validate email if provided
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Validate contact number (assuming Philippine format)
    if (!empty($_POST['contact_no']) && !preg_match('/^09[0-9]{9}$/', $_POST['contact_no'])) {
        $errors[] = "Contact number must be 11 digits starting with 09";
    }

    // If there are errors, redirect back with messages
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        header("Location: ../Login/Signup.php");
        exit();
    }

    // Prepare data for database insertion
    $conn = getDBConnection();
    $firstname = db_escape(trim($_POST['first_name']));
    $lastname = db_escape(trim($_POST['last_name']));
    $middlename = db_escape(trim($_POST['middle_name']));
    $email = !empty($_POST['email']) ? db_escape(trim($_POST['email'])) : NULL;
    $contactNo = db_escape(trim($_POST['contact_no']));
    $birthday = db_escape(trim($_POST['birthday']));
    $gender = db_escape(trim($_POST['gender']));
    $address = db_escape(trim($_POST['address']));
    $birthplace = db_escape(trim($_POST['birthplace']));
    $civilStat = db_escape(trim($_POST['civil_status']));
    $nationality = db_escape(trim($_POST['nationality']));

    // Check if users table exists, create if not
    $table_check = db_query("SHOW TABLES LIKE 'users'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE users (
            UserId INT AUTO_INCREMENT PRIMARY KEY,
            Firstname VARCHAR(50) NOT NULL,
            Lastname VARCHAR(50) NOT NULL,
            Middlename VARCHAR(15) NOT NULL,
            Email VARCHAR(50),
            ContactNo VARCHAR(15) NOT NULL,
            Birthday DATE NOT NULL,
            Gender VARCHAR(20) NOT NULL,
            Age TINYINT(4) NOT NULL,
            Address VARCHAR(50) NOT NULL,
            Birthplace VARCHAR(50) NOT NULL,
            CivilStat VARCHAR(25) NOT NULL,
            Nationality VARCHAR(50) NOT NULL,
            ProfilePic BLOB,
            DateSignedIn TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
        )";

        if (!db_query($create_table)) {
            $_SESSION['message'] = "Error creating table: " . $conn->error;
            $_SESSION['message_type'] = "error";
            header("Location: ../Login/Signup.php");
            exit();
        }
    }

    // Insert user data
    $sql = "INSERT INTO users (
        Firstname, Lastname, Middlename, Email, ContactNo, Birthday,
        Gender, Age, Address, Birthplace, CivilStat, Nationality
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = db_prepare($sql);

    if ($stmt === false) {
        $_SESSION['message'] = "Prepare failed: " . $conn->error;
        $_SESSION['message_type'] = "error";
        header("Location: ../Login/Signup.php");
        exit();
    }

    $stmt->bind_param(
        "sssssssissss",
        $firstname,
        $lastname,
        $middlename,
        $email,
        $contactNo,
        $birthday,
        $gender,
        $age,
        $address,
        $birthplace,
        $civilStat,
        $nationality
    );

    if ($stmt->execute()) {
        // Store the new user ID for login credentials creation
        $_SESSION['new_user_id'] = $conn->insert_id;

        // Clean up session data on success
        unset($_SESSION['old_data']);

        $_SESSION['message'] = "Basic registration successful! Please create your login credentials.";
        $_SESSION['message_type'] = "success";
        header("Location: ../Login/login_registration.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
        header("Location: ../Login/Signup.php");
        exit();
    }
}

// Handle document request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["doc_request"])) {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Get document types
    $doctypes = isset($_POST["doctype"]) ? (array) $_POST["doctype"] : [];

    if (empty($doctypes)) {
        echo json_encode(['status' => 'error', 'message' => "Please select at least one document type!"]);
        exit();
    }

    // Prepare and sanitize input fields
    $conn = getDBConnection();
    $firstname = db_escape(trim($_POST["firstname"] ?? ''));
    $lastname = db_escape(trim($_POST["lastname"] ?? ''));
    $gender = db_escape(trim($_POST["gender"] ?? ''));
    $contactNo = db_escape(trim($_POST["contactNo"] ?? ''));
    $address = db_escape(trim($_POST["address"] ?? ''));
    $reqPurpose = db_escape(trim($_POST["reqPurpose"] ?? ''));

    // Validate required fields
    $required = [
        'First Name' => $firstname,
        'Last Name' => $lastname,
        'Gender' => $gender,
        'Contact Number' => $contactNo,
        'Address' => $address,
        'Purpose' => $reqPurpose
    ];

    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        echo json_encode(['status' => 'error', 'message' => "Missing required fields: " . implode(", ", $missing)]);
        exit();
    }

    // Handle file upload
    $certificateImage = null;
    if (isset($_FILES['certificateImage'])) {
        if ($_FILES['certificateImage']['error'] === UPLOAD_ERR_OK) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $fileType = $_FILES['certificateImage']['type'];

            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => "Invalid file type. Only JPEG, PNG, GIF, and PDF are allowed."]);
                exit();
            }

            // Read file content
            $certificateImage = file_get_contents($_FILES['certificateImage']['tmp_name']);
            $certificateImage = db_escape($certificateImage);
        } else {
            echo json_encode(['status' => 'error', 'message' => "File upload error: " . $_FILES['certificateImage']['error']]);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => "Certificate proof image is required."]);
        exit();
    }

    // Generate reference number
    $refno = date('Ymd') . rand(1000, 9999);
    $successCount = 0;

    // Start transaction
    $conn->begin_transaction();

    try {
        foreach ($doctypes as $doctype) {
            $doctype = db_escape(trim($doctype));

            $sql = "INSERT INTO docsreqtbl (
                Userid, DocuType, Firstname, Lastname,
                Gender, ContactNO, ReqPurpose, Address, refno, CertificateImage, DateRequested
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = db_prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $null = null;
            $stmt->bind_param(
                "issssssssb",
                $userId,
                $doctype,
                $firstname,
                $lastname,
                $gender,
                $contactNo,
                $reqPurpose,
                $address,
                $refno,
                $null
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
            $_SESSION['message'] = "Successfully requested $successCount document(s)! Your reference number is: $refno";
            $_SESSION['message_type'] = "success";
            $_SESSION['ref_no'] = $refno;

            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'ref_no' => $refno]);
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Failed to request documents: ' . $e->getMessage()]);
        exit();
    }
}

// Handle complaint request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complaint_request"])) {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Validate required fields
    $required = ['firstname', 'lastname', 'complain'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['message'] = "All fields are required!";
            $_SESSION['message_type'] = "error";
            header("Location: ../Pages/landingpage.php");
            exit();
        }
    }

    // Process file upload as MEDIUMBLOB
    $evidencePicData = null;
    if (isset($_FILES['evidence_image']) && $_FILES['evidence_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['evidence_image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['message'] = "Only JPG, PNG, and GIF files are allowed";
            $_SESSION['message_type'] = "error";
            header("Location: ../Pages/landingpage.php");
            exit();
        }

        // Read the file content
        $evidencePicData = file_get_contents($_FILES['evidence_image']['tmp_name']);
    }

    // Generate reference number
    $refno = (int)(date('Ymd') . rand(1000, 9999));

    // Get user ID if logged in
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Get database connection
    $conn = getDBConnection();
    if (!$conn) {
        $_SESSION['message'] = "Database connection failed!";
        $_SESSION['message_type'] = "error";
        header("Location: ../Pages/landingpage.php");
        exit();
    }

    // Insert into database - include all required fields
    $sql = "INSERT INTO complaintbl (Firstname, Lastname, Complain, Evidencepic, refno, Userid) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['message'] = "Database error: " . $conn->error;
        $_SESSION['message_type'] = "error";
        error_log("Prepare error: " . $conn->error);
        header("Location: ../Pages/landingpage.php");
        exit();
    }

    // Bind parameters - Evidencepic is parameter 4 (index 3)
    $null = null;
    if ($evidencePicData !== null) {
        $stmt->bind_param(
            "sssbii",
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['complain'],
            $null,
            $refno,
            $userId
        );
        $stmt->send_long_data(3, $evidencePicData); // Parameter index 3 is Evidencepic
    } else {
        $stmt->bind_param(
            "ssssii",
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['complain'],
            $null,
            $refno,
            $userId
        );
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Complaint submitted successfully! Reference: " . $refno;
        $_SESSION['message_type'] = "success";
        error_log("Complaint inserted successfully. Ref: " . $refno);
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
        error_log("Database execute error: " . $stmt->error);
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r([$_POST['firstname'], $_POST['lastname'], $_POST['complain'], $refno, $userId], true));
    }

    $stmt->close();
    header("Location: ../Pages/landingpage.php");
    exit();
}

// Handle business permit/closure request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["business_request"])) {
    // Enable detailed error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Initialize response
    $response = ['status' => 'error', 'message' => ''];

    try {
        // Generate reference number
        $refno = date('Ymd') . rand(1000, 9999);
        $successCount = 0;

        // Get database connection with error handling
        $conn = getDBConnection();
        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
        }

        // Validate and sanitize inputs
        $requiredFields = [
            'RequestType' => ['type' => 'string', 'name' => 'Request type'],
            'BusinessName' => ['type' => 'string', 'name' => 'Business name'],
            'BusinessLoc' => ['type' => 'string', 'name' => 'Business location'],
            'Purpose' => ['type' => 'string', 'name' => 'Purpose'],
            'OwnerName' => ['type' => 'string', 'name' => 'Owner name'],
            'OwnerContact' => ['type' => 'string', 'name' => 'Owner contact']
        ];

        $data = [];
        $errors = [];

        foreach ($requiredFields as $field => $info) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[] = "{$info['name']} is required";
            } else {
                $data[$field] = $conn->real_escape_string(trim($_POST[$field]));
            }
        }

        // Get UserId from session (assuming user is logged in)
        if (!isset($_SESSION['user_id'])) {
            $errors[] = "User not authenticated. Please log in.";
        } else {
            $data['UserId'] = $_SESSION['user_id'];
        }

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
        if (empty($_FILES['businessProof']['tmp_name'])) {
            $errors[] = "Proof document is required";
        } else {
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
        }

        // Return errors if any
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Prepare SQL query
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
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $response['status'] = 'success';
        $response['message'] = "Business request submitted successfully!";
        $response['refno'] = $refno;
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $response['message'] = $e->getMessage();

        // For debugging - don't show full errors in production
        if (strpos($e->getMessage(), 'Database') !== false) {
            $response['message'] = "Failed to save business request. Please try again.";
        }
    } finally {
        if (isset($stmt)) $stmt->close();
    }

    // Return response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle scholar application request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["scholar_request"])) {
    // Initialize response array
    $response = ['status' => 'error', 'message' => ''];

    try {
        $conn = getDBConnection();
        $userId = null;

        // Check if this is an admin or staff submission (walk-in application)
        // Login sets: $_SESSION['user_id'], $_SESSION['role'] (admin/finance/sk/SuperAdmin)
        $isAdminSubmission = isset($_SESSION['user_id']) && isset($_SESSION['role'])
            && in_array($_SESSION['role'], ['admin', 'finance', 'sk', 'SuperAdmin']);

        if ($isAdminSubmission) {
            // Admin/Staff submitting walk-in application - create user record from form data
            error_log("Admin/Staff walk-in submission detected");

            // Extract user data from form
            $firstname = trim($_POST['firstname'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contactNo = trim($_POST['contact_no'] ?? '');
            $address = trim($_POST['address'] ?? '');

            // Validate required fields
            if (empty($firstname) || empty($lastname) || empty($email) || empty($contactNo) || empty($address)) {
                $response['message'] = "All applicant information fields are required";
                sendResponse($response);
            }

            // Check if user already exists by email
            $checkExisting = $conn->prepare("SELECT UserId FROM users WHERE Email = ?");
            $checkExisting->bind_param("s", $email);
            $checkExisting->execute();
            $existingResult = $checkExisting->get_result();

            if ($existingResult->num_rows > 0) {
                // User exists, use their ID
                $userId = $existingResult->fetch_assoc()['UserId'];
                error_log("Found existing user with ID: $userId");
            } else {
                // Create new user record
                $insertUser = $conn->prepare("INSERT INTO users (Firstname, Lastname, Middlename, Email, ContactNo, Birthday, Gender, Age, Address, Birthplace, CivilStat, Nationality) VALUES (?, ?, '', ?, ?, '2000-01-01', 'Not Specified', 0, ?, '', 'Single', 'Filipino')");
                $insertUser->bind_param("sssss", $firstname, $lastname, $email, $contactNo, $address);

                if ($insertUser->execute()) {
                    $userId = $insertUser->insert_id;
                    error_log("Created new user record with ID: $userId");
                } else {
                    error_log("Failed to create user record: " . $insertUser->error);
                    $response['message'] = "Failed to create user record";
                    sendResponse($response);
                }
                $insertUser->close();
            }
            $checkExisting->close();
        } else {
            // Regular user submission - require login
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = "Please log in to apply for scholarship";
                sendResponse($response);
            }
            $userId = $_SESSION['user_id'];
        }

        // Debug: Log received data
        error_log("Form data received: " . print_r($_POST, true));
        error_log("Files received: " . print_r(array_keys($_FILES), true));

        // Validate input fields
        $errors = validateInput();
        if (!empty($errors)) {
            $response['message'] = implode("<br>", $errors);
            sendResponse($response);
        }

        // Process file uploads
        $fileResult = processFileUploads();
        if (isset($fileResult['error'])) {
            $response['message'] = implode("<br>", $fileResult['error']);
            sendResponse($response);
        }
        $blobs = $fileResult;

        // Sanitize user input
        $data = sanitizeInput();

        // Debug: Log sanitized data
        error_log("Sanitized data: " . print_r($data, true));

        // Validate reason based on type
        $reasonType = $_POST['reason_type'] ?? 'text';
        error_log("Reason type: " . $reasonType);

        if ($reasonType === 'text') {
            // Validate text reason is not empty
            if (empty(trim($data['reason']))) {
                $response['message'] = "Reason for applying is required";
                sendResponse($response);
            }
        } elseif ($reasonType === 'file') {
            // Validate that reason_file was uploaded
            if (!isset($blobs['reason_file']) || empty($blobs['reason_file'])) {
                $response['message'] = "Handwritten reason document is required";
                sendResponse($response);
            }
            // Reason is already set to placeholder in sanitizeInput()
        }

        // Save to database
        $dbResult = saveApplication($userId, $data, $blobs);
        if ($dbResult['status'] === 'error') {
            $response['message'] = $dbResult['message'];
            sendResponse($response);
        }

        // Return success response
        $response = [
            'status' => 'success',
            'message' => "Scholarship application submitted successfully!",
            'reference_id' => $dbResult['insert_id']
        ];
        sendResponse($response);
    } catch (Exception $e) {
        error_log("Exception in scholarship application: " . $e->getMessage());
        $response['message'] = "System error: " . $e->getMessage();
        sendResponse($response);
    }
}

function validateInput()
{
    $errors = [];
    $required = [
        'firstname' => 'First name',
        'lastname' => 'Last name',
        'email' => 'Email',
        'contact_no' => 'Contact number',
        'address' => 'Address',
        'education_level' => 'Education level'
        // Note: 'reason' is NOT in required list because it's validated separately based on reason_type
    ];

    foreach ($required as $field => $name) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = "$name is required";
        }
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    return $errors;
}

function processFileUploads()
{
    $required_files = [
        'school_id' => 'School ID',
        'barangay_id' => 'Barangay ID',
        'cor' => 'Certificate of Registration',
        'parents_id' => 'Parents ID',
        'birth_certificate' => 'Birth Certificate'
    ];

    $blobs = [];
    $errors = [];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

    // Process required files
    foreach ($required_files as $field => $label) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $upload_error = isset($_FILES[$field]) ? $_FILES[$field]['error'] : 'File not uploaded';
            error_log("File upload error for $field: $upload_error");
            $errors[] = "$label is required (Error: $upload_error)";
            continue;
        }

        if ($_FILES[$field]['size'] > 5242880) { // 5MB limit
            $errors[] = "$label must be smaller than 5MB";
            continue;
        }

        $tmp_name = $_FILES[$field]['tmp_name'];

        // Check if file exists
        if (!file_exists($tmp_name)) {
            $errors[] = "Failed to access $label file";
            continue;
        }

        $mime = mime_content_type($tmp_name);
        error_log("File $field MIME type: $mime");

        if (!in_array($mime, $allowed_mime_types)) {
            $errors[] = "$label must be a JPG, PNG, GIF, or PDF (detected: $mime)";
            continue;
        }

        $content = file_get_contents($tmp_name);
        if ($content === false) {
            $errors[] = "Failed to read $label file";
            continue;
        }

        $blobs[$field] = $content;
        error_log("Successfully processed file: $field (size: " . strlen($content) . " bytes)");
    }

    // Process optional reason_file if uploaded
    if (isset($_FILES['reason_file']) && $_FILES['reason_file']['error'] === UPLOAD_ERR_OK) {
        error_log("Processing reason_file upload");

        if ($_FILES['reason_file']['size'] > 5242880) { // 5MB limit
            $errors[] = "Reason document must be smaller than 5MB";
        } else {
            $tmp_name = $_FILES['reason_file']['tmp_name'];

            if (file_exists($tmp_name)) {
                $mime = mime_content_type($tmp_name);
                error_log("Reason file MIME type: $mime");

                if (in_array($mime, $allowed_mime_types)) {
                    $content = file_get_contents($tmp_name);
                    if ($content !== false) {
                        $blobs['reason_file'] = $content;
                        error_log("Successfully processed reason_file (size: " . strlen($content) . " bytes)");
                    } else {
                        $errors[] = "Failed to read reason document";
                    }
                } else {
                    $errors[] = "Reason document must be a JPG, PNG, GIF, or PDF";
                }
            } else {
                $errors[] = "Failed to access reason document file";
            }
        }
    }

    if (!empty($errors)) {
        return ['error' => $errors];
    }

    return $blobs;
}

function sanitizeInput()
{
    $conn = getDBConnection();
    $reasonType = $_POST['reason_type'] ?? 'text';

    // Handle reason based on type
    $reason = '';
    if ($reasonType === 'file') {
        // If file upload, set a placeholder - actual file content will be in blobs
        $reason = '[Handwritten document uploaded]';
    } else {
        // If text, get the actual text content
        $reason = db_escape(trim($_POST['reason'] ?? ''));
    }

    return [
        'firstname' => db_escape(trim($_POST['firstname'])),
        'lastname' => db_escape(trim($_POST['lastname'])),
        'email' => filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
        'contactNo' => preg_replace('/[^0-9]/', '', trim($_POST['contact_no'])),
        'address' => db_escape(trim($_POST['address'])),
        'reason' => $reason,
        'education_level' => db_escape(trim($_POST['education_level'] ?? ''))
    ];
}

function saveApplication($userId, $data, $blobs)
{
    $conn = getDBConnection();

    try {
        // Check if connection exists
        if (!$conn) {
            throw new Exception("Database connection not available");
        }

        // Check if ReasonFile column exists, if not create it
        $checkCol = $conn->query("SHOW COLUMNS FROM scholarship LIKE 'ReasonFile'");
        if ($checkCol && $checkCol->num_rows === 0) {
            error_log("ReasonFile column doesn't exist, creating it...");
            $conn->query("ALTER TABLE scholarship ADD COLUMN ReasonFile LONGBLOB NULL AFTER Reason");
        }

        // Check if reason_file blob exists
        $hasReasonFile = isset($blobs['reason_file']) && !empty($blobs['reason_file']);

        if ($hasReasonFile) {
            $sql = "INSERT INTO scholarship
                    (UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, EducationLevel, ReasonFile,
                     SchoolID, BaranggayID, COR, ParentsID, BirthCertificate, RequestStatus, DateApplied)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        } else {
            $sql = "INSERT INTO scholarship
                    (UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, EducationLevel,
                     SchoolID, BaranggayID, COR, ParentsID, BirthCertificate, RequestStatus, DateApplied)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        }

        error_log("SQL Query: " . $sql);

        $stmt = db_prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        // Log data being bound
        error_log("Binding data - UserID: $userId, Name: {$data['firstname']} {$data['lastname']}, Education: {$data['education_level']}, Has ReasonFile: " . ($hasReasonFile ? 'YES' : 'NO'));

        if ($hasReasonFile) {
            $stmt->bind_param(
                "isssssssssssss",
                $userId,
                $data['firstname'],
                $data['lastname'],
                $data['email'],
                $data['contactNo'],
                $data['address'],
                $data['reason'],
                $data['education_level'],
                $blobs['reason_file'],
                $blobs['school_id'],
                $blobs['barangay_id'],
                $blobs['cor'],
                $blobs['parents_id'],
                $blobs['birth_certificate']
            );
        } else {
            $stmt->bind_param(
                "issssssssssss",
                $userId,
                $data['firstname'],
                $data['lastname'],
                $data['email'],
                $data['contactNo'],
                $data['address'],
                $data['reason'],
                $data['education_level'],
                $blobs['school_id'],
                $blobs['barangay_id'],
                $blobs['cor'],
                $blobs['parents_id'],
                $blobs['birth_certificate']
            );
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $insert_id = $stmt->insert_id;
        error_log("Successfully inserted application with ID: $insert_id");

        return ['status' => 'success', 'insert_id' => $insert_id];
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

function sendResponse($response)
{
    // Always send JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle unemployment certificate request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["unemployment_request"])) {
    // Validate input
    $errors = [];

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
    if (empty($_POST["agreeTerms"] ?? ''))
        $errors[] = "You must agree to the terms and conditions";

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'status' => 'error',
            'message' => implode("\n", $errors)
        ]);
        exit();
    }

    // Generate reference number
    $refno = date('Ymd') . rand(1000, 9999);
    $successCount = 0;

    try {
        // Check if table exists, create if not
        $conn = getDBConnection();
        $table_check = db_query("SHOW TABLES LIKE 'unemploymenttbl'");
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

            if (!db_query($create_table)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }

        // Get user ID if logged in
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Insert into database
        $sql = "INSERT INTO unemploymenttbl (
            refno, certificate_type, fullname, age, address,
            unemployed_since, no_fixed_income_since, purpose,
            request_date, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = db_prepare($sql);

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
            echo json_encode([
                'status' => 'success',
                'refno' => $refno
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit();
}

// Handle guardianship/solo parent request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardianship_request"])) {
    // Validate input
    $errors = [];

    $requestType = trim($_POST["requestType"] ?? '');
    $childName = trim($_POST["childName"] ?? '');
    $childAge = trim($_POST["childAge"] ?? '');
    $childAddress = trim($_POST["childAddress"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');
    $applicantName = trim($_POST["applicantName"] ?? '');
    $applicantRelationship = trim($_POST["applicantRelationship"] ?? '');
    $guardianshipSince = ($requestType === 'Guardianship') ? trim($_POST["guardianshipSince"] ?? '') : null;
    $soloParentSince = ($requestType === 'Solo Parent') ? trim($_POST["soloParentSince"] ?? '') : null;

    // Basic validation
    if (empty($requestType))
        $errors[] = "Request type is required";
    if (empty($childName))
        $errors[] = "Child's name is required";
    if (empty($childAge) || !is_numeric($childAge) || $childAge < 0 || $childAge > 18)
        $errors[] = "Valid child age (0-18) is required";
    if (empty($childAddress))
        $errors[] = "Child's address is required";
    if (empty($purpose))
        $errors[] = "Purpose is required";
    if (empty($applicantName))
        $errors[] = "Applicant's name is required";
    if (empty($applicantRelationship))
        $errors[] = "Relationship to child is required";
    if ($requestType === 'Guardianship' && empty($guardianshipSince))
        $errors[] = "Guardian since date is required";
    if ($requestType === 'Solo Parent' && empty($soloParentSince))
        $errors[] = "Solo parent since date is required";
    if (empty($_POST["agreeTerms"] ?? ''))
        $errors[] = "You must agree to the terms and conditions";

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'status' => 'error',
            'message' => implode("\n", $errors)
        ]);
        exit();
    }

    // Generate reference number
    $refno = date('Ymd') . rand(1000, 9999);

    try {
        // Check if table exists, create if not
        $conn = getDBConnection();
        $table_check = db_query("SHOW TABLES LIKE 'guardianshiptbl'");
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

            if (!db_query($create_table)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }

        // Get user ID if logged in
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Insert into database
        $sql = "INSERT INTO guardianshiptbl (
            refno, request_type, child_name, child_age, child_address,
            guardianship_since, solo_parent_since, purpose,
            applicant_name, applicant_relationship, request_date, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = db_prepare($sql);

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
            echo json_encode([
                'status' => 'success',
                'ref_no' => $refno
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit();
}

// Handle no birth certificate request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["no_birthcert_request"])) {
    // Validate input
    $errors = [];

    $requestorName = trim($_POST["requestorName"] ?? '');
    $requestorBirthday = trim($_POST["requestorBirthday"] ?? '');
    $requestorAddress = trim($_POST["requestorAddress"] ?? '');
    $purpose = trim($_POST["purpose"] ?? '');

    // Basic validation
    if (empty($requestorName))
        $errors[] = "Requestor's name is required";
    if (empty($requestorBirthday))
        $errors[] = "Requestor's birthday is required";
    if (empty($requestorAddress))
        $errors[] = "Requestor's address is required";
    if (empty($purpose))
        $errors[] = "Purpose is required";
    if (empty($_POST["agreeTerms"] ?? ''))
        $errors[] = "You must agree to the terms and conditions";

    // Validate birthday format
    if (!empty($requestorBirthday)) {
        $date = DateTime::createFromFormat('Y-m-d', $requestorBirthday);
        if (!$date || $date->format('Y-m-d') !== $requestorBirthday) {
            $errors[] = "Invalid birthday format (YYYY-MM-DD)";
        }
    }

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'status' => 'error',
            'message' => implode("\n", $errors)
        ]);
        exit();
    }

    // Generate reference number
    $refno = date('Ymd') . rand(1000, 9999);

    try {
        // Check if table exists, create if not
        $conn = getDBConnection();
        $table_check = db_query("SHOW TABLES LIKE 'no_birthcert_tbl'");
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

            if (!db_query($create_table)) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }

        // Get user ID if logged in
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Insert into database
        $sql = "INSERT INTO no_birthcert_tbl (
            refno, requestor_name, requestor_birthday,
            requestor_address, purpose, request_date, user_id
        ) VALUES (?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = db_prepare($sql);

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
            echo json_encode([
                'status' => 'success',
                'ref_no' => $refno
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit();
}
