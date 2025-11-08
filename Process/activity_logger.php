<?php
/**
 * Activity Logger - Simple logging to existing user_activity_history table
 * Logs all user and admin actions to track system usage
 */

class ActivityLogger {
    
    // Activity Type Constants
    const LOGIN = 'LOGIN';
    const LOGOUT = 'LOGOUT';
    const PROFILE_UPDATE = 'PROFILE_UPDATE';
    const PROFILE_COMPLETION = 'PROFILE_COMPLETION';
    const DOCUMENT_REQUEST = 'DOCUMENT_REQUEST';
    const DOCUMENT_APPROVE = 'DOCUMENT_APPROVE';
    const DOCUMENT_DECLINE = 'DOCUMENT_DECLINE';
    const BUSINESS_REQUEST = 'BUSINESS_REQUEST';
    const BUSINESS_APPROVE = 'BUSINESS_APPROVE';
    const BUSINESS_DECLINE = 'BUSINESS_DECLINE';
    const UNEMPLOYMENT_REQUEST = 'UNEMPLOYMENT_REQUEST';
    const UNEMPLOYMENT_APPROVE = 'UNEMPLOYMENT_APPROVE';
    const UNEMPLOYMENT_DECLINE = 'UNEMPLOYMENT_DECLINE';
    const GUARDIANSHIP_REQUEST = 'GUARDIANSHIP_REQUEST';
    const GUARDIANSHIP_APPROVE = 'GUARDIANSHIP_APPROVE';
    const GUARDIANSHIP_DECLINE = 'GUARDIANSHIP_DECLINE';
    const ITEM_REQUEST = 'ITEM_REQUEST';
    const ITEM_RETURN = 'ITEM_RETURN';
    const ACCOUNT_VERIFY = 'ACCOUNT_VERIFY';
    const ACCOUNT_CREATION = 'ACCOUNT_CREATION';
    const PASSWORD_CHANGE = 'PASSWORD_CHANGE';
    const PAYMENT_MADE = 'PAYMENT_MADE';
    const NEWS_CREATED = 'NEWS_CREATED';
    const NEWS_DELETED = 'NEWS_DELETED';
    const BLOTTER_CREATED = 'BLOTTER_CREATED';
    const FILE_UPLOAD = 'FILE_UPLOAD';
    const FILE_DOWNLOAD = 'FILE_DOWNLOAD';
    const ADMIN_ACTION = 'ADMIN_ACTION';

    /**
     * Log an activity to user_activity_history table
     * 
     * @param int $userId - User ID performing the action
     * @param string $activityType - Type of activity (use constants)
     * @param string $description - Description of what happened
     * @param string $requestType - Type of request (document, business, etc.) - optional
     * @param string $requestRefNo - Request reference number - optional
     * @param string $requestStatus - Current status of request - optional
     * @param string $adminAction - Admin-specific action details - optional
     */
    public static function log($userId, $activityType, $description, $requestType = null, $requestRefNo = null, $requestStatus = null, $adminAction = null) {
        try {
            $conn = getDBConnection();
            
            // Get user role
            $userRole = self::getUserRole($userId);
            
            // Get IP address
            $ipAddress = self::getClientIP();
            
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("
                INSERT INTO user_activity_history 
                (UserId, ActivityType, ActivityDescription, RequestType, RequestRefNo, RequestStatus, IPAddress, UserRole, Timestamp, CreatedAt) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param(
                "isssssss",
                $userId,
                $activityType,
                $description,
                $requestType,
                $requestRefNo,
                $requestStatus,
                $ipAddress,
                $userRole
            );
            
            // Execute
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user role from database
     */
    private static function getUserRole($userId) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT Role FROM userlogtbl WHERE UserID = ? LIMIT 1");
            
            if (!$stmt) {
                error_log("Prepare failed in getUserRole: " . $conn->error);
                return 'user';
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? $row['Role'] : 'user';
        } catch (Exception $e) {
            error_log("Error getting user role: " . $e->getMessage());
            return 'user';
        }
    }

    /**
     * Get client IP address with fallback chain
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs (take first one)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '0.0.0.0';
        }
        
        // Validate IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }
        
        return $ip;
    }

    /**
     * Retrieve activity logs with optional filters
     */
    public static function getActivityLogs($filters = []) {
        try {
            $conn = getDBConnection();
            
            $query = "SELECT * FROM user_activity_history WHERE 1=1";
            $params = [];
            $types = "";
            
            // Filter by user
            if (!empty($filters['user_id'])) {
                $query .= " AND UserId = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            // Filter by activity type
            if (!empty($filters['activity_type'])) {
                $query .= " AND ActivityType = ?";
                $params[] = $filters['activity_type'];
                $types .= "s";
            }
            
            // Filter by role
            if (!empty($filters['user_role'])) {
                $query .= " AND UserRole = ?";
                $params[] = $filters['user_role'];
                $types .= "s";
            }
            
            // Filter by date range
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(Timestamp) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(Timestamp) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }
            
            // Search in description
            if (!empty($filters['search'])) {
                $query .= " AND (ActivityDescription LIKE ? OR RequestRefNo LIKE ?)";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ss";
            }
            
            // Pagination
            $limit = $filters['limit'] ?? 50;
            $offset = $filters['offset'] ?? 0;
            
            $query .= " ORDER BY Timestamp DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $conn->prepare($query);
            
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $logs = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $logs;
            
        } catch (Exception $e) {
            error_log("Error retrieving activity logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count activity logs with filters
     */
    public static function getActivityLogsCount($filters = []) {
        try {
            $conn = getDBConnection();
            
            $query = "SELECT COUNT(*) as total FROM user_activity_history WHERE 1=1";
            $params = [];
            $types = "";
            
            // Apply same filters as getActivityLogs
            if (!empty($filters['user_id'])) {
                $query .= " AND UserId = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            if (!empty($filters['activity_type'])) {
                $query .= " AND ActivityType = ?";
                $params[] = $filters['activity_type'];
                $types .= "s";
            }
            
            if (!empty($filters['user_role'])) {
                $query .= " AND UserRole = ?";
                $params[] = $filters['user_role'];
                $types .= "s";
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(Timestamp) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(Timestamp) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (ActivityDescription LIKE ? OR RequestRefNo LIKE ?)";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ss";
            }
            
            $stmt = $conn->prepare($query);
            
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return (int) $row['total'];
            
        } catch (Exception $e) {
            error_log("Error counting activity logs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete old logs (maintenance)
     */
    public static function clearOldLogs($days = 90) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("DELETE FROM user_activity_history WHERE Timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error clearing old logs: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Helper function for quick logging
 * Usage: logActivity(ActivityLogger::LOGIN, 'User logged in', 'LoginRequest', 'REF123', 'success')
 */
if (!function_exists('logActivity')) {
    function logActivity($activityType, $description, $requestType = null, $requestRefNo = null, $requestStatus = null, $adminAction = null) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        return ActivityLogger::log(
            $_SESSION['user_id'],
            $activityType,
            $description,
            $requestType,
            $requestRefNo,
            $requestStatus,
            $adminAction
        );
    }
}

?>
