<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "barangayDb");

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch only verified residents (AccountStatus = 'Verified')
$query = "SELECT UserID, Firstname, Lastname, Middlename, Address 
          FROM userloginfo 
          WHERE AccountStatus = 'Verified' 
          ORDER BY Firstname, Lastname";

$result = $conn->query($query);

$residents = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $residents[] = [
            'UserID' => $row['UserID'],
            'Firstname' => $row['Firstname'],
            'Lastname' => $row['Lastname'],
            'Middlename' => $row['Middlename'] ?? '',
            'Address' => $row['Address'] ?? ''
        ];
    }
}

// Singleton connection - don't close

echo json_encode($residents);
?>
