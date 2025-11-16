<?php
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
     * Log an activity to user_activity_reports table
     * 
     * @param int $userId - User ID performing the action
     * @param string $userName - User name/full name
     * @param string $activity - Description of the activity (e.g., "Requested No Birth Certificate", "Changed Password", "Updated Profile")
     * @param string $loggedInDate - Optional: timestamp when user logged in
     * @param string $loggedOutDate - Optional: timestamp when user logged out
     */
    public static function log($userId, $activityType, $description, $requestType = null, $requestRefNo = null, $requestStatus = null, $adminAction = null) {
        try {
            error_log("ActivityLogger::log called with userId: " . $userId . ", activity: " . $description);
            
            $conn = getDBConnection();
            
            // Get user name
            $userName = self::getUserName($userId);
            error_log("ActivityLogger::log: Got userName: " . $userName);
            
            // Build detailed activity description
            $activityDescription = self::buildActivityDescription($activityType, $description, $requestType, $requestRefNo, $requestStatus);
            error_log("ActivityLogger::log: Activity description: " . $activityDescription);
            
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("
                INSERT INTO user_activity_reports 
                (UserId, UserName, Activity, ActivityDate) 
                VALUES (?, ?, ?, NOW())
            ");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return false;
            }
            
            // Bind parameters
            $stmt->bind_param(
                "iss",
                $userId,
                $userName,
                $activityDescription
            );
            
            // Execute
            $result = $stmt->execute();
            error_log("ActivityLogger::log execute result: " . ($result ? "true" : "false"));
            if (!$result) {
                error_log("log execute failed: " . $stmt->error);
            }
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log login activity with login timestamp (no activity description)
     */
    public static function logLogin($userId) {
        try {
            $conn = getDBConnection();
            $userName = self::getUserName($userId);
            
            $stmt = $conn->prepare("
                INSERT INTO user_activity_reports 
                (UserId, UserName, LoggedInDate, ActivityDate) 
                VALUES (?, ?, NOW(), NOW())
            ");
            
            if (!$stmt) {
                error_log("Prepare failed in logLogin: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("is", $userId, $userName);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error logging login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log logout activity with logout timestamp (no activity description)
     */
    public static function logLogout($userId) {
        try {
            error_log("ActivityLogger::logLogout called for userId: " . $userId);
            
            $conn = getDBConnection();
            $userName = self::getUserName($userId);
            error_log("ActivityLogger::logLogout: Got userName: " . $userName);
            
            $stmt = $conn->prepare("
                INSERT INTO user_activity_reports 
                (UserId, UserName, LoggedOutDate, ActivityDate) 
                VALUES (?, ?, NOW(), NOW())
            ");
            
            if (!$stmt) {
                error_log("Prepare failed in logLogout: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("is", $userId, $userName);
            $result = $stmt->execute();
            error_log("ActivityLogger::logLogout execute result: " . ($result ? "true" : "false"));
            if (!$result) {
                error_log("logLogout execute failed: " . $stmt->error);
            }
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error logging logout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build detailed activity description from components
     */
    private static function buildActivityDescription($activityType, $description, $requestType = null, $requestRefNo = null, $requestStatus = null) {
        $parts = [$description];
        
        if ($requestType) {
            $parts[] = "Type: " . $requestType;
        }
        if ($requestRefNo) {
            $parts[] = "Ref: " . $requestRefNo;
        }
        if ($requestStatus) {
            $parts[] = "Status: " . $requestStatus;
        }
        
        return implode(" | ", $parts);
    }

    /**
     * Get user name from session or database
     */
    private static function getUserName($userId) {
        try {
            // First try to get from session (faster)
            if (isset($_SESSION['fullname']) && !empty($_SESSION['fullname'])) {
                return $_SESSION['fullname'];
            }
            
            // Fallback to database query
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT CONCAT(IFNULL(Firstname, ''), ' ', IFNULL(Lastname, '')) FROM userloginfo WHERE UserID = ? LIMIT 1");
            
            if (!$stmt) {
                error_log("Prepare failed in getUserName: " . $conn->error);
                return 'Unknown User';
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_array(MYSQLI_NUM);
            $stmt->close();
            
            return ($row && !empty(trim($row[0]))) ? trim($row[0]) : 'Unknown User';
        } catch (Exception $e) {
            error_log("Error getting user name: " . $e->getMessage());
            return 'Unknown User';
        }
    }

    /**
     * Retrieve activity logs with optional filters
     */
    public static function getActivityLogs($filters = []) {
        try {
            $conn = getDBConnection();
            
            $query = "SELECT * FROM user_activity_reports WHERE 1=1";
            $params = [];
            $types = "";
            
            // Filter by user
            if (!empty($filters['user_id'])) {
                $query .= " AND UserId = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            // Filter by username
            if (!empty($filters['user_name'])) {
                $query .= " AND UserName LIKE ?";
                $searchTerm = "%" . $filters['user_name'] . "%";
                $params[] = $searchTerm;
                $types .= "s";
            }
            
            // Filter by date range
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(ActivityDate) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(ActivityDate) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }
            
            // Search in activity description
            if (!empty($filters['search'])) {
                $query .= " AND Activity LIKE ?";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $types .= "s";
            }
            
            // Pagination
            $limit = $filters['limit'] ?? 50;
            $offset = $filters['offset'] ?? 0;
            
            $query .= " ORDER BY ActivityDate DESC LIMIT ? OFFSET ?";
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
            
            $query = "SELECT COUNT(*) as total FROM user_activity_reports WHERE 1=1";
            $params = [];
            $types = "";
            
            // Apply same filters as getActivityLogs
            if (!empty($filters['user_id'])) {
                $query .= " AND UserId = ?";
                $params[] = $filters['user_id'];
                $types .= "i";
            }
            
            if (!empty($filters['user_name'])) {
                $query .= " AND UserName LIKE ?";
                $searchTerm = "%" . $filters['user_name'] . "%";
                $params[] = $searchTerm;
                $types .= "s";
            }
            
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(ActivityDate) >= ?";
                $params[] = $filters['date_from'];
                $types .= "s";
            }
            
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(ActivityDate) <= ?";
                $params[] = $filters['date_to'];
                $types .= "s";
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND Activity LIKE ?";
                $searchTerm = "%" . $filters['search'] . "%";
                $params[] = $searchTerm;
                $types .= "s";
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
            $stmt = $conn->prepare("DELETE FROM user_activity_reports WHERE ActivityDate < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error clearing old logs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activity summary for a specific user
     */
    public static function getUserActivitySummary($userId) {
        try {
            $conn = getDBConnection();
            
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_activities,
                    MAX(ActivityDate) as last_activity,
                    COUNT(CASE WHEN Activity LIKE '%logged in%' THEN 1 END) as login_count,
                    COUNT(CASE WHEN Activity LIKE '%logged out%' THEN 1 END) as logout_count
                FROM user_activity_reports 
                WHERE UserId = ?
            ");
            
            if (!$stmt) {
                error_log("Prepare failed in getUserActivitySummary: " . $conn->error);
                return null;
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $summary = $result->fetch_assoc();
            $stmt->close();
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting user activity summary: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Helper function for quick logging
 * Usage: logActivity(ActivityLogger::LOGIN, 'User logged in')
 * Usage: logActivity(ActivityLogger::DOCUMENT_REQUEST, 'Document request submitted', 'No Birth Certificate', 'REF123', 'Pending')
 */
if (!function_exists('logActivity')) {
    function logActivity($activityType, $description, $requestType = null, $requestRefNo = null, $requestStatus = null, $adminAction = null) {
        if (!isset($_SESSION['user_id'])) {
            error_log("logActivity: user_id not in session. Description: " . $description);
            return false;
        }
        
        error_log("logActivity: Logging activity for user ID: " . $_SESSION['user_id'] . ", Activity: " . $description);
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

/**
 * Helper function for login logging
 * Usage: logActivityLogin()
 */
if (!function_exists('logActivityLogin')) {
    function logActivityLogin() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        return ActivityLogger::logLogin($_SESSION['user_id']);
    }
}

/**
 * Helper function for logout logging
 * Usage: logActivityLogout()
 */
if (!function_exists('logActivityLogout')) {
    function logActivityLogout() {
        if (!isset($_SESSION['user_id'])) {
            error_log("logActivityLogout: user_id not in session");
            return false;
        }
        
        error_log("logActivityLogout: Logging logout for user ID: " . $_SESSION['user_id']);
        $result = ActivityLogger::logLogout($_SESSION['user_id']);
        error_log("logActivityLogout: Result = " . ($result ? "true" : "false"));
        return $result;
    }
}

?>
