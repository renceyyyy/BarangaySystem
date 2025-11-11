<?php
session_start();
require_once 'db_connection.php';

// Get database connection
$conn = getDBConnection();

// Verify current user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../Login/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize variables
    $errors = [];
    $username = 'BRGYADMIN_' . trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate inputs
    if (empty(trim($_POST['username'] ?? ''))) {
        $errors[] = "Username is required";
    }
    if (strlen($username) > 50) {
        $errors[] = "Username must be 50 characters or less";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username exists
    $check_sql = "SELECT Username FROM userlogtbl WHERE Username = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        $errors[] = "Database error. Please try again later.";
    } else {
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $errors[] = "Username already exists";
        }
        $check_stmt->close();
    }

    // If no errors, create account
    if (empty($errors)) {
        // Get the highest admin UserID
        $get_id_sql = "SELECT MAX(UserID) AS max_id FROM userlogtbl WHERE Username LIKE 'BRGYADMIN_%'";
        $result = $conn->query($get_id_sql);
        $next_id = 1000; // Starting admin ID
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $next_id = ($row['max_id'] ?? 1000) + 1;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($password_hash === false) {
            $errors[] = "Failed to hash password";
        } else {
            $insert_sql = "INSERT INTO userlogtbl (UserID, Username, Password, Role) VALUES (?, ?, ?, 'admin')";
            $insert_stmt = $conn->prepare($insert_sql);
            
            if (!$insert_stmt) {
                $errors[] = "Database error. Please try again later.";
            } else {
                $insert_stmt->bind_param("iss", $next_id, $username, $password_hash);
                
                if ($insert_stmt->execute()) {
                    header("Location: ../Pages/Adminpage.php?success=1");
                    exit();
                } else {
                    $errors[] = "Database error: " . $insert_stmt->error;
                }
                $insert_stmt->close();
            }
        }
    }

    // Handle errors
    if (!empty($errors)) {
        $_SESSION['admin_errors'] = $errors;
        header("Location: ../Pages/Adminpage.php?error=1");
        exit();
    }
} else {
    header("Location: ../Pages/Adminpage.php");
    exit();
}
?>
