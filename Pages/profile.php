<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize resident session
require_once __DIR__ . '/../config/session_resident.php';

require_once '../Process/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Handle AJAX request to dismiss verified notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dismiss_verified_notice') {
  $_SESSION['verified_notice_dismissed'] = true;
  echo json_encode(['success' => true]);
  exit();
}

require_once '../Process/db_connection.php';
$conn = getDBConnection();

// Function to calculate age
function calculateAge($birthdate)
{
  if (empty($birthdate))
    return 0;
  $today = new DateTime();
  $birthDate = new DateTime($birthdate);
  $age = $today->diff($birthDate);
  return $age->y;
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload_profile_pic"])) {
  if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['profile_pic']['type'];

    if (in_array($file_type, $allowed_types) && $_FILES['profile_pic']['size'] <= 5 * 1024 * 1024) {
      $upload_dir = "../uploads/profile_pics/";
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }

      $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
      $file_name = "profile_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
      $file_path = $upload_dir . $file_name;

      if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
        $relative_path = "uploads/profile_pics/" . $file_name;

        $sql = "UPDATE userloginfo SET ProfilePic = ? WHERE UserId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $relative_path, $_SESSION['user_id']);

        if ($stmt->execute()) {
          $_SESSION['profile_pic'] = $relative_path;
          $_SESSION['profile_message'] = "Profile picture updated successfully!";
          $_SESSION['profile_message_type'] = "success";
        } else {
          $_SESSION['profile_message'] = "Error updating profile picture.";
          $_SESSION['profile_message_type'] = "error";
        }
        $stmt->close();
      }
    }
  }
  header("Location: ../Pages/profile.php");
  exit();
}

