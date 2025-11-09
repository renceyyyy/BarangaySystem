<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection module
require_once '../Process/db_connection.php';

// Check if user is logged in - if not, redirect to login
function checkAuth()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login.php");
        exit();
    }
}

// Get user data if logged in
if (isset($_SESSION['user_id'])) {
    // Get database connection
    $conn = getDBConnection();

    $userId = $_SESSION['user_id'];
    $sql = "SELECT Firstname, Lastname, ProfilePic, AccountStatus FROM userloginfo WHERE UserId = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        // Store user data in session
        $_SESSION['Firstname'] = $userData['Firstname'] ?? '';
        $_SESSION['Lastname'] = $userData['Lastname'] ?? '';

        // Check if account status has changed to verified
        $previousStatus = $_SESSION['AccountStatus'] ?? 'unverified';
        $newStatus = $userData['AccountStatus'] ?? 'unverified';

        $_SESSION['AccountStatus'] = $newStatus;

        // Set verification notification if status changed to verified
        if ($previousStatus !== 'verified' && $newStatus === 'verified') {
            $_SESSION['verification_notification'] = 'Your account has been verified! You can now access all services.';
        }

        // Always refresh profile picture from database
        $_SESSION['profile_pic'] = !empty($userData['ProfilePic']) ? $userData['ProfilePic'] : '';
    }

    $stmt->close();
    // Don't close the connection here as it might be needed elsewhere
    
    // REFRESH pending request types on each navbar load
    // This ensures we always have current pending status
    if (!isset($_SESSION['pending_by_type']) || !is_array($_SESSION['pending_by_type'])) {
        $_SESSION['pending_by_type'] = [];
    }
    
    // Re-check pending requests to ensure fresh data
    $pendingTypes = [];
    
    // Document requests
    $sql = "SELECT COUNT(*) as count FROM docsreqtbl WHERE UserId = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['document'] = true;
        }
        $stmt->close();
    }

    // Business requests
    $sql = "SELECT COUNT(*) as count FROM businesstbl WHERE UserId = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['business'] = true;
        }
        $stmt->close();
    }

    // Scholarship
    $sql = "SELECT COUNT(*) as count FROM scholarship WHERE UserID = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['scholarship'] = true;
        }
        $stmt->close();
    }

    // Unemployment
    $sql = "SELECT COUNT(*) as count FROM unemploymenttbl WHERE user_id = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['unemployment'] = true;
        }
        $stmt->close();
    }

    // Guardianship
    $sql = "SELECT COUNT(*) as count FROM guardianshiptbl WHERE user_id = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['guardianship'] = true;
        }
        $stmt->close();
    }

    // No Birth Certificate
    $sql = "SELECT COUNT(*) as count FROM no_birthcert_tbl WHERE user_id = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['no_birth'] = true;
        }
        $stmt->close();
    }

    // Complaints
    $sql = "SELECT COUNT(*) as count FROM complaintbl WHERE Userid = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['complaint'] = true;
        }
        $stmt->close();
    }
    
    // Update session with latest pending types
    $_SESSION['pending_by_type'] = $pendingTypes;
}

// Check for success messages
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
$refNo = $_SESSION['ref_no'] ?? '';

// Check for verification notification
$verificationNotification = $_SESSION['verification_notification'] ?? '';

