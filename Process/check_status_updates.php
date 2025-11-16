<?php
// Set resident session name BEFORE starting session
session_name('BarangayResidentSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if user is a resident (not admin/finance/sk)
$userRole = $_SESSION['role'] ?? 'resident';
if (in_array($userRole, ['admin', 'finance', 'sk', 'SuperAdmin'])) {
    // Admin/staff shouldn't use this endpoint
    http_response_code(403);
    echo json_encode([
        'error' => 'This endpoint is for residents only',
        'role' => $userRole
    ]);
    exit();
}

require_once 'db_connection.php';

// Prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Get database connection
$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Function to get all user requests with current status
function getUserRequestsRealtime($conn, $userId) {
    $requests = [];

    // Document requests
    $sql = "SELECT 'Document Request' as type, DocuType as description, refno, DateRequested as date_requested, RequestStatus as status, Reason as decline_reason FROM docsreqtbl WHERE UserId = ? ORDER BY DateRequested DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Business requests 
    $sql = "SELECT 'Business Request' as type, CONCAT(BusinessName, ' - ', RequestType) as description, refno, RequestedDate as date_requested, RequestStatus as status, Reason as decline_reason FROM businesstbl WHERE UserId = ? ORDER BY RequestedDate DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Scholarship applications
    $sql = "SELECT 'Scholarship Application' as type, 'Scholarship Application' as description, ApplicationID as refno, DateApplied as date_requested, RequestStatus as status, '' as decline_reason FROM scholarship WHERE UserID = ? ORDER BY DateApplied DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Unemployment certificates
    $sql = "SELECT 'Unemployment Certificate' as type, CONCAT(certificate_type, ' Certificate') as description, refno, request_date as date_requested, RequestStatus as status, decline_reason FROM unemploymenttbl WHERE user_id = ? ORDER BY request_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Guardianship/Solo Parent requests
    $sql = "SELECT 'Guardianship/Solo Parent' as type, CONCAT(request_type, ' for ', child_name) as description, refno, request_date as date_requested, RequestStatus as status, COALESCE(Reason, '') as decline_reason FROM guardianshiptbl WHERE user_id = ? ORDER BY request_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Guardianship query failed: " . $conn->error);
    }

    // No Birth Certificate requests
    $sql = "SELECT 'No Birth Certificate' as type, 'No Birth Certificate Request' as description, refno, request_date as date_requested, RequestStatus as status, COALESCE(Reason, '') as decline_reason FROM no_birthcert_tbl WHERE user_id = ? ORDER BY request_date DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Complain requests 
    $sql = "SELECT 'Complaint' as type, Complain as description, refno, DateComplained as date_requested, RequestStatus as status, '' as decline_reason FROM complaintbl WHERE Userid = ? ORDER BY DateComplained DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Cohabitation Form requests
    $sql = "SELECT 'Cohabitation Form' as type, CONCAT(Name, ' - ', Purpose) as description, refno, DateRequested as date_requested, RequestStatus as status, COALESCE(Reason, '') as decline_reason FROM cohabitationtbl WHERE UserId = ? ORDER BY DateRequested DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
    }

    // Sort all requests by date
    usort($requests, function ($a, $b) {
        return strtotime($b['date_requested']) - strtotime($a['date_requested']);
    });

    return $requests;
}

// Function to get pending request types
function getPendingRequestTypesRealtime($conn, $userId) {
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

    // Cohabitation Form
    $sql = "SELECT COUNT(*) as count FROM cohabitationtbl WHERE UserId = ? AND RequestStatus = 'Pending'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()['count'] > 0) {
            $pendingTypes['cohabitation'] = true;
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

    return $pendingTypes;
}

