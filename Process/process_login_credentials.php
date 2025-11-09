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
        $_SESSION['form_data'] = ['username' => $_POST['username'] ?? '']; // Only save username, not passwords
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
        $_SESSION['form_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: ../Login/login_registration.php");
        exit();
    }
    $check_username->close();

    // Hash the password before storing
    $username = db_escape($username);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($password_hash === false) {
        $_SESSION['message'] = "Error hashing password";
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: ../Login/login_registration.php");
        exit();
    }

    // Determine role
    $role = ($is_admin_request && $current_user_is_admin) ? 'admin' : 'resident';

    // Insert login credentials with hashed password and role
    $sql = "INSERT INTO userlogtbl (UserID, Username, Password, Role) VALUES (?, ?, ?, ?)";
    $stmt = db_prepare($sql);
    
    if ($stmt === false) {
        $_SESSION['message'] = "Prepare failed: " . $conn->error;
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: ../Login/login_registration.php");
        exit();
    }
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    $stmt->bind_param("isss", $user_id, $username, $password_hash, $role);
    
    if ($stmt->execute()) {
        // Clean up session data on success
        unset($_SESSION['new_user_id']);
        unset($_SESSION['form_data']);
        
        $_SESSION['message'] = "Account created successfully! Please login.";
        $_SESSION['message_type'] = "success";
        header("Location: ../Login/login.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
        $_SESSION['form_data'] = ['username' => $_POST['username'] ?? ''];
        header("Location: ../Login/login_registration.php");
        exit();
    }
    
    $stmt->close();
}

header("Location: ../Login/Signup.php");
exit();
?>
