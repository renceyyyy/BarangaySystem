<?php
/**
 * Blotter Validation Module
 * 
 * This module provides functions to check if a user has records in the blotter
 * and prevent them from accessing services until validated by the barangay.
 */

/**
 * Check if user has a blotter record
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID to check
 * @return array ['has_blotter' => bool, 'blotter_record' => array|null]
 */
function checkUserBlotterRecord($conn, $user_id)
{
    try {
        // Check in blotter_participantstbl for any records where the user is a participant
        // We check if the user's name matches participant records
        
        $sql = "SELECT u.FirstName, u.LastName, u.MiddleName 
                FROM userloginfo u 
                WHERE u.UserID = ? 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ['has_blotter' => false, 'blotter_record' => null];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['has_blotter' => false, 'blotter_record' => null];
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Now search for blotter records with matching names
        // Checking blotter_participantstbl for participants and blottertbl for reported_by
        $search_sql = "SELECT b.blotter_id, b.incident_type, b.blotter_details, b.status, b.created_at,
                              bp.participant_type, bp.firstname, bp.lastname
                       FROM blottertbl b
                       LEFT JOIN blotter_participantstbl bp ON b.blotter_id = bp.blotter_id
                       WHERE LOWER(CONCAT(bp.firstname, ' ', bp.lastname)) = LOWER(CONCAT(?, ' ', ?))
                       AND b.status = 'Active'
                       LIMIT 1";
        
        $search_stmt = $conn->prepare($search_sql);
        if (!$search_stmt) {
            return ['has_blotter' => false, 'blotter_record' => null];
        }
        
        $fullName = trim($user['FirstName'] . ' ' . $user['LastName']);
        $firstName = $user['FirstName'];
        $lastName = $user['LastName'];
        
        $search_stmt->bind_param("ss", $firstName, $lastName);
        $search_stmt->execute();
        $search_result = $search_stmt->get_result();
        
        if ($search_result->num_rows > 0) {
            $blotter = $search_result->fetch_assoc();
            $search_stmt->close();
            
            return [
                'has_blotter' => true,
                'blotter_record' => [
                    'blotter_id' => $blotter['blotter_id'],
                    'incident_type' => $blotter['incident_type'],
                    'blotter_details' => $blotter['blotter_details'],
                    'status' => $blotter['status'],
                    'created_at' => $blotter['created_at'],
                    'participant_type' => $blotter['participant_type']
                ]
            ];
        }
        
        $search_stmt->close();
        return ['has_blotter' => false, 'blotter_record' => null];
        
    } catch (Exception $e) {
        error_log("Blotter validation error: " . $e->getMessage());
        return ['has_blotter' => false, 'blotter_record' => null];
    }
}

/**
 * Store blotter status in session
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 */
function refreshBlotterStatus($conn, $user_id)
{
    $blotter_check = checkUserBlotterRecord($conn, $user_id);
    
    if ($blotter_check['has_blotter']) {
        $_SESSION['has_blotter_record'] = true;
        $_SESSION['blotter_record'] = $blotter_check['blotter_record'];
    } else {
        $_SESSION['has_blotter_record'] = false;
        unset($_SESSION['blotter_record']);
    }
}

/**
 * Get blotter block message for display
 * 
 * @return string HTML message
 */
function getBlotterBlockMessage()
{
    $message = '<strong style="color: #c62828;">⚠️ Account Restricted</strong><br>';
    $message .= 'Your account has an active record in the barangay blotter. ';
    $message .= 'You cannot access services at this time.<br><br>';
    $message .= '<strong>Please visit the barangay office to validate your account and resolve this matter.</strong>';
    
    return $message;
}

?>
