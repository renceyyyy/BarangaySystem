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
    $sql = "SELECT 'Guardianship/Solo Parent' as type, CONCAT(request_type, ' for ', child_name) as description, refno, request_date as date_requested, RequestStatus as status, decline_reason FROM guardianshiptbl WHERE user_id = ? ORDER BY request_date DESC";
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

    // No Birth Certificate requests
    $sql = "SELECT 'No Birth Certificate' as type, 'No Birth Certificate Request' as description, refno, request_date as date_requested, RequestStatus as status, decline_reason FROM no_birthcert_tbl WHERE user_id = ? ORDER BY request_date DESC";
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
    $sql = "SELECT 'Cohabitation Form' as type, CONCAT(Name, ' - ', Purpose) as description, refno, DateRequested as date_requested, RequestStatus as status FROM cohabitationtbl WHERE UserId = ? ORDER BY DateRequested DESC";
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
    foreach ($currentRequests as $request) {
        $requestSnapshot[$request['refno']] = [
            'status' => strtolower($request['status']),
            'type' => $request['type'],
            'description' => $request['description'],
            'decline_reason' => $request['decline_reason'] ?? '',
            'refno' => $request['refno']
        ];
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
    
    // Prepare response with current snapshot
    // The client will handle comparison with previous state
    $response = [
        'success' => true,
        'requests' => $requestSnapshot,
        'counts' => $currentCounts,
        'hasPendingRequests' => !empty($pendingTypes),
        'timestamp' => time()
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