// Handle profile update with age validation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
  // Get current user data
  $sql = "SELECT * FROM userloginfo WHERE UserId = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $current_user = $result->fetch_assoc();
  $stmt->close();

  // Check if profile is already completed
  if (
    $current_user &&
    $current_user['Firstname'] !== 'uncompleted' &&
    $current_user['Lastname'] !== 'uncompleted' &&
    $current_user['Gender'] !== 'uncompleted' &&
    $current_user['AccountStatus'] !== 'unverified'
  ) {
    $_SESSION['profile_message'] = "Profile already completed and locked.";
    $_SESSION['profile_message_type'] = "error";
    header("Location: ../Pages/profile.php");
    exit();
  }

  // Get and validate form data
  $firstname = trim($_POST['firstname'] ?? '');
  $lastname = trim($_POST['lastname'] ?? '');
  $middlename = trim($_POST['middlename'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $contactno = trim($_POST['contactno'] ?? '');
  $birthdate = $_POST['Birthdate'] ?? '';
  $gender = $_POST['gender'] ?? '';
  $address = trim($_POST['address'] ?? '');
  $birthplace = trim($_POST['birthplace'] ?? '');
  $civilstatus = $_POST['civilstatus'] ?? '';
  $nationality = trim($_POST['nationality'] ?? '');

  // Basic validation - Enhanced
  $errors = [];

  // Required fields validation
  if (empty($firstname)) {
    $errors[] = "First name is required.";
  }
  if (empty($lastname)) {
    $errors[] = "Last name is required.";
  }
  if (empty($gender)) {
    $errors[] = "Gender is required.";
  }
  if (empty($birthdate)) {
    $errors[] = "Birthdate is required.";
  } else {
    // Age validation - must be 18 or older
    $calculated_age = calculateAge($birthdate);
    if ($calculated_age < 18) {
      $errors[] = "You must be at least 18 years old to complete your profile.";
    }
  }
  
  // Additional required fields for profile completion
  if (empty($email)) {
    $errors[] = "Email address is required.";
  }
  if (empty($contactno)) {
    $errors[] = "Contact number is required.";
  }
  if (empty($address)) {
    $errors[] = "Address is required.";
  }
  if (empty($birthplace)) {
    $errors[] = "Birthplace is required.";
  }
  if (empty($civilstatus)) {
    $errors[] = "Civil status is required.";
  }
  if (empty($nationality)) {
    $errors[] = "Nationality is required.";
  }
  
  // Email format validation
  if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }
  
  // Contact number validation
  if (!empty($contactno) && !preg_match('/^[0-9]{10,11}$/', $contactno)) {
    $errors[] = "Contact number must be 10-11 digits.";
  }
  
  // Valid ID requirement
  if (!isset($_FILES['valid_id']) || $_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
    // Check if user already has a valid ID uploaded
    if (empty($current_user['ValidID'])) {
      $errors[] = "Valid ID upload is required to complete your profile.";
    }
  }

  if (!empty($errors)) {
    $_SESSION['profile_message'] = implode("<br>", $errors);
    $_SESSION['profile_message_type'] = "error";
    header("Location: ../Pages/profile.php");
    exit();
  }

  // Convert empty strings to NULL for optional fields only (middlename is optional)
  $middlename = empty($middlename) ? NULL : $middlename;

  // Calculate age from birthdate
  $calculated_age = calculateAge($birthdate);

  // Handle Valid ID upload - Process AFTER validation
  $valid_id_path = $current_user['ValidID'] ?? NULL;

  if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] == UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $file_type = $_FILES['valid_id']['type'];
    $file_size = $_FILES['valid_id']['size'];

    // Validate file type and size
    if (!in_array($file_type, $allowed_types)) {
      $_SESSION['profile_message'] = "Valid ID file type not allowed. Please upload JPEG, PNG, GIF, or PDF.";
      $_SESSION['profile_message_type'] = "error";
      header("Location: ../Pages/profile.php");
      exit();
    }
    
    if ($file_size > 5 * 1024 * 1024) {
      $_SESSION['profile_message'] = "Valid ID file size must not exceed 5MB.";
      $_SESSION['profile_message_type'] = "error";
      header("Location: ../Pages/profile.php");
      exit();
    }

    $upload_dir = "../uploads/valid_ids/";
    if (!file_exists($upload_dir)) {
      if (!mkdir($upload_dir, 0777, true)) {
        $_SESSION['profile_message'] = "Error creating upload directory. Please try again.";
        $_SESSION['profile_message_type'] = "error";
        header("Location: ../Pages/profile.php");
        exit();
      }
    }

    $file_extension = pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION);
    $file_name = "valid_id_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $file_path)) {
      $valid_id_path = "uploads/valid_ids/" . $file_name;
    } else {
      $_SESSION['profile_message'] = "Error uploading Valid ID. Please try again.";
      $_SESSION['profile_message_type'] = "error";
      header("Location: ../Pages/profile.php");
      exit();
    }
  }

  // Update the database
  $sql = "UPDATE userloginfo SET 
          Firstname = ?, 
          Lastname = ?, 
          Middlename = ?, 
          Email = ?, 
          ContactNo = ?, 
          Birthdate = ?, 
          Age = ?,
          Gender = ?, 
          Address = ?, 
          Birthplace = ?, 
          CivilStatus = ?, 
          Nationality = ?, 
          ValidID = ?, 
          AccountStatus = 'pending' 
          WHERE UserId = ?";

  $stmt = $conn->prepare($sql);

  if (!$stmt) {
    $_SESSION['profile_message'] = "Database error: " . $conn->error;
    $_SESSION['profile_message_type'] = "error";
    header("Location: ../Pages/profile.php");
    exit();
  }

  $stmt->bind_param(
    "ssssssissssssi",
    $firstname,
    $lastname,
    $middlename,
    $email,
    $contactno,
    $birthdate,
    $calculated_age,
    $gender,
    $address,
    $birthplace,
    $civilstatus,
    $nationality,
    $valid_id_path,
    $_SESSION['user_id']
  );

  if ($stmt->execute()) {
    $_SESSION['profile_message'] = "Profile completed successfully! Your account is now pending verification.";
    $_SESSION['profile_message_type'] = "success";
    $_SESSION['firstname'] = $firstname;
    $_SESSION['lastname'] = $lastname;
  } else {
    $_SESSION['profile_message'] = "Error updating profile: " . $stmt->error;
    $_SESSION['profile_message_type'] = "error";
  }

  $stmt->close();
  header("Location: ../Pages/profile.php");
  exit();
}

// Get user information
$user = [];
$show_complete_button = true;
$show_verification_notice = false;

