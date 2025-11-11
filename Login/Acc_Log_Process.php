<?php
session_start();

// Include your database module
require_once '../Process/db_connection.php';

// Get database connection instance
$db = DatabaseConnection::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Get form data and sanitize
    $username = trim(db_escape($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? db_escape($_POST['role']) : 'resident';

    // Validation
    $errors = array();

    // Check if fields are empty
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    // Username validation
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }

    if (strlen($username) > 50) {
        $errors[] = "Username must not exceed 50 characters.";
    }

    // Check if username contains only allowed characters
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (strlen($password) > 255) {
        $errors[] = "Password is too long.";
    }

    // Check password complexity
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Role validation
    $allowed_roles = array('admin', 'resident');
    if (!in_array($role, $allowed_roles)) {
        $role = 'resident'; // Default to resident if invalid role
    }

    // Check if username already exists
    if (empty($errors)) {
        $check_stmt = db_prepare("SELECT Username FROM userlogtbl WHERE Username = ?");
        if ($check_stmt === false) {
            $errors[] = "Database error. Please try again later.";
        } else {
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Username already exists. Please choose a different username.";
            }
            $check_stmt->close();
        }
    }

    // If there are validation errors, redirect back with error message
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(" ", $errors);
        header("Location: Acc_Log_Registration.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction to ensure both inserts succeed or fail together
    $conn->begin_transaction();

    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Insert into userloginfo first to generate UserID
        $info_stmt = db_prepare("INSERT INTO userloginfo 
            (Firstname, Lastname, Middlename, Birthdate, ContactNo, Email, Birthplace, Address, CivilStatus, Nationality, AccountStatus) 
            VALUES ('uncompleted', 'uncompleted', 'uncompleted', 0, 0, 'uncompleted', 'uncompleted', 'uncompleted', 'uncompleted', 'uncompleted', 'unverified')");
            
        if ($info_stmt === false) {
            throw new Exception("Database error: Failed to prepare userloginfo insert statement. Error: " . $conn->error);
        }

        if (!$info_stmt->execute()) {
            throw new Exception("Error creating user profile: " . $info_stmt->error);
        }

        // Get the generated UserID
        $new_user_id = $info_stmt->insert_id;
        $info_stmt->close();

        // Insert into userlogtbl with the same UserID
        $stmt = db_prepare("INSERT INTO userlogtbl (UserID, Username, Password, Role) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Database error: Failed to prepare user insert statement. Error: " . $conn->error);
        }
        
        $stmt->bind_param("isss", $new_user_id, $username, $hashed_password, $role);

        if (!$stmt->execute()) {
            throw new Exception("Error creating user account: " . $stmt->error);
        }
        $stmt->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        // Commit the transaction
        $conn->commit();

        $_SESSION['register_success'] = "Account created successfully! You can now log in.";

        // Redirect to login page after successful registration
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Rollback the transaction in case of any error
        if ($conn) {
            $conn->rollback();
        }

        $_SESSION['register_error'] = "Registration failed. Please try again.";
        header("Location: Acc_Log_Registration.php");
        exit();
    }

} else {
    // If someone tries to access this file directly
    header("Location: Acc_Log_Registration.php");
    exit();
}
?>
