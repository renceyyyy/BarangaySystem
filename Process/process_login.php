<?php
// Don't start session yet - we need to set the session name first based on role
require_once 'db_connection.php';

// Get database connection
$conn = getDBConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Start a temporary session for error messages
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

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
            $userRole = $user['Role'];
            
            // Destroy any existing temp session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
            // Set role-specific session name BEFORE starting session
            if (in_array($userRole, ['admin', 'finance', 'sk', 'SuperAdmin', 'lupong'])) {
                session_name('BarangayStaffSession');
            } else {
                session_name('BarangayResidentSession');
            }
            
            // Now start the role-specific session
            session_start();
            
            // ✅ Store all session info
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $userRole;
            $_SESSION['fullname'] = $user['FullName']; // full name from userloginfo
            $_SESSION['login_time'] = time();

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
            } elseif ($user['Role'] === 'lupong') {
                header("Location: ../Pages/LupongPage.php");
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
