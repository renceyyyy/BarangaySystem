<?php
// get_user_requests.php - AJAX endpoint for fetching user requests
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
   echo json_encode(['success' => false, 'message' => 'Not logged in']);
   exit();
}

// Include database connection module
require_once '../Process/db_connection.php';

// Get database connection
$conn = getDBConnection();

if (!$conn) {
   echo json_encode(['success' => false, 'message' => 'Database connection failed']);
   exit();
}

// Function to get user requests from all tables
function getUserRequests($userId, $conn) {
   $requests = [];
   
   try {
       // Document requests
       $sql = "SELECT 'Document Request' as request_type, DocuType as details, refno, 
               DateRequested as date_requested, 'Pending' as status, ReqID as id
               FROM docsreqtbl WHERE UserId = ? ORDER BY DateRequested DESC";
       $stmt = db_prepare($sql);
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
       $sql = "SELECT 'Business Request' as request_type, 
               CONCAT(RequestType, ' - ', BusinessName) as details, 
               refno, RequestedDate as date_requested, 'Pending' as status, BsnssID as id
               FROM businesstbl ORDER BY RequestedDate DESC";
       $stmt = db_prepare($sql);
       if ($stmt) {
           $stmt->execute();
           $result = $stmt->get_result();
           while ($row = $result->fetch_assoc()) {
               $requests[] = $row;
           }
           $stmt->close();
       }
       
       // Scholarship applications
       $sql = "SELECT 'Scholarship Application' as request_type, 
               CONCAT('Scholarship - ', Reason) as details, 
               CONCAT('SCH-', ApplicationID) as refno, 
               DateApplied as date_requested, RequestStatus as status, ApplicationID as id
               FROM scholarship WHERE UserID = ? ORDER BY DateApplied DESC";
       $stmt = db_prepare($sql);
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
       $sql = "SELECT 'Unemployment Certificate' as request_type, 
               CONCAT(certificate_type, ' - ', fullname) as details, 
               refno, request_date as date_requested, status, id
               FROM unemploymenttbl WHERE user_id = ? ORDER BY request_date DESC";
       $stmt = db_prepare($sql);
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
       $sql = "SELECT 'Guardianship/Solo Parent' as request_type, 
               CONCAT(request_type, ' - ', child_name) as details, 
               refno, request_date as date_requested, status, id
               FROM guardianshiptbl WHERE user_id = ? ORDER BY request_date DESC";
       $stmt = db_prepare($sql);
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
       $sql = "SELECT 'No Birth Certificate' as request_type, 
               CONCAT('No Birth Cert - ', requestor_name) as details, 
               refno, request_date as date_requested, status, id
               FROM no_birthcert_tbl WHERE user_id = ? ORDER BY request_date DESC";
       $stmt = db_prepare($sql);
       if ($stmt) {
           $stmt->bind_param("i", $userId);
           $stmt->execute();
           $result = $stmt->get_result();
           while ($row = $result->fetch_assoc()) {
               $requests[] = $row;
           }
           $stmt->close();
       }
       
       // Sort all requests by date (newest first)
       usort($requests, function($a, $b) {
           return strtotime($b['date_requested']) - strtotime($a['date_requested']);
       });
       
   } catch (Exception $e) {
       error_log("Error fetching user requests: " . $e->getMessage());
       return [];
   }
   
   return $requests;
}

// Get user requests and return as JSON
try {
   $userRequests = getUserRequests($_SESSION['user_id'], $conn);
   echo json_encode([
       'success' => true,
       'requests' => $userRequests,
       'count' => count($userRequests)
   ]);
} catch (Exception $e) {
   error_log("AJAX Error: " . $e->getMessage());
   echo json_encode([
       'success' => false,
       'message' => 'Error fetching requests'
   ]);
}

// Don't close the singleton connection
?>