$sql = "SELECT * FROM userloginfo WHERE UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();

  // Set profile pic in session if available - Always refresh from database
  $_SESSION['profile_pic'] = !empty($user['ProfilePic']) ? $user['ProfilePic'] : '';

  // Clean up 'uncompleted' values for display
  foreach ($user as $key => $value) {
    if ($value === 'uncompleted' || $value === '0') {
      $user[$key] = '';
    }
  }

  // Check if profile is completed
  $is_profile_completed = (
    !empty($user['Firstname']) &&
    !empty($user['Lastname']) &&
    !empty($user['Gender'])
  );

  if ($is_profile_completed) {
    $show_complete_button = false;
  }

  // Check verification status
  if ($user['AccountStatus'] === 'pending') {
    $show_verification_notice = true;
  }
} else {
  // Create new record if doesn't exist
  $insert_sql = "INSERT INTO userloginfo (UserId, Firstname, Lastname, Gender, AccountStatus) VALUES (?, '', '', '', 'unverified')";
  $insert_stmt = $conn->prepare($insert_sql);
  $insert_stmt->bind_param("i", $_SESSION['user_id']);
  $insert_stmt->execute();
  $insert_stmt->close();

  // Fetch the newly created record
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  // Clean up values
  foreach ($user as $key => $value) {
    if ($value === 'uncompleted' || $value === '0') {
      $user[$key] = '';
    }
  }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | Barangay Sampaguita</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Styles/StylesProfile.css">
  <style>
    :root {
      --primary: #5CB25D;
      --primary-dark: #4A9A47;
      --primary-light: #7BC67A;
      --text-dark: #2c3e50;
      --text-muted: #7f8c8d;
      --bg-light: #f8faf9;
      --border-color: #e8ede8;
      --shadow-sm: 0 2px 8px rgba(92, 178, 93, 0.08);
      --shadow-md: 0 4px 16px rgba(92, 178, 93, 0.12);
      --shadow-lg: 0 8px 24px rgba(92, 178, 93, 0.15);
    }

    body {
      background: linear-gradient(135deg, #f8faf9 0%, #ffffff 100%);
      font-family: 'Archivo', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      color: var(--text-dark);
      line-height: 1.6;
      min-height: 100vh;
      padding: 0;
      margin: 0;
    }

    .profile-container {
      max-width: 1100px;
      margin: 2rem auto;
      padding: 0 2rem;
      margin-top: 0;
      padding-top: 80px;
    }

    /* Profile Header - Enhanced */
    .profile-header {
      text-align: center;
      padding: 3rem 0 2.5rem;
      margin-bottom: 2rem;
      position: relative;
    }

    .profile-pic-container {
      position: relative;
      width: 150px;
      height: 150px;
      margin: 0 auto;
      border-radius: 50%;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(92, 178, 93, 0.2);
    }

    .profile-pic {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .profile-name {
      margin-top: 0;
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--text-dark);
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, var(--text-dark) 0%, var(--primary-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Profile Section - Enhanced */
    .profile-section {
      background: white;
      padding: 2.5rem;
      border-radius: 16px;
      box-shadow: var(--shadow-md);
      margin-bottom: 2rem;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .profile-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
    }

    .profile-section:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-2px);
    }

    .profile-section h2 {
      color: var(--text-dark);
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0 0 2rem 0;
      padding: 0;
      border: none;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .profile-section h2::before {
      content: '';
      width: 4px;
      height: 28px;
      background: linear-gradient(180deg, var(--primary) 0%, var(--primary-light) 100%);
      border-radius: 2px;
    }

    /* Profile Details Grid - Enhanced */
    .profile-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem 2.5rem;
      margin-top: 0;
    }

    .detail-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      padding: 0;
      border: none;
      background: transparent;
      position: relative;
      padding-bottom: 0.75rem;
    }

    .detail-group::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 2px;
      background: linear-gradient(90deg, var(--primary) 0%, transparent 100%);
      transition: width 0.3s ease;
    }

    .detail-group:hover::after {
      width: 100%;
    }

    .detail-label {
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--primary);
      margin-bottom: 0.4rem;
    }

    .detail-value {
      font-size: 1.1rem;
      color: var(--text-dark);
      font-weight: 500;
      padding: 0;
      background: transparent;
      border: none;
      line-height: 1.6;
    }

    .detail-value:empty::before {
      content: 'Not provided';
      color: var(--text-muted);
      font-style: italic;
      font-weight: 400;
      opacity: 0.7;
    }

    /* Form Styling - Enhanced */
    .form-group {
      margin-bottom: 2rem;
      position: relative;
    }

    .form-group label {
      display: block;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--primary);
      margin-bottom: 0.75rem;
      transition: color 0.3s ease;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.9rem 0;
      border: none;
      border-bottom: 2px solid var(--border-color);
      background: transparent;
      font-size: 1.05rem;
      color: var(--text-dark);
      transition: all 0.3s ease;
      box-sizing: border-box;
      font-family: 'Archivo', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-bottom-color: var(--primary);
      padding-left: 8px;
    }

    .form-group input:focus+label,
    .form-group select:focus+label {
      color: var(--primary-dark);
    }

    .form-group input::placeholder {
      color: var(--text-muted);
      font-style: italic;
      opacity: 0.6;
    }

    .form-group small {
      display: block;
      margin-top: 0.75rem;
      font-size: 0.8rem;
      color: var(--text-muted);
      line-height: 1.5;
    }

    /* Age field specific styling */
    .form-group input[readonly] {
      background-color: #f8f9fa;
      cursor: not-allowed;
      opacity: 0.8;
    }

    /* Messages - Enhanced */
    .message {
      padding: 1.25rem 1.5rem;
      margin: 1.5rem 0;
      border-radius: 12px;
      text-align: left;
      border-left: 4px solid;
      font-weight: 500;
      box-shadow: var(--shadow-sm);
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideOut {
      from {
        opacity: 1;
        transform: translateY(0);
      }

      to {
        opacity: 0;
        transform: translateY(-10px);
      }
    }

    .message.success {
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f1 100%);
      color: #2e7d32;
      border-left-color: var(--primary);
    }

    .message.error {
      background: linear-gradient(135deg, #ffebee 0%, #fef5f5 100%);
      color: #c62828;
      border-left-color: #ef5350;
    }

    /* Verification Notices - Enhanced */
    .verification-notice {
      background: linear-gradient(135deg, #fff9e6 0%, #fffef7 100%);
      border-left: 4px solid #ffd54f;
      padding: 1.25rem 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      display: flex;
      align-items: flex-start;
      gap: 1.25rem;
      box-shadow: var(--shadow-sm);
      animation: slideIn 0.3s ease;
    }

    .verification-notice i {
      font-size: 1.5rem;
      margin-top: 0.1rem;
      flex-shrink: 0;
    }

    .verification-notice.pending {
      background: linear-gradient(135deg, #e3f2fd 0%, #f3f9ff 100%);
      border-left-color: #42a5f5;
      color: #1565c0;
    }

    .verification-notice.verified {
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f1 100%);
      border-left-color: var(--primary);
      color: #2e7d32;
    }

    .verification-notice strong {
      display: block;
      margin-bottom: 0.4rem;
      font-size: 1.05rem;
      font-weight: 700;
    }

    .verification-notice p {
      margin: 0;
      font-size: 0.9rem;
      line-height: 1.6;
      opacity: 0.9;
    }

    .close-notice {
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      color: inherit;
      padding: 0.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: auto;
      margin-top: -0.5rem;
      flex-shrink: 0;
      opacity: 0.6;
      transition: opacity 0.2s ease;
      border-radius: 4px;
    }

    .close-notice:hover {
      opacity: 1;
      background-color: rgba(0, 0, 0, 0.1);
    }

    .close-notice:active {
      transform: scale(0.95);
    }

    /* Buttons - Enhanced - FIXED */
    .btn {
      padding: 0.6rem 1rem;
      border-radius: 10px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 0.95rem;
      font-weight: 600;
      transition: all 0.25s ease;
      border: none;
      width: 180px;
      min-width: 180px;
      max-width: 180px;
      height: 44px;
      box-shadow: var(--shadow-sm);
      position: relative;
      overflow: hidden;
      box-sizing: border-box;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.5s ease;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(92, 178, 93, 0.25);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
      color: var(--text-dark);
    }

    .btn-secondary:hover {
      background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
      transform: translateY(-2px);
    }

    .btn-outline {
      background: white;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
    }

    .btn-disabled {
      background: #e0e0e0;
      color: #9e9e9e;
      cursor: not-allowed;
      opacity: 0.7;
    }

    .btn-disabled:hover {
      transform: none;
      box-shadow: var(--shadow-sm);
    }

    .btn i {
      font-size: 0.95rem;
    }

    /* Action Buttons - Enhanced - FIXED */
    .action-buttons {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      margin-top: 2.5rem;
      padding-top: 2rem;
      border-top: 2px solid var(--border-color);
      align-items: center;
    }

    /* Button Groups - FIXED */
    .btn-group {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      flex-wrap: wrap;
      align-items: center;
    }

    /* Age Notice - Enhanced */
    .age-notice {
      background: linear-gradient(135deg, #fff9e6 0%, #fffef7 100%);
      border-left: 4px solid #ffd54f;
      padding: 0.9rem 1.2rem;
      border-radius: 8px;
      margin-top: 0.75rem;
      font-size: 0.85rem;
      color: #856404;
      line-height: 1.5;
      box-shadow: var(--shadow-sm);
    }

    .age-notice.error {
      background: linear-gradient(135deg, #ffebee 0%, #fef5f5 100%);
      border-left-color: #ef5350;
      color: #c62828;
    }

    .age-notice.success {
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f1 100%);
      border-left-color: var(--primary);
      color: #2e7d32;
    }

    /* Error message styles for form validation */
    .error-message {
      display: block;
      color: #dc3545;
      font-size: 0.875rem;
      font-weight: 500;
      margin-top: 0.25rem;
      padding: 0.25rem 0;
      min-height: 1.2rem;
      line-height: 1.2;
    }

    .form-group input.error,
    .form-group select.error {
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
    }

    .form-group input:invalid {
      border-color: #dc3545;
    }

    .form-group input:valid:not(:placeholder-shown) {
      border-color: #28a745;
    }

    .required {
      color: #dc3545;
      font-weight: 600;
    }

    /* File Upload - Enhanced - FIXED */
    .custom-file-upload {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-top: 0.5rem;
    }

    .file-upload-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1rem;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 0.95rem;
      font-weight: 600;
      transition: all 0.25s ease;
      box-shadow: var(--shadow-sm);
      width: 180px;
      min-width: 180px;
      max-width: 180px;
      height: 44px;
      justify-content: center;
      box-sizing: border-box;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    .file-upload-btn:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(92, 178, 93, 0.3);
    }

    #file-name {
      font-size: 0.9rem;
      color: var(--text-muted);
      font-style: italic;
      padding: 0.5rem 1rem;
      background: var(--bg-light);
      border-radius: 6px;
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* Profile Header Button Alignment - FIXED */
    .profile-section>div:first-child {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    /* Ensure consistent button sizes in profile header */
    .profile-section>div:first-child .btn {
      flex-shrink: 0;
    }

    /* Edit Form Specific */
    .edit-form {
      display: none;
    }

    /* Responsive Design - FIXED */
    @media (max-width: 768px) {
      .profile-container {
        padding: 0 1rem;
        margin: 1rem auto;
      }

      .profile-details {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .profile-section {
        padding: 1.5rem;
        border-radius: 12px;
      }

      .profile-section h2 {
        font-size: 1.3rem;
      }

      .profile-name {
        font-size: 1.8rem;
      }

      .profile-pic-container {
        width: 140px;
        height: 140px;
      }

      .btn,
      .file-upload-btn {
        width: 170px;
        min-width: 170px;
        max-width: 170px;
        height: 44px;
        padding: 0.6rem 1rem;
      }

      .action-buttons {
        flex-direction: column;
        align-items: stretch;
      }

      .action-buttons .btn {
        width: 170px;
        min-width: 170px;
        max-width: 170px;
        justify-content: center;
        align-self: center;
      }

      .btn-group {
        flex-direction: column;
        align-items: stretch;
      }

      .btn-group .btn {
        width: 170px;
        min-width: 170px;
        max-width: 170px;
        align-self: center;
      }

      .profile-section>div:first-child {
        flex-direction: column;
        align-items: flex-start;
      }

      .profile-section>div:first-child .btn {
        align-self: center;
        text-align: center;
        width: 170px;
        min-width: 170px;
        max-width: 170px;
      }
    }

    @media (max-width: 480px) {
      .profile-section {
        padding: 1.25rem;
      }

      .detail-value {
        font-size: 1rem;
      }

      .profile-name {
        font-size: 1.6rem;
      }

      .btn,
      .file-upload-btn {
        width: 160px;
        min-width: 160px;
        max-width: 160px;
        height: 44px;
        padding: 0.55rem 0.9rem;
      }
    }

    /* Print Styles */
    @media print {

      .btn,
      .action-buttons,
      .profile-pic-upload {
        display: none !important;
      }

      .profile-section {
        box-shadow: none;
        border: 1px solid #ddd;
      }
    }

    /* Hide navbar verification notice on profile page - show only profile-specific notice */
    /* Target navbar verification notice specifically (without pending/verified classes) */
    .navbar .verification-notice:not(.pending):not(.verified) {
      display: none !important;
    }

    /* Also hide the top yellow banner notification in navbar on profile page */
    body > .verification-notice {
      display: none !important;
    }
  </style>
</head>

<body>
  <?php include './Navbar/navbar.php'; ?>

  <div class="profile-container">
    <!-- Display Messages -->
    <?php 
    $message_shown = false;
    if (isset($_SESSION['profile_message'])): 
      $message_shown = true;
    ?>
      <div class="message <?php echo $_SESSION['profile_message_type']; ?>">
        <?php echo $_SESSION['profile_message']; ?>
      </div>
      <?php
      unset($_SESSION['profile_message']);
      unset($_SESSION['profile_message_type']);
      ?>
    <?php endif; ?>

    <!-- Verification Notice -->
    <?php if (!$message_shown && $show_verification_notice): ?>
      <div class="verification-notice pending">
        <i class="fas fa-clock"></i>
        <div>
          <strong>Account Pending Verification</strong>
          <p>Your profile has been completed. Please wait for admin verification.</p>
        </div>
      </div>
    <?php elseif ($user['AccountStatus'] === 'verified' && !isset($_SESSION['verified_notice_dismissed'])): ?>
      <div class="verification-notice verified" id="verifiedNotice">
        <i class="fas fa-check-circle"></i>
        <div>
          <strong>Verified Account</strong>
          <p>Your account has been verified by admin.</p>
        </div>
        <button class="close-notice" onclick="closeVerifiedNotice()" title="Close notification">
          <i class="fas fa-times"></i>
        </button>
      </div>
    <?php elseif ($user['AccountStatus'] === 'unverified' && $show_complete_button): ?>
      <div class="verification-notice">
        <i class="fas fa-exclamation-circle"></i>
        <div>
          <strong>Unverified Account</strong>
          <p>Please complete your profile information to submit for verification. Note: You must be at least 18 years old.
          </p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="profile-header">
      <div class="profile-pic-container">
        <img
          src="<?php echo !empty($_SESSION['profile_pic']) ? '../' . htmlspecialchars($_SESSION['profile_pic']) : 'https://via.placeholder.com/150?text=No+Image'; ?>"
          alt="" class="profile-pic" id="profilePicDisplay">
        <!-- Always show profile picture upload option -->
        <label for="profilePicInput" class="profile-pic-upload">
          <i class="fas fa-camera"></i>
        </label>
        <form id="profilePicForm" action="profile.php" method="post" enctype="multipart/form-data">
          <input type="file" id="profilePicInput" name="profile_pic" accept="image/*">
          <input type="hidden" name="upload_profile_pic" value="1">
        </form>
      </div>
      <h1 class="profile-name">
        <?php
        if (!empty($user['Firstname']) && !empty($user['Lastname'])) {
          echo htmlspecialchars($user['Firstname'] . ' ' . $user['Lastname']);
        } else {
          echo 'User';
        }
        ?>
      </h1>
    </div>

    <!-- View Mode -->
    <div id="viewMode">
      <div class="profile-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h2>Personal Information</h2>
          <?php if ($show_complete_button): ?>
            <button onclick="toggleEditMode()" class="btn btn-primary">
              <i class="fas fa-edit"></i> Complete Profile
            </button>
          <?php else: ?>
            <button class="btn btn-disabled" title="Personal information completed and locked">
              <i class="fas fa-lock"></i> Profile Locked
            </button>
          <?php endif; ?>
        </div>

        <div class="profile-details">
          <div class="detail-group">
            <div class="detail-label">First Name</div>
            <div class="detail-value">
              <?php echo !empty($user['Firstname']) ? htmlspecialchars($user['Firstname']) : 'Not provided'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Last Name</div>
            <div class="detail-value">
              <?php echo !empty($user['Lastname']) ? htmlspecialchars($user['Lastname']) : 'Not provided'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Middle Name</div>
            <div class="detail-value">
              <?php echo !empty($user['Middlename']) ? htmlspecialchars($user['Middlename']) : 'Not provided'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Email</div>
            <div class="detail-value">
              <?php echo !empty($user['Email']) ? htmlspecialchars($user['Email']) : 'Not provided'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Contact Number</div>
            <div class="detail-value">
              <?php echo !empty($user['ContactNo']) ? htmlspecialchars($user['ContactNo']) : 'Not provided'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Gender</div>
            <div class="detail-value">
              <?php echo !empty($user['Gender']) ? htmlspecialchars($user['Gender']) : 'Not specified'; ?></div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Birthdate</div>
            <div class="detail-value">
              <?php
              if (!empty($user['Birthdate'])) {
                echo date('F j, Y', strtotime($user['Birthdate']));
              } else {
                echo 'Not provided';
              }
              ?>
            </div>
          </div>
          <div class="detail-group">
            <div class="detail-label">Address</div>
            <div class="detail-value">
              <?php echo !empty($user['Address']) ? htmlspecialchars($user['Address']) : 'Not provided'; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Mode -->
    <div id="editMode" class="edit-form">
      <form method="POST" action="profile.php" enctype="multipart/form-data">
        <div class="profile-section">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Complete Your Profile</h2>
            <button type="button" onclick="toggleEditMode()" class="btn btn-secondary">
              <i class="fas fa-times"></i> Cancel
            </button>
          </div>

          <div class="form-group">
            <label for="firstname">First Name *</label>
            <input type="text" name="firstname" id="firstname"
              value="<?php echo htmlspecialchars($user['Firstname'] ?: ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="lastname">Last Name *</label>
            <input type="text" name="lastname" id="lastname"
              value="<?php echo htmlspecialchars($user['Lastname'] ?: ''); ?>" required>
          </div>

          <div class="form-group">
            <label for="middlename">Middle Name</label>
            <input type="text" name="middlename" id="middlename"
              value="<?php echo htmlspecialchars($user['Middlename'] ?: ''); ?>">
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['Email'] ?: ''); ?>" required>
            <span id="emailError" class="error-message"></span>
          </div>

          <div class="form-group">
            <label for="contactno">Contact Number *</label>
            <input type="text" name="contactno" id="contactno"
              value="<?php echo htmlspecialchars($user['ContactNo'] ?: ''); ?>" 
              pattern="[0-9]{10,11}" 
              title="Please enter a valid 10-11 digit contact number"
              required>
            <small>Enter a valid 10-11 digit contact number</small>
            <span id="contactError" class="error-message"></span>
          </div>

          <div class="form-group">
            <label for="gender">Gender *</label>
            <select name="gender" id="gender" required>
              <option value="">Select Gender</option>
              <option value="Male" <?php echo ($user['Gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($user['Gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>

          <div class="form-group">
            <label for="birthdate">Birthdate *</label>
            <input type="date" name="Birthdate" id="birthdate"
              value="<?php echo htmlspecialchars($user['Birthdate'] ?: ''); ?>" required>
            <div class="age-notice" id="ageNotice">
              You must be at least 18 years old to complete your profile.
            </div>
          </div>

          <div class="form-group">
            <label for="address">Address *</label>
            <input type="text" name="address" id="address"
              value="<?php echo htmlspecialchars($user['Address'] ?: ''); ?>" required>
            <span id="addressError" class="error-message"></span>
          </div>

          <div class="form-group">
            <label for="birthplace">Birthplace *</label>
            <input type="text" name="birthplace" id="birthplace"
              value="<?php echo htmlspecialchars($user['Birthplace'] ?: ''); ?>" required>
            <span id="birthplaceError" class="error-message"></span>
          </div>

          <div class="form-group">
            <label for="civilstatus">Civil Status *</label>
            <select name="civilstatus" id="civilstatus" required>
              <option value="">Select Status</option>
              <option value="Single" <?php echo ($user['CivilStatus'] === 'Single') ? 'selected' : ''; ?>>Single</option>
              <option value="Married" <?php echo ($user['CivilStatus'] === 'Married') ? 'selected' : ''; ?>>Married
              </option>
              <option value="Widowed" <?php echo ($user['CivilStatus'] === 'Widowed') ? 'selected' : ''; ?>>Widowed
              </option>
              <option value="Divorced" <?php echo ($user['CivilStatus'] === 'Divorced') ? 'selected' : ''; ?>>Divorced
              </option>
            </select>
            <span id="civilstatusError" class="error-message"></span>
          </div>

          <div class="form-group">
            <label for="nationality">Nationality *</label>
            <input type="text" name="nationality" id="nationality"
              value="<?php echo htmlspecialchars($user['Nationality'] ?: ''); ?>" required>
            <span id="nationalityError" class="error-message"></span>
          </div>

          <div class="form-group">
           <label for="valid-id">Valid ID *</label>
<p class="helper-text">Please provide a Barangay ID or any government-issued ID with your address listed as Barangay Sampaguita.</p>
            <input type="file" name="valid_id" id="valid_id" accept="image/*,application/pdf" style="display: none;" 
              <?php echo empty($user['ValidID']) ? 'required' : ''; ?>>
            <button type="button" class="file-upload-btn" onclick="document.getElementById('valid_id').click();">
              <i class="fas fa-upload"></i>
              <span>Choose File</span>
            </button>
            <span id="file-name"><?php echo !empty($user['ValidID']) ? 'File already uploaded' : ''; ?></span>
            <small>Upload a clear photo of your valid government ID (JPEG, PNG, PDF only, max 5MB)
              <?php if (!empty($user['ValidID'])): ?>
                <br><em>Current file: <?php echo basename($user['ValidID']); ?> (You can upload a new one to replace it)</em>
                <span class="existing-file" style="display: none;"></span>
              <?php endif; ?>
            </small>
            <span id="validIdError" class="error-message"></span>
          </div>

          <div class="btn-group">
            <button type="submit" name="update_profile" class="btn btn-primary" id="submitBtn">
              <i class="fas fa-save"></i> Complete Profile
            </button>
            <button type="button" onclick="toggleEditMode()" class="btn btn-secondary">
              <i class="fas fa-times"></i> Cancel
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="../Pages/landingpage.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <a href="../Login/logout.php" class="btn btn-primary">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

  <script>
    // Profile picture upload
    document.getElementById('profilePicInput').addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById('profilePicDisplay').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
        document.getElementById('profilePicForm').submit();
      }
    });

    // Toggle between view and edit modes
    function toggleEditMode() {
      const viewMode = document.getElementById('viewMode');
      const editMode = document.getElementById('editMode');

      if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
      } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
      }
    }

    // File upload display
    document.getElementById('valid_id').addEventListener('change', function () {
      const fileName = document.getElementById('file-name');
      if (this.files && this.files[0]) {
        fileName.textContent = this.files[0].name;
      } else {
        fileName.textContent = '';
      }
    });

    // Calculate age from birthdate and update age field
    function calculateAgeFromBirthdate(birthdate) {
      if (!birthdate) return 0;
      
      const today = new Date();
      const birthDate = new Date(birthdate);
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      
      return age;
    }

    // Update age field when birthdate changes
    function updateAgeField() {
      const birthdateInput = document.getElementById('birthdate');
      const ageInput = document.getElementById('age');
      
      if (birthdateInput && ageInput) {
        const age = calculateAgeFromBirthdate(birthdateInput.value);
        ageInput.value = age > 0 ? age : '';
        
        // Also update age validation notice
        updateAgeValidationNotice(age);
      }
    }

    // Update age validation notice
    function updateAgeValidationNotice(age) {
      const ageNotice = document.getElementById('ageNotice');
      const submitBtn = document.getElementById('submitBtn');
      
      if (!ageNotice) return;
      
      if (age < 18 && age > 0) {
        ageNotice.innerHTML = `You are ${age} years old. You must be at least 18 years old to complete your profile.`;
        ageNotice.className = 'age-notice error';
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.style.opacity = '0.5';
          submitBtn.style.cursor = 'not-allowed';
        }
      } else if (age >= 18) {
        ageNotice.innerHTML = `Age requirement met. You are ${age} years old.`;
        ageNotice.className = 'age-notice success';
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
          submitBtn.style.cursor = 'pointer';
        }
      } else {
        ageNotice.innerHTML = 'You must be at least 18 years old to complete your profile.';
        ageNotice.className = 'age-notice';
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
          submitBtn.style.cursor = 'pointer';
        }
      }
    }

    // Initialize - hide edit mode by default
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('editMode').style.display = 'none';
      
      // Set up birthdate change listener
      const birthdateInput = document.getElementById('birthdate');
      if (birthdateInput) {
        birthdateInput.addEventListener('change', updateAgeField);
        
        // Calculate initial age if birthdate is already set
        if (birthdateInput.value) {
          updateAgeField();
        }
      }

      // Check if verified notification was already dismissed
      initializeVerifiedNotice();

      // Initialize form validation
      initializeFormValidation();
    });

    // Initialize verified account notification
    function initializeVerifiedNotice() {
      const notice = document.getElementById('verifiedNotice');
      if (notice) {
        // Notification is already hidden server-side if dismissed, so just show it
        notice.style.display = 'flex';
      }
    }

    // Close verified account notification
    function closeVerifiedNotice() {
      const notice = document.getElementById('verifiedNotice');
      if (notice) {
        // Animate out
        notice.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() {
          notice.style.display = 'none';
          
          // Save to session server-side
          const formData = new FormData();
          formData.append('action', 'dismiss_verified_notice');
          
          fetch('profile.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .catch(error => console.log('Dismissal saved to localStorage'));
          
          // Also save to localStorage as backup
          localStorage.setItem('verifiedNoticeDismissed', 'true');
        }, 300);
      }
    }

    // Profile validation functions
    function validateEmail() {
      const email = document.getElementById('email');
      const emailError = document.getElementById('emailError');
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      
      if (!email.value.trim()) {
        emailError.textContent = 'Email is required.';
        return false;
      } else if (!emailPattern.test(email.value)) {
        emailError.textContent = 'Please enter a valid email address.';
        return false;
      } else {
        emailError.textContent = '';
        return true;
      }
    }

    function validateContactNo() {
      const contact = document.getElementById('contactno');
      const contactError = document.getElementById('contactError');
      const contactPattern = /^[0-9]{10,11}$/;
      
      if (!contact.value.trim()) {
        contactError.textContent = 'Contact number is required.';
        return false;
      } else if (!contactPattern.test(contact.value)) {
        contactError.textContent = 'Contact number must be 10-11 digits.';
        return false;
      } else {
        contactError.textContent = '';
        return true;
      }
    }

    function validateRequiredField(fieldId, errorId, fieldName) {
      const field = document.getElementById(fieldId);
      const error = document.getElementById(errorId);
      
      if (!field || !error) return true; // Field doesn't exist, skip validation
      
      if (!field.value.trim()) {
        error.textContent = fieldName + ' is required.';
        return false;
      } else {
        error.textContent = '';
        return true;
      }
    }

    function validateForm() {
      let isValid = true;
      
      // Validate all required fields
      isValid &= validateRequiredField('email', 'emailError', 'Email');
      isValid &= validateEmail();
      isValid &= validateRequiredField('contactno', 'contactError', 'Contact Number');
      isValid &= validateContactNo();
      isValid &= validateRequiredField('address', 'addressError', 'Address');
      isValid &= validateRequiredField('birthplace', 'birthplaceError', 'Birthplace');
      isValid &= validateRequiredField('civilstatus', 'civilstatusError', 'Civil Status');
      isValid &= validateRequiredField('nationality', 'nationalityError', 'Nationality');
      
      // Validate Valid ID upload (only for new profiles without existing ID)
      const validIdFile = document.getElementById('valid_id');
      const validIdError = document.getElementById('validIdError');
      const hasExistingId = document.querySelector('.existing-file') !== null;
      
      if (validIdFile && validIdError && !hasExistingId && (!validIdFile.files || validIdFile.files.length === 0)) {
        validIdError.textContent = 'Valid ID is required.';
        isValid = false;
      } else if (validIdError) {
        validIdError.textContent = '';
      }
      
      const submitBtn = document.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = !isValid;
        if (!isValid) {
          submitBtn.style.opacity = '0.5';
          submitBtn.style.cursor = 'not-allowed';
        } else {
          submitBtn.style.opacity = '1';
          submitBtn.style.cursor = 'pointer';
        }
      }
      
      return isValid;
    }

    // Enhanced DOMContentLoaded to include validation
    function initializeFormValidation() {
      // Add validation on input events
      const fields = ['email', 'contactno', 'address', 'birthplace', 'civilstatus', 'nationality'];
      
      fields.forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
          field.addEventListener('input', validateForm);
          field.addEventListener('blur', validateForm);
        }
      });
      
      // Special handling for email
      const emailField = document.getElementById('email');
      if (emailField) {
        emailField.addEventListener('input', validateEmail);
        emailField.addEventListener('blur', validateEmail);
      }
      
      // Special handling for contact number
      const contactField = document.getElementById('contactno');
      if (contactField) {
        contactField.addEventListener('input', validateContactNo);
        contactField.addEventListener('blur', validateContactNo);
      }
      
      // Valid ID file input
      const validIdField = document.getElementById('valid_id');
      if (validIdField) {
        validIdField.addEventListener('change', validateForm);
      }
      
      // Form submission validation
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            alert('Please complete all required fields before submitting.');
          }
        });
      }
      
      // Initial validation
      validateForm();
    }
  </script>
</body>

</html>