try {
    // Get current requests and pending types
    $currentRequests = getUserRequestsRealtime($conn, $userId);
    $pendingTypes = getPendingRequestTypesRealtime($conn, $userId);
    
    // Create a snapshot of current request statuses
    $requestSnapshot = [];
    $newNotifications = [];
    
    // CHECK FOR ACCOUNT VERIFICATION STATUS CHANGE
    $accountStatusSql = "SELECT AccountStatus FROM userloginfo WHERE UserID = ? LIMIT 1";
    $accountStatusStmt = $conn->prepare($accountStatusSql);
    if ($accountStatusStmt) {
        $accountStatusStmt->bind_param("i", $userId);
        $accountStatusStmt->execute();
        $accountStatusResult = $accountStatusStmt->get_result();
        
        if ($accountStatusResult->num_rows > 0) {
            $currentAccountStatus = $accountStatusResult->fetch_assoc()['AccountStatus'];
            
            // Check previous account status from snapshot
            $prevAccountSql = "SELECT account_status FROM user_account_status_snapshot WHERE user_id = ? LIMIT 1";
            $prevAccountStmt = $conn->prepare($prevAccountSql);
            
            if ($prevAccountStmt) {
                $prevAccountStmt->bind_param("i", $userId);
                $prevAccountStmt->execute();
                $prevAccountResult = $prevAccountStmt->get_result();
                
                if ($prevAccountResult->num_rows > 0) {
                    // Check if status changed
                    $prevStatus = $prevAccountResult->fetch_assoc()['account_status'];
                    
                    if ($currentAccountStatus !== $prevStatus) {
                        // Account status changed!
                        if ($currentAccountStatus === 'verified') {
                            $message = "🎉 Great news! Your account has been verified by the admin. You can now access all barangay services!";
                            
                            // Insert notification
                            $insertNotifSql = "INSERT INTO user_notifications (user_id, refno, message, status, request_type) VALUES (?, 'ACCOUNT_VERIFICATION', ?, 'verified', 'Account Verification')";
                            $insertNotifStmt = $conn->prepare($insertNotifSql);
                            if ($insertNotifStmt) {
                                $insertNotifStmt->bind_param("is", $userId, $message);
                                if ($insertNotifStmt->execute()) {
                                    $notificationId = $insertNotifStmt->insert_id;
                                    $newNotifications[] = [
                                        'id' => $notificationId,
                                        'message' => $message,
                                        'status' => 'verified',
                                        'refno' => 'ACCOUNT_VERIFICATION',
                                        'type' => 'Account Verification'
                                    ];
                                }
                                $insertNotifStmt->close();
                            }
                            
                            // Update session
                            $_SESSION['AccountStatus'] = 'verified';
                        }
                        
                        // Update snapshot
                        $updateAccountSql = "UPDATE user_account_status_snapshot SET account_status = ?, last_checked = CURRENT_TIMESTAMP WHERE user_id = ?";
                        $updateAccountStmt = $conn->prepare($updateAccountSql);
                        if ($updateAccountStmt) {
                            $updateAccountStmt->bind_param("si", $currentAccountStatus, $userId);
                            $updateAccountStmt->execute();
                            $updateAccountStmt->close();
                        }
                    }
                } else {
                    // First time checking - insert initial snapshot
                    $insertAccountSql = "INSERT INTO user_account_status_snapshot (user_id, account_status) VALUES (?, ?)";
                    $insertAccountStmt = $conn->prepare($insertAccountSql);
                    if ($insertAccountStmt) {
                        $insertAccountStmt->bind_param("is", $userId, $currentAccountStatus);
                        $insertAccountStmt->execute();
                        $insertAccountStmt->close();
                    }
                }
                
                $prevAccountStmt->close();
            }
        }
        $accountStatusStmt->close();
    }
    
    // First, fetch any unread notifications from the database
    // NOTE: DO NOT mark as read here - notifications should persist across all devices
    // Users should see the same notification on all their logged-in devices
    $unreadSql = "SELECT id, message, status, refno, request_type, created_at FROM user_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5";
    $unreadStmt = $conn->prepare($unreadSql);
    if ($unreadStmt) {
        $unreadStmt->bind_param("i", $userId);
        $unreadStmt->execute();
        $unreadResult = $unreadStmt->get_result();
        
        while ($notifRow = $unreadResult->fetch_assoc()) {
            $newNotifications[] = [
                'id' => $notifRow['id'],
                'message' => $notifRow['message'],
                'status' => $notifRow['status'],
                'refno' => $notifRow['refno'],
                'type' => $notifRow['request_type'],
                'created_at' => $notifRow['created_at']
            ];
        }
        $unreadStmt->close();
        
        // REMOVED: Automatic marking as read
        // Instead, keep notifications as unread so they appear on all devices
        // They will be marked as read only when user explicitly acknowledges them
    }
    
    foreach ($currentRequests as $request) {
        $refno = $request['refno'];
        $currentStatus = strtolower($request['status']);
        $requestType = $request['type'];
        
        $requestSnapshot[$refno] = [
            'status' => $currentStatus,
            'type' => $requestType,
            'description' => $request['description'],
            'decline_reason' => $request['decline_reason'] ?? '',
            'refno' => $refno
        ];
        
        // Check if status has changed by comparing with snapshot table
        $checkSql = "SELECT last_status FROM user_request_snapshots WHERE user_id = ? AND refno = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        if ($checkStmt) {
            $checkStmt->bind_param("is", $userId, $refno);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Existing request - check for status change
                $snapshotRow = $checkResult->fetch_assoc();
                $previousStatus = strtolower($snapshotRow['last_status']);
                
                if ($currentStatus !== $previousStatus) {
                    // Status changed! Create notification
                    $message = '';
                    
                    if ($currentStatus === 'approved' || $currentStatus === 'completed') {
                        $message = "Your {$requestType} (Ref No: {$refno}) is approved. Please proceed to the barangay office and pay the needed fee to get your request.";
                    } elseif ($currentStatus === 'declined') {
                        $reason = $request['decline_reason'] ?: 'administrative reasons';
                        $message = "Unfortunately, your {$requestType} (Ref No: {$refno}) is declined due to {$reason}. For inquiries, go to the barangay or contact us at: 86380301.";
                    } elseif ($currentStatus === 'released') {
                        $message = "Your {$requestType} (Ref No: {$refno}) has been released. Thank you for using our services!";
                    } elseif ($currentStatus === 'pending') {
                        $message = "Your {$requestType} (Ref No: {$refno}) is now being processed. Please wait for approval.";
                    } else {
                        $message = "Your {$requestType} (Ref No: {$refno}) status has been updated to: {$currentStatus}";
                    }
                    
                    // Insert notification into database
                    $insertNotifSql = "INSERT INTO user_notifications (user_id, refno, message, status, request_type) VALUES (?, ?, ?, ?, ?)";
                    $insertNotifStmt = $conn->prepare($insertNotifSql);
                    if ($insertNotifStmt) {
                        $insertNotifStmt->bind_param("issss", $userId, $refno, $message, $currentStatus, $requestType);
                        if ($insertNotifStmt->execute()) {
                            // Get the inserted notification ID
                            $notificationId = $insertNotifStmt->insert_id;
                            // Add to new notifications array with ID
                            $newNotifications[] = [
                                'id' => $notificationId,
                                'message' => $message,
                                'status' => $currentStatus,
                                'refno' => $refno,
                                'type' => $requestType
                            ];
                        }
                        $insertNotifStmt->close();
                    }
                    
                    // Update snapshot
                    $updateSql = "UPDATE user_request_snapshots SET last_status = ?, last_checked = CURRENT_TIMESTAMP WHERE user_id = ? AND refno = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    if ($updateStmt) {
                        $updateStmt->bind_param("sis", $currentStatus, $userId, $refno);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
            } else {
                // New request - insert into snapshot table
                $insertSql = "INSERT INTO user_request_snapshots (user_id, refno, request_type, last_status) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                if ($insertStmt) {
                    $insertStmt->bind_param("isss", $userId, $refno, $requestType, $currentStatus);
                    $insertStmt->execute();
                    $insertStmt->close();
                }
            }
            
            $checkStmt->close();
        }
    }
    
    // Create counts for each status
    $pendingCount = 0;
    $approvedCount = 0;
    $releasedCount = 0;
    $declinedCount = 0;
    
    foreach ($currentRequests as $request) {
        switch (strtolower($request['status'])) {
            case 'pending':
                $pendingCount++;
                break;
            case 'approved':
            case 'completed':
                $approvedCount++;
                break;
            case 'released':
                $releasedCount++;
                break;
            case 'declined':
                $declinedCount++;
                break;
        }
    }
    
    $currentCounts = [
        'pending' => $pendingCount,
        'approved' => $approvedCount,
        'released' => $releasedCount,
        'declined' => $declinedCount,
        'total' => count($currentRequests)
    ];
    
    // Update session with latest data
    $_SESSION['pending_by_type'] = $pendingTypes;
    
    // Prepare response with current snapshot and new notifications
    $response = [
        'success' => true,
        'requests' => $requestSnapshot,
        'counts' => $currentCounts,
        'hasPendingRequests' => !empty($pendingTypes),
        'newNotifications' => $newNotifications,
        'timestamp' => time(),
        'debug' => [
            'total_requests' => count($currentRequests),
            'new_notifications_count' => count($newNotifications),
            'user_id' => $userId
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while checking for updates',
        'debug' => $e->getMessage()
    ]);
}
?>