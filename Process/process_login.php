<?php
session_start();
require_once 'db_connection.php';

// Get database connection
$conn = getDBConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required";
        header("Location: ../Login/login.php");
        exit();
    }

    // Debug output
    error_log("Login attempt - Username: $username");

    $sql = "SELECT UserID, Username, Password, Role FROM userlogtbl WHERE Username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['login_error'] = "Database error. Please try again later.";
        header("Location: ../Login/login.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $_SESSION['login_error'] = "Database error. Please try again later.";
        header("Location: ../Login/login.php");
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Debug logs
        error_log("User found: " . print_r($user, true));
        error_log("Input password: $password");
        error_log("Stored hash: " . $user['Password']);
        error_log("Verification result: " . (password_verify($password, $user['Password']) ? "MATCH" : "NO MATCH"));

        if (password_verify($password, $user['Password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['is_admin'] = ($user['Role'] === 'admin');


            // Fetch user info to get full name // newly added 102825
            // $userInfoSql = "SELECT Lastname, Firstname, Middlename FROM userloginfo WHERE UserID = ?";
            // $userInfoStmt = $conn->prepare($userInfoSql);
            // if ($userInfoStmt) {
            //     $userInfoStmt->bind_param("i", $user['UserID']);
            //     $userInfoStmt->execute();
            //     $userInfoResult = $userInfoStmt->get_result();
            //     if ($userInfoResult->num_rows === 1) {
            //         $userInfo = $userInfoResult->fetch_assoc();
            //         $fullname = trim($userInfo['Lastname']) . ', ' . trim($userInfo['Firstname']) . ' ' . trim($userInfo['Middlename']);//i-comment ito para magamit ung naka comment sa baba na para sa uncompleted names
            //         $_SESSION['fullname'] = $fullname; //i-comment ito para magamit ung naka comment sa baba na para sa uncompleted names

            //         // // Inside process_login.php after fetching userInfo
            //         // if ($userInfo['Lastname'] !== 'uncompleted' && $userInfo['Firstname'] !== 'uncompleted') {
            //         //     $fullname = trim($userInfo['Lastname']) . ', ' . trim($userInfo['Firstname']) . ' ' . trim($userInfo['Middlename']);
            //         //     $_SESSION['fullname'] = $fullname;
            //         // } else {
            //         //     $_SESSION['fullname'] = $user['Username']; // Use username as fallback
            //         // }
            //     } else {
            //         $_SESSION['fullname'] = 'Unknown Officer'; // Fallback
            //     }
            //     $userInfoStmt->close();
            // } else {
            //     $_SESSION['fullname'] = 'Unknown Officer'; // Fallback
            // }

            // Insert TimeIn into audittrail
            $timeIn = date("Y-m-d H:i:s");
            $auditSql = "INSERT INTO audittrail (username, TimeIn) VALUES (?, ?)";
            $auditStmt = $conn->prepare($auditSql);

            if ($auditStmt) {
                $auditStmt->bind_param("ss", $username, $timeIn);
                if ($auditStmt->execute()) {
                    $_SESSION['audit_id'] = $conn->insert_id; // store ID for logout
                } else {
                    error_log("Audit insert failed: " . $auditStmt->error);
                }
                $auditStmt->close();
            } else {
                error_log("Audit prepare failed: " . $conn->error);
            }

            $stmt->close();

            // Redirect based on role
            if ($user['Role'] === 'admin') {
                header("Location: ../Pages/Adminpage.php");
            } elseif ($user['Role'] === 'finance') {
                header("Location: ../Pages/FinancePage.php");
            } elseif ($user['Role'] === 'sk') {
                header("Location: ../Pages/SKpage.php");
            } elseif ($user['Role'] === 'SuperAdmin') {
                header("Location: ../Pages/SuperAdmin.php");
                
            } else {
                header("Location: ../Pages/landingpage.php");
            }

            exit();
        } else {
            error_log("Password verification failed");
            $_SESSION['login_error'] = "Invalid username or password";
        }
    } else {
        error_log("Username not found: $username");
        $_SESSION['login_error'] = "Invalid username or password";
    }

    $stmt->close();
    header("Location: ../Login/login.php");
    exit();
}

header("Location: ../Login/login.php");
exit();
?>