// Clear the messages after displaying them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['ref_no']);
unset($_SESSION['verification_notification']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Services Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../Styles/StylesNavbar.css">

    <!-- Mobile Responsive Styles -->
    <style>
        /* Base navbar styles */
        .navbar {
            position: relative;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
        }

        /* Mobile Responsive Styles */
        @media screen and (max-width: 768px) {
            .navbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                position: relative;
                height: auto;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .logo {
                max-height: 40px;
                order: 1;
            }

            .menu-container {
                display: none !important;
                /* Force hide by default */
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                width: 100%;
                background-color: #5CB25D;
                flex-direction: column;
                z-index: 1000;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
                max-height: calc(100vh - 80px);
                overflow-y: auto;
            }

            .menu-container.active {
                display: flex !important;
            }

            /* Profile section at the top in mobile */
            .mobile-profile-section {
                order: -1;
                width: 100%;
                padding: 15px 20px;
                border-bottom: 2px solid rgba(255, 255, 255, 0.3);
                background-color: rgba(0, 0, 0, 0.15);
            }

            .user-dropdown {
                width: 100%;
                margin: 0;
                position: relative;
            }

            .user-btn {
                width: 100%;
                justify-content: flex-start;
                padding: 14px 0;
                border: none;
                background: none;
                text-align: left;
                font-weight: 500;
                font-size: 1.05rem;
                transition: background-color 0.2s ease;
            }

            .user-btn:hover {
                background-color: rgba(0, 0, 0, 0.1);
                border-radius: 4px;
            }

            .user-dropdown-content {
                position: static;
                display: none !important;
                /* Force hide by default */
                width: 100%;
                box-shadow: none;
                background-color: rgba(0, 0, 0, 0.3);
                border-radius: 0;
                margin-top: 8px;
                border-left: 4px solid rgba(255, 255, 255, 0.3);
            }

            .user-dropdown.active .user-dropdown-content {
                display: block !important;
            }

            .user-dropdown-content a {
                padding: 12px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                display: block;
                font-size: 1rem;
                transition: background-color 0.2s ease;
            }

            .user-dropdown-content a:hover {
                background-color: rgba(0, 0, 0, 0.2);
            }

            /* Regular menu items */
            .menu-item {
                padding: 16px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                text-align: left;
                width: 100%;
                display: block;
                text-decoration: none;
                font-size: 1.1rem;
                font-weight: 500;
                transition: background-color 0.2s ease;
            }

            .menu-item:hover {
                background-color: rgba(0, 0, 0, 0.1);
            }

            .dropdown {
                width: 100%;
            }

            .dropbtn {
                width: 100%;
                text-align: left;
                padding: 16px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
                justify-content: space-between;
                background: none;
                border: none;
                color: inherit;
                display: flex;
                align-items: center;
                font-size: 1.1rem;
                font-weight: 500;
                transition: background-color 0.2s ease;
            }

            .dropbtn:hover {
                background-color: rgba(0, 0, 0, 0.1);
            }

            .dropdown-content {
                position: static;
                display: none !important;
                /* Force hide by default */
                width: 100%;
                box-shadow: none;
                background-color: rgba(0, 0, 0, 0.3);
                margin-left: 0;
                border-radius: 0;
                max-height: 500px;
                overflow-y: auto;
                border-left: 4px solid rgba(255, 255, 255, 0.3);
            }

            .dropdown.active .dropdown-content {
                display: block !important;
            }

            .dropdown-content a {
                padding: 14px 25px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                display: block;
                white-space: normal;
                word-break: break-word;
                font-size: 1rem;
                transition: background-color 0.2s ease;
            }

            .dropdown-content a:hover {
                background-color: rgba(0, 0, 0, 0.2);
            }

            .signup-btn {
                width: calc(100% - 40px);
                text-align: center;
                margin: 15px 20px;
                padding: 14px 20px;
                border: 2px solid rgba(255, 255, 255, 0.5);
                border-radius: 6px;
                display: block;
                text-decoration: none;
                font-weight: 600;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .signup-btn:hover {
                background-color: rgba(255, 255, 255, 0.1);
                border-color: white;
            }

            .nav-profile-pic {
                width: 30px;
                height: 30px;
                margin-right: 10px;
                border-radius: 50%;
                object-fit: cover;
            }

            /* Desktop menu hidden in mobile */
            .menu-container .user-dropdown:not(.mobile-profile-section .user-dropdown) {
                display: none;
            }
        }

        /* Show mobile profile section only on mobile */
        .mobile-profile-section {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .mobile-profile-section {
                display: block;
            }
        }

        /* Tablet Responsive */
        @media screen and (max-width: 1024px) and (min-width: 769px) {
            .navbar {
                padding: 10px 20px;
            }

            .logo {
                max-height: 45px;
            }

            .menu-item {
                font-size: 0.9rem;
                margin: 0 8px;
            }

            .dropbtn {
                font-size: 0.9rem;
            }

            .user-btn {
                font-size: 0.9rem;
            }
        }

        /* Small mobile devices */
        @media screen and (max-width: 480px) {
            .navbar {
                padding: 8px 12px;
            }

            .logo {
                max-height: 35px;
            }

            .menu-item,
            .dropbtn,
            .user-btn {
                font-size: 1rem;
                padding: 14px 18px;
            }

            .dropdown-content a,
            .user-dropdown-content a {
                padding: 12px 18px;
                font-size: 0.95rem;
            }

            .menu-container {
                top: 70px !important;
                max-height: calc(100vh - 70px) !important;
            }

            .dropdown-content {
                max-height: 450px;
            }
        }

        /* Ensure proper stacking */
        .navbar {
            z-index: 999;
        }

        /* Verification notice style */
        .verification-notice {
            background: linear-gradient(135deg, #ffcc00 0%, #ffd700 100%);
            color: #333;
            padding: 12px 20px;
            text-align: center;
            font-weight: 500;
            border-bottom: 2px solid #ff9800;
            display: block !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .verification-notice i {
            margin-right: 8px;
            color: #ff6b6b;
        }

        .verification-notice a {
            color: #333;
            font-weight: bold;
            text-decoration: underline;
            transition: all 0.3s ease;
        }

        .verification-notice a:hover {
            color: #ff6b6b;
            text-decoration: underline;
        }

        @media screen and (max-width: 768px) {
            .verification-notice {
                display: block !important;
                padding: 10px 15px;
                font-size: 0.95rem;
            }

            .verification-notice i {
                margin-right: 5px;
            }
        }

        /* Custom Notification Popup */
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: none;
            max-width: 350px;
            animation: slideIn 0.3s ease-out;
            font-family: 'Archivo', sans-serif;
        }

        .custom-notification.verification {
            background-color: #4CAF50;
        }

        .custom-notification.warning {
            background-color: #ffcc00;
            color: #333;
        }

        .custom-notification i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .custom-notification-content {
            display: flex;
            align-items: center;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Change Password Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <!-- Custom Notification Popup -->
    <div class="custom-notification" id="customNotification">
        <div class="custom-notification-content">
            <i class="fas fa-check-circle"></i>
            <span id="notificationMessage"></span>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Change Password</h2>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                    <div id="currentPasswordError" class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                    <div id="newPasswordError" class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <div id="confirmPasswordError" class="error-message"></div>
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Verification Notice (shown only for unverified users) -->
    <?php if (isset($_SESSION['user_id']) && ($_SESSION['AccountStatus'] ?? 'unverified') === 'unverified'): ?>
        <div class="verification-notice">
            <i class="fas fa-exclamation-circle"></i> 
            <span>Complete your <a href="profile.php" style="color: #333; font-weight: bold; text-decoration: underline;">Account Profile</a> for admin to verify your account and access services.</span>
        </div>
    <?php endif; ?>

    <nav class="navbar">
        <a href="landingpage.php">
            <img src="../Assets/sampaguitalogo.png" alt="Logo" class="logo" />
        </a>

        <!-- Mobile menu toggle button -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="menu-container" id="menuContainer">
            <!-- Mobile Profile Section (shown only on mobile at the top) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mobile-profile-section">
                    <div class="user-dropdown" id="mobileUserDropdown">
                        <button class="user-btn" id="mobileUserDropdownBtn">
                            <?php if (!empty($_SESSION['profile_pic'])): ?>
                                <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" class="nav-profile-pic"
                                    alt="Profile">
                            <?php else: ?>
                                <i class="fas fa-user-circle" style="font-size: 1.5rem; margin-right: 10px;"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['Firstname'] ?? 'User'); ?>
                            <i class="fa fa-caret-down" style="margin-left: auto;"></i>
                        </button>
                        <div class="user-dropdown-content">
                            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                            <a href="UserReports.php"><i class="fas fa-file-alt"></i> My Requests</a>
                            <a href="#" id="changePasswordMobile"><i class="fas fa-key"></i> Change Password</a>
                            <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Regular Menu Items -->
            <a href="landingpage.php" class="menu-item">Home</a>
            <a href="Newspage.php" class="menu-item">News</a>
            <a href="about.php" class="menu-item">About</a>

            <div class="dropdown" id="servicesDropdown">
                <button class="dropbtn" id="servicesDropdownBtn">
                    Services <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (($_SESSION['AccountStatus'] ?? 'unverified') === 'verified'): ?>
                            <!-- Government Documents -->
                            <a href="../NewRequests/NewGovernmentDocs.php">Request Government Documents</a>

                            <!-- Business Permit -->
                            <a href="../NewRequests/NewBusinessRequest.php">Request Business Permit / Closure</a>

                            <!-- Complaint -->
                            <a href="../NewRequests/NewComplain.php">Complain</a>

                            <!-- Scholarship -->
                            <a href="../NewRequests/NewScholar.php">Apply for Scholar</a>

                            <!-- No Fix Income -->
                            <a href="../NewRequests/NewNoFixIncome.php">No fix income/No income</a>

                            <!-- Guardianship -->
                            <a href="../NewRequests/NewGuardianshipForm.php">Guardianship</a>

                            <!-- Cohabitation -->
                            <a href="../NewRequests/CohabilitationForm.php">Cohabitation</a>

                            <!-- No Birth Certificate -->
                            <a href="../NewRequests/NewNoBirthCertificate.php">No Birth Certificate</a>
                            
                        <?php else: ?>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Request
                                Government Documents</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Request
                                Business Permit</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Blotter/Complaint</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Apply
                                for Scholar</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">No
                                fix income/No income</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Guardianship</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">Cohabitation</a>
                            <a href="#"
                                onclick="showNotification('Please wait for admin to verify your account to access services.', 'warning'); return false;">No
                                Birth Certificate</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="../Login/login.php">Request Government Documents</a>
                        <a href="../Login/login.php">Request Business Permit</a>
                        <a href="../Login/login.php">Blotter/Complaint</a>
                        <a href="../Login/login.php">Apply for Scholar</a>
                        <a href="../Login/login.php">No fix income/No income</a>
                        <a href="../Login/login.php">Guardianship</a>
                    
                    <?php endif; ?>
                </div>
            </div>

            <!-- Desktop User Dropdown (hidden on mobile) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown desktop-user-dropdown" id="userDropdown">
                    <button class="user-btn" id="userDropdownBtn">
                        <?php if (!empty($_SESSION['profile_pic'])): ?>
                            <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" class="nav-profile-pic"
                                alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['Firstname'] ?? 'User'); ?>
                    </button>
                    <div class="user-dropdown-content">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="UserReports.php"><i class="fas fa-file-alt"></i> My Requests</a>
                        <a href="#" id="changePasswordDesktop"><i class="fas fa-key"></i> Change Password</a>
                        <a href="../Login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../Login/Acc_Log_Registration.php" class="signup-btn">Sign up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Request Pages (no popups needed - using main pages directly) -->
    <!-- All service requests are handled through dedicated pages:
         - NewGovernmentDocs.php
         - NewBusinessRequest.php
         - NewComplain.php
         - NewScholar.php
         - NewNoFixIncome.php
         - NewGuardianshipForm.php
         - NewNoBirthCertificate.php
         - CohabilitationForm.php
    -->

    <script src="../Script.js"></script>

    <!-- Mobile Responsive Script -->
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const menuContainer = document.getElementById('menuContainer');
            const servicesDropdown = document.getElementById('servicesDropdown');
            const servicesDropdownBtn = document.getElementById('servicesDropdownBtn');
            const mobileUserDropdown = document.getElementById('mobileUserDropdown');
            const mobileUserDropdownBtn = document.getElementById('mobileUserDropdownBtn');
            const changePasswordDesktop = document.getElementById('changePasswordDesktop');
            const changePasswordMobile = document.getElementById('changePasswordMobile');
            const modal = document.getElementById('changePasswordModal');
            const closeBtn = document.querySelector('.close');
            const changePasswordForm = document.getElementById('changePasswordForm');

            // Ensure menu is hidden on page load for mobile
            if (window.innerWidth <= 768 && menuContainer) {
                menuContainer.classList.remove('active');
                if (servicesDropdown) servicesDropdown.classList.remove('active');
                if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
            }

            // Toggle mobile menu
            if (mobileMenuToggle && menuContainer) {
                mobileMenuToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isActive = menuContainer.classList.contains('active');
                    const icon = this.querySelector('i');

                    if (isActive) {
                        menuContainer.classList.remove('active');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                        // Close all dropdowns
                        if (servicesDropdown) servicesDropdown.classList.remove('active');
                        if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    } else {
                        menuContainer.classList.add('active');
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    }
                });
            }

            // Handle services dropdown in mobile
            if (servicesDropdownBtn && servicesDropdown) {
                servicesDropdownBtn.addEventListener('click', function (e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        e.stopPropagation();
                        servicesDropdown.classList.toggle('active');
                        // Close mobile user dropdown if open
                        if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    }
                });
            }

            // Handle mobile user dropdown
            if (mobileUserDropdownBtn && mobileUserDropdown) {
                mobileUserDropdownBtn.addEventListener('click', function (e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        e.stopPropagation();
                        mobileUserDropdown.classList.toggle('active');
                        // Close services dropdown if open
                        if (servicesDropdown) servicesDropdown.classList.remove('active');
                    }
                });
            }

            // Close mobile menu when clicking on regular menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function () {
                    if (window.innerWidth <= 768 && menuContainer) {
                        menuContainer.classList.remove('active');
                        const icon = mobileMenuToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                });
            });

            // Close mobile menu when clicking on dropdown links
            const dropdownLinks = document.querySelectorAll('.dropdown-content a, .user-dropdown-content a');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768 && menuContainer) {
                        menuContainer.classList.remove('active');
                        const icon = mobileMenuToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                        if (servicesDropdown) servicesDropdown.classList.remove('active');
                        if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    }
                });
            });

            // Close mobile menu and dropdowns when window is resized to desktop
            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    if (menuContainer) menuContainer.classList.remove('active');
                    if (servicesDropdown) servicesDropdown.classList.remove('active');
                    if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    const icon = mobileMenuToggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                } else {
                    // Ensure menu is hidden when resizing to mobile
                    if (menuContainer && !menuContainer.classList.contains('active')) {
                        const icon = mobileMenuToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            });

            // Close dropdowns when clicking outside (mobile only)
            document.addEventListener('click', function (e) {
                if (window.innerWidth <= 768) {
                    // Don't close if clicking inside the menu container
                    if (!e.target.closest('.menu-container') && !e.target.closest('.mobile-menu-toggle')) {
                        if (menuContainer && menuContainer.classList.contains('active')) {
                            menuContainer.classList.remove('active');
                            const icon = mobileMenuToggle.querySelector('i');
                            if (icon) {
                                icon.classList.remove('fa-times');
                                icon.classList.add('fa-bars');
                            }
                        }
                        if (servicesDropdown) servicesDropdown.classList.remove('active');
                        if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    }
                }
            });

            // Prevent menu container clicks from bubbling up
            if (menuContainer) {
                menuContainer.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }

            // Show verification notification if available
            <?php if (!empty($verificationNotification)): ?>
                showNotification('<?php echo $verificationNotification; ?>', 'verification');
            <?php endif; ?>

            // Change Password Modal functionality
            if (changePasswordDesktop) {
                changePasswordDesktop.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.style.display = 'block';
                });
            }

            if (changePasswordMobile) {
                changePasswordMobile.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.style.display = 'block';
                    // Close mobile menu
                    if (menuContainer) menuContainer.classList.remove('active');
                    if (mobileUserDropdown) mobileUserDropdown.classList.remove('active');
                    const icon = mobileMenuToggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                    clearFormErrors();
                });
            }

            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    clearFormErrors();
                }
            });

            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors();
                    
                    const currentPassword = document.getElementById('currentPassword').value;
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    let isValid = true;
                    
                    if (!currentPassword) {
                        document.getElementById('currentPasswordError').textContent = 'Current password is required';
                        isValid = false;
                    }
                    
                    if (!newPassword) {
                        document.getElementById('newPasswordError').textContent = 'New password is required';
                        isValid = false;
                    } else if (newPassword.length < 6) {
                        document.getElementById('newPasswordError').textContent = 'Password must be at least 6 characters';
                        isValid = false;
                    }
                    
                    if (!confirmPassword) {
                        document.getElementById('confirmPasswordError').textContent = 'Please confirm your password';
                        isValid = false;
                    } else if (newPassword !== confirmPassword) {
                        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                        isValid = false;
                    }
                    
                    if (isValid) {
                        // Submit the form via AJAX
                        const formData = new FormData();
                        formData.append('currentPassword', currentPassword);
                        formData.append('newPassword', newPassword);
                        
                        fetch('../Process/change_password.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Password changed successfully!', 'verification');
                                modal.style.display = 'none';
                                changePasswordForm.reset();
                            } else {
                                if (data.field) {
                                    document.getElementById(data.field + 'Error').textContent = data.message;
                                } else {
                                    showNotification(data.message || 'Error changing password', 'warning');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('An error occurred. Please try again.', 'warning');
                        });
                    }
                });
            }

            function clearFormErrors() {
                const errorElements = document.querySelectorAll('.error-message');
                errorElements.forEach(element => {
                    element.textContent = '';
                });
            }
        });

        // Custom notification function
        function showNotification(message, type = 'warning') {
            const notification = document.getElementById('customNotification');
            const messageElement = document.getElementById('notificationMessage');

            // Set notification type
            notification.className = 'custom-notification';
            if (type === 'verification') {
                notification.classList.add('verification');
                notification.querySelector('i').className = 'fas fa-check-circle';
            } else {
                notification.classList.add('warning');
                notification.querySelector('i').className = 'fas fa-exclamation-circle';
            }

            messageElement.textContent = message;
            notification.style.display = 'block';
            notification.style.animation = 'slideIn 0.3s ease-out';

            // Auto hide after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }, 5000);
        }
    </script>
</body>

</html>
