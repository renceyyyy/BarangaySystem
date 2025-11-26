<?php
session_name("BarangayStaffSession");
session_start();
require_once '../db_connection.php';
$conn = getDBConnection();

header('Content-Type: application/json');

// Get the search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Prepare the search: Match against concatenated name (Firstname + Lastname + Middlename)
$searchTerm = '%' . $query . '%';
$sql = "SELECT UserID, Firstname, Lastname, Middlename, Age, Address, ContactNo, Email 
        FROM userloginfo 
        WHERE CONCAT(Firstname, ' ', Lastname, ' ', IFNULL(Middlename, '')) LIKE ? 
        AND AccountStatus = 'verified'  -- Optional: Only show verified users
        LIMIT 10";  // Limit results for performance

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$residents = [];
while ($row = $result->fetch_assoc()) {
    $residents[] = $row;
}

echo json_encode($residents);
$stmt->close();
$conn->close();
?>