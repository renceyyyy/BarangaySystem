<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic sanitization
    $refno    = trim($_POST['refno'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $docutype = trim($_POST['docutype'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);
    $DateRequested = date('Y-m-d H:i:s');

    // ✅ Finance user's full name from session
    $paymentReceivedBy = $_SESSION['fullname'] ?? 'Unknown User';

    // Validate required fields
    if (!$refno || !$name || !$docutype) {
        http_response_code(400);
        echo "Missing required fields.";
        exit;
    }

    require_once '../Process/db_connection.php';
    $connection = getDBConnection();

    if ($connection->connect_error) {
        http_response_code(500);
        echo "Database connection failed.";
        exit;
    }

    // ✅ Insert with PaymentReceivedBy
    $stmt = $connection->prepare("
        INSERT INTO tblpayment 
            (refno, name, type, address, date, amount, RequestStatus, PaymentReceivedBy)
        VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?)
    ");

    if (!$stmt) {
        http_response_code(500);
        echo "Failed to prepare statement.";
        $connection->close();
        exit;
    }

    $stmt->bind_param("sssssds", $refno, $name, $docutype, $address, $DateRequested, $amount, $paymentReceivedBy);


    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to save payment.";
    }

    $stmt->close();
    $connection->close();
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
?>
