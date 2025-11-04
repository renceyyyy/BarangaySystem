<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Process/db_connection.php';

$connection = getDBConnection();
if ($connection->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Handle POST request for updating or deleting user
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

// Convert file path into accessible image URL
if (!empty($row['ValidID'])) {
    $filePath = str_replace('\\', '/', $row['ValidID']);
    $filename = basename($filePath);
    $row['ValidID'] = '/BarangaySampaguita/BarangaySystem/uploads/valid_ids/' . $filename;
} else {
    $row['ValidID'] = null;
}

echo json_encode(['data' => $row], JSON_PRETTY_PRINT);

$stmt->close();
$connection->close();
?>