<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic sanitization (optional, since prepared statements are used)
    $refno    = trim($_POST['refno'] ?? '');
    $ownername     = trim($_POST['owner'] ?? '');
    $requesttype = trim($_POST['type'] ?? '');
    $requesteddate  = trim($_POST['date'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);

    // Validate required fields
    if (!$refno || !$ownername || !$requesttype || !$amount) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

 // Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

    if ($connection->connect_error) {
        http_response_code(500);
        echo "Database connection failed.";
        exit;
    }

    $stmt = $connection->prepare("
        INSERT INTO tblpayment (refno, name, type, date, amount, RequestStatus)
        VALUES (?, ?, ?, ?, ?, 'Pending')
    ");

    if (!$stmt) {
        http_response_code(500);
        echo "Failed to prepare statement.";
        $connection->close();
        exit;
    }

    $stmt->bind_param("ssssd", $refno, $ownername, $requesttype, $requesteddate, $amount);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to save payment.";
    }

    $stmt->close();
    $connection->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method.";
}
?>
