<?php
/**
 * Blotter User Linking Process
 * 
 * This module handles linking blotter records to user accounts when
 * admin processes a blotter with a name matching a resident account.
 */

header('Content-Type: application/json');
session_name('BarangayStaffSession');
session_start();

require_once '../db_connection.php';

// Verify admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SuperAdmin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

/**
 * Search for potential user matches for a blotter record
 */
if ($action === 'search_matches') {
    $blotter_id = isset($_POST['blotter_id']) ? trim($_POST['blotter_id']) : '';
    
    if (empty($blotter_id)) {
        echo json_encode(['success' => false, 'error' => 'Blotter ID required']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Get blotter participant details
    $sql = "SELECT DISTINCT 
            bp.firstname, 
            bp.lastname, 
            bp.blotter_id,
            b.blotter_id as main_blotter_id
            FROM blotter_participantstbl bp
            JOIN blottertbl b ON bp.blotter_id = b.blotter_id
            WHERE b.blotter_id = ?
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $blotter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Blotter record not found']);
        $stmt->close();
        exit;
    }
    
    $blotter = $result->fetch_assoc();
    $stmt->close();
    
    $firstName = $blotter['firstname'];
    $lastName = $blotter['lastname'];
    
    // Search for matching users
    $search_sql = "SELECT 
                    UserID, 
                    Firstname, 
                    Lastname, 
                    Email, 
                    ContactNo,
                    AccountStatus
                   FROM userloginfo
                   WHERE (
                       (LOWER(Firstname) = LOWER(?) AND LOWER(Lastname) = LOWER(?))
                       OR LOWER(CONCAT(Firstname, ' ', Lastname)) = LOWER(CONCAT(?, ' ', ?))
                   )
                   LIMIT 10";
    
    $search_stmt = $conn->prepare($search_sql);
    if (!$search_stmt) {
        echo json_encode(['success' => false, 'error' => 'Search error']);
        exit;
    }
    
    $search_stmt->bind_param("ssss", $firstName, $lastName, $firstName, $lastName);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
    
    $matches = [];
    while ($row = $search_result->fetch_assoc()) {
        $matches[] = [
            'user_id' => $row['UserID'],
            'name' => $row['Firstname'] . ' ' . $row['Lastname'],
            'email' => $row['Email'],
            'contact' => $row['ContactNo'],
            'status' => $row['AccountStatus']
        ];
    }
    
    $search_stmt->close();
    
    echo json_encode([
        'success' => true,
        'blotter_name' => $firstName . ' ' . $lastName,
        'blotter_id' => $blotter_id,
        'matches' => $matches
    ]);
    exit;
}

/**
 * Link a blotter record to a user account
 */
if ($action === 'link_blotter_to_user') {
    $blotter_id = isset($_POST['blotter_id']) ? trim($_POST['blotter_id']) : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $confirm = isset($_POST['confirm']) ? intval($_POST['confirm']) : 0;
    
    if (empty($blotter_id) || $user_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid blotter or user ID']);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Verify blotter exists and is active
        $blotter_check = "SELECT blotter_id, status FROM blottertbl WHERE blotter_id = ?";
        $stmt = $conn->prepare($blotter_check);
        $stmt->bind_param("s", $blotter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Blotter record not found");
        }
        
        $blotter = $result->fetch_assoc();
        if ($blotter['status'] !== 'Active') {
            throw new Exception("Blotter must be Active to link");
        }
        $stmt->close();
        
        // 2. Verify user exists
        $user_check = "SELECT UserID, Firstname, Lastname, AccountStatus FROM userloginfo WHERE UserID = ?";
        $stmt = $conn->prepare($user_check);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // 3. Create linking record
        $link_sql = "INSERT INTO blotter_user_links 
                    (blotter_id, user_id, linked_at, linked_by, confirmation_status) 
                    VALUES (?, ?, NOW(), ?, ?)";
        
        $linked_by = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'System';
        $status = $confirm === 1 ? 'confirmed' : 'pending';
        
        $stmt = $conn->prepare($link_sql);
        if (!$stmt) {
            // Try to create the table if it doesn't exist
            $create_table = "CREATE TABLE IF NOT EXISTS blotter_user_links (
                id INT AUTO_INCREMENT PRIMARY KEY,
                blotter_id VARCHAR(50) NOT NULL,
                user_id INT NOT NULL,
                linked_at TIMESTAMP,
                linked_by VARCHAR(100),
                confirmation_status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_link (blotter_id, user_id),
                FOREIGN KEY (blotter_id) REFERENCES blottertbl(blotter_id),
                FOREIGN KEY (user_id) REFERENCES userloginfo(UserID)
            )";
            
            if (!$conn->query($create_table)) {
                throw new Exception("Cannot create linking table: " . $conn->error);
            }
            
            $stmt = $conn->prepare($link_sql);
        }
        
        $stmt->bind_param("siss", $blotter_id, $user_id, $linked_by, $status);
        
        if (!$stmt->execute()) {
            // Check if link already exists
            if (strpos($stmt->error, 'Duplicate') !== false) {
                throw new Exception("This blotter is already linked to this user");
            }
            throw new Exception("Error creating link: " . $stmt->error);
        }
        
        $stmt->close();
        
        // 4. If confirmed, update user blotter session flag (next login will pick it up)
        if ($confirm === 1) {
            // Log activity
            $log_sql = "INSERT INTO activity_log (action, module, details, timestamp) 
                       VALUES (?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            if ($log_stmt) {
                $action_text = "Admin linked blotter $blotter_id to user $user_id";
                $module = "Blotter Management";
                $log_stmt->bind_param("sss", $action_text, $module, $action_text);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Blotter successfully linked to ' . $user['Firstname'] . ' ' . $user['Lastname'],
            'user_name' => $user['Firstname'] . ' ' . $user['Lastname'],
            'user_id' => $user_id,
            'status' => $status
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

/**
 * Get all blotters linked to a specific user
 */
if ($action === 'get_user_blotters') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        exit;
    }
    
    $conn = getDBConnection();
    
    $sql = "SELECT 
            bul.blotter_id,
            bul.confirmation_status,
            bul.linked_at,
            b.incident_type,
            b.blotter_details,
            b.status,
            b.created_at
            FROM blotter_user_links bul
            JOIN blottertbl b ON bul.blotter_id = b.blotter_id
            WHERE bul.user_id = ?
            ORDER BY bul.linked_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $blotters = [];
    while ($row = $result->fetch_assoc()) {
        $blotters[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'blotters' => $blotters
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>
