<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Process/db_connection.php';

$connection = getDBConnection();
if ($connection->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Handle POST request for updating, deleting, or deactivating user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['UserID'])) {
        echo json_encode(['error' => 'Invalid request data']);
        exit;
    }
    
    $id = (int) $data['UserID'];
    
    // Handle DELETE action
    if (isset($data['action']) && $data['action'] === 'delete') {
        $stmt = $connection->prepare("DELETE FROM userloginfo WHERE UserID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Delete failed: ' . $stmt->error]);
        }
        
        $stmt->close();
        $connection->close();
        exit;
    }
    
    // Handle DEACTIVATE action
    if (isset($data['action']) && $data['action'] === 'deactivate') {
        $stmt = $connection->prepare("UPDATE userloginfo SET AccountStatus = 'Unverified' WHERE UserID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Account deactivated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Deactivation failed: ' . $stmt->error]);
        }
        
        $stmt->close();
        $connection->close();
        exit;
    }
    
    // Handle UPDATE action
    $stmt = $connection->prepare("
        UPDATE userloginfo SET 
            Firstname = ?,
            Lastname = ?,
            Middlename = ?,
            Birthdate = ?,
            Gender = ?,
            ContactNo = ?,
            Email = ?,
            Birthplace = ?,
            Address = ?,
            CivilStatus = ?,
            Nationality = ?
        WHERE UserID = ?
    ");
    
    $stmt->bind_param("sssssssssssi", 
        $data['Firstname'],
        $data['Lastname'],
        $data['Middlename'],
        $data['Birthdate'],
        $data['Gender'],
        $data['ContactNo'],
        $data['Email'],
        $data['Birthplace'],
        $data['Address'],
        $data['CivilStatus'],
        $data['Nationality'],
        $id
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    $connection->close();
    exit;
}

// Handle GET request for fetching user data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

$id = (int) $_GET['id'];

$stmt = $connection->prepare("
    SELECT UserID, Firstname, Lastname, Middlename, Birthdate, Gender, ContactNo, 
           Email, Birthplace, Address, CivilStatus, Nationality, ValidID, AccountStatus
    FROM userloginfo
    WHERE UserID = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found']);
    $stmt->close();
    $connection->close();
    exit;
}

$row = $result->fetch_assoc();

// Convert ValidID to proper base64 data URL or file path
if (!empty($row['ValidID'])) {
    // Check if it's already a base64 data URL
    if (strpos($row['ValidID'], 'data:image') === 0) {
        // Already a data URL, keep as is
        // Do nothing
    } else {
        // It's a file path - convert to accessible URL
        $filePath = str_replace('\\', '/', $row['ValidID']);
        
        // Check if the path is relative or absolute
        if (strpos($filePath, 'uploads/') !== false) {
            // Extract the part after 'uploads/'
            $relativePath = substr($filePath, strpos($filePath, 'uploads/'));
            $row['ValidID'] = '../' . $relativePath;
        } else {
            // Try to read the file and convert to base64
            $fullPath = '../uploads/valid_ids/' . basename($filePath);
            if (file_exists($fullPath)) {
                $imageData = file_get_contents($fullPath);
                $base64 = base64_encode($imageData);
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $fullPath);
                finfo_close($finfo);
                $row['ValidID'] = 'data:' . $mimeType . ';base64,' . $base64;
            } else {
                $row['ValidID'] = null;
            }
        }
    }
} else {
    $row['ValidID'] = null;
}

echo json_encode(['data' => $row], JSON_PRETTY_PRINT);

$stmt->close();
$connection->close();
?>