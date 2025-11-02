<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "barangayDb";

$connection = new mysqli($servername, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$sql = "SELECT SUM(amount) AS totalApproved FROM tblpayment WHERE RequestStatus = 'Approved'";
$result = $connection->query($sql);

$totalApproved = 0;

if ($result && $row = $result->fetch_assoc()) {
    $totalApproved = $row['totalApproved'] ?? 0;
}

$connection->close();
?>
