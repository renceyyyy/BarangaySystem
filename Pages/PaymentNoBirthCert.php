<?php
session_start();
require_once '../Process/db_connection.php';

// Only allow admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connection = getDBConnection();

    $refno = $_POST['refno'] ?? '';
    $requestor_name = $_POST['requestor_name'] ?? '';
    $DocuType = $_POST['DocuType'] ?? '';
    $request_date = $_POST['request_date'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $payment_date = date('Y-m-d H:i:s');
    $payment_status = 'Paid';

    // Use prepared statement to prevent SQL injection
    $stmt = $connection->prepare("INSERT INTO payments (refno, name, document_type, request_date, amount, payment_date, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssdss", $refno, $requestor_name, $DocuType, $request_date, $amount, $payment_date, $payment_status);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to record payment: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>