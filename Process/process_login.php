<?php
session_start();
require_once 'db_connection.php';
require_once 'activity_logger.php';

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

    // JOIN userlogtbl and userloginfo to get full name
    $sql = "
        SELECT 
            u.UserID,
            u.Username,
            u.Password,
            u.Role,
            CONCAT(i.Firstname, ' ', i.Lastname) AS FullName
        FROM userlogtbl u
        JOIN userloginfo i ON u.UserID = i.UserID
        WHERE u.Username = ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['login_error'] = "Database error. Please try again later.";
        header("Location: ../Login/login.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            // ✅ Store all session info
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['fullname'] = $user['FullName']; // full name from userloginfo

            // Log login activity
            logActivity(
                ActivityLogger::LOGIN,
                'User logged in successfully',
                'Login',
                null,
                'success'
            );

            // Insert TimeIn into audittrail
            $timeIn = date("Y-m-d H:i:s");
            $auditSql = "INSERT INTO audittrail (username, TimeIn) VALUES (?, ?)";
            $auditStmt = $conn->prepare($auditSql);

            if ($auditStmt) {
                $auditStmt->bind_param("ss", $username, $timeIn);
                if ($auditStmt->execute()) {
                    $_SESSION['audit_id'] = $conn->insert_id;
                }
                $auditStmt->close();
            }

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
            $_SESSION['login_error'] = "Invalid username or password";
        }
    } else {
        $_SESSION['login_error'] = "Invalid username or password";
    }

    $stmt->close();
    header("Location: ../Login/login.php");
    exit();
}

header("Location: ../Login/login.php");
exit();
?>
