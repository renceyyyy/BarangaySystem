<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';

/**
 * Log user activity to the database
 * Excludes login and logout activities
 * 
 * @param string $activity The activity type/description
 * @param string $category The category of activity (e.g., 'document_request', 'profile_update', 'payment')
 * @param array $details Additional details about the activity (optional)
 * @return bool True if logged successfully, false otherwise
 */
function logUserActivity($activity, $category, $details = null)
{
    // Only log if user is logged in
    if (!isset($_SESSION['user_id'])) {
        error_log("DEBUG: User not logged in, cannot log activity");
        return false;
    }

    try {
        $conn = getDBConnection();
        if (!$conn) {
            error_log("DEBUG: Database connection failed");
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $detailsJson = $details ? json_encode($details) : null;

        error_log("DEBUG: Attempting to log activity - User: $userId, Activity: $activity, Category: $category");

        $sql = "INSERT INTO user_activity_logs (user_id, activity, category, details, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("ERROR: Prepare failed: " . $conn->error);
            return false;
        }

        // Bind 6 parameters: i=int (user_id), s=strings (activity, category, details, ip_address, user_agent)
        $bindResult = $stmt->bind_param("isssss", $userId, $activity, $category, $detailsJson, $ipAddress, $userAgent);
        
        if (!$bindResult) {
            error_log("ERROR: bind_param failed");
            return false;
        }
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("ERROR: Execute failed: " . $stmt->error);
        } else {
            $insertId = $stmt->insert_id;
            error_log("SUCCESS: Activity logged - ID: " . $insertId);
        }
        
        $stmt->close();

        return $result;
    } catch (Exception $e) {
        error_log("ERROR: Exception logging user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user activity logs from database
 * 
 * @param int $userId User ID to retrieve logs for
 * @param int $limit Maximum number of records to retrieve (default: 100)
 * @param int $offset Starting position (default: 0)
 * @return array Array of activity logs
 */
function getUserActivityLogs($userId, $limit = 100, $offset = 0)
{
    try {
        $conn = getDBConnection();
        
        $sql = "SELECT id, user_id, activity, category, details, ip_address, user_agent, timestamp 
                FROM user_activity_logs 
                WHERE user_id = ? 
                ORDER BY timestamp DESC 
                LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return [];
        }

        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        $stmt->close();
        return $logs;
    } catch (Exception $e) {
        error_log("Error retrieving user activity logs: " . $e->getMessage());
        return [];
    }
}

/**
 * Get activity count for a specific user
 * 
 * @param int $userId User ID
 * @return int Total activity count
 */
function getUserActivityCount($userId)
{
    try {
        $conn = getDBConnection();
        
        $sql = "SELECT COUNT(*) as count FROM user_activity_logs WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return 0;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error retrieving activity count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Delete old activity logs (for maintenance)
 * 
 * @param int $daysOld Delete logs older than this many days (default: 90)
 * @return bool True if deleted successfully
 */
function deleteOldActivityLogs($daysOld = 90)
{
    try {
        $conn = getDBConnection();
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysOld days"));

        $sql = "DELETE FROM user_activity_logs WHERE timestamp < ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }

        $stmt->bind_param("s", $cutoffDate);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    } catch (Exception $e) {
        error_log("Error deleting old activity logs: " . $e->getMessage());
        return false;
    }
}
?>
