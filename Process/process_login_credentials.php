<?php
session_start();

// Include database connection module
require_once 'db_connection.php';

// Handle login credentials submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get database connection
    $conn = getDBConnection();
    
    // Validate input
    $errors = [];
    
    // First name validation
    $firstname = trim($_POST['firstname'] ?? '');
    if (empty($firstname)) {
        $errors[] = "First name is required";
    } elseif (strlen($firstname) > 50) {
        $errors[] = "First name must be 50 characters or less";
    }

    // Last name validation
    $lastname = trim($_POST['lastname'] ?? '');
    if (empty($lastname)) {
        $errors[] = "Last name is required";
    } elseif (strlen($lastname) > 50) {
        $errors[] = "Last name must be 50 characters or less";
    }

    // Email validation
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif (strlen($email) > 100) {
        $errors[] = "Email must be 100 characters or less";
    }
    
    // Username validation
    $username = trim($_POST['username'] ?? '');
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must be 50 characters or less";
    }

    // Password validation
    $password = trim($_POST['password'] ?? '');
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    } elseif (strlen($password) > 72) {
        $errors[] = "Password must be 72 characters or less";
    }

    // Password confirmation
    if ($password !== trim($_POST['confirm_password'] ?? '')) {
        $errors[] = "Passwords do not match";
    }

    // Terms agreement
    if (empty($_POST['agreeTerms'] ?? '')) {
        $errors[] = "You must agree to the terms and conditions";
    }

    // Check for admin registration attempt
    $is_admin_request = (strtoupper(substr($username, 0, 9)) === "BRGYADMIN");
    $current_user_is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

    // Security validation: Only allow admin creation if current user is admin
    if ($is_admin_request && !$current_user_is_admin) {
        $errors[] = "Admin accounts can only be created by existing administrators";
        // Prevent the username from being used by adding a random string
        $username = "invalid_".bin2hex(random_bytes(4))."_".$username;
    }

    // If there are errors, redirect back with messages
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: ../Login/login_registration.php");
        exit();
    }

    // Check if username already exists
    $check_username = db_prepare("SELECT LoginID FROM userlogtbl WHERE Username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();
    
    if ($check_username->num_rows > 0) {
        $_SESSION['message'] = "Username already exists";
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: ../Login/login_registration.php");
        exit();
    }
    $check_username->close();

    // Check if email already exists
    $check_email = db_prepare("SELECT UserId FROM userloginfo WHERE Email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        $_SESSION['message'] = "Email already exists";
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: ../Login/login_registration.php");
        exit();
    }
    $check_email->close();

    // Determine role
    $role = ($is_admin_request && $current_user_is_admin) ? 'admin' : 'resident';

    // Hash the password before storing
    $username = db_escape($username);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($password_hash === false) {
        $_SESSION['message'] = "Error hashing password";
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: ../Login/login_registration.php");
        exit();
    }

    $user_id = (int)($_POST['user_id'] ?? 0);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert login credentials
        $sql = "INSERT INTO userlogtbl (UserID, Username, Password, Role) VALUES (?, ?, ?, ?)";
        $stmt = db_prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("isss", $user_id, $username, $password_hash, $role);
        
        if (!$stmt->execute()) {
            throw new Exception("Error inserting login credentials: " . $stmt->error);
        }
        $stmt->close();

        // Update userloginfo with firstname, lastname, and email
        $update_sql = "UPDATE userloginfo SET Firstname = ?, Lastname = ?, Email = ? WHERE UserId = ?";
        $update_stmt = db_prepare($update_sql);
        
        if ($update_stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("sssi", $firstname, $lastname, $email, $user_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Error updating user info: " . $update_stmt->error);
        }
        $update_stmt->close();

        // Commit transaction
        $conn->commit();
        
        // Clean up session data on success
        unset($_SESSION['new_user_id']);
        unset($_SESSION['form_data']);
        
        $_SESSION['message'] = "Account created successfully! Please login.";
        $_SESSION['message_type'] = "success";
        header("Location: ../Login/login.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: ../Login/login_registration.php");
        exit();
    }
}

header("Location: ../Login/Signup.php");
exit();
?>
