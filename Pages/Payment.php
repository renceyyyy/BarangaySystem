<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $refno    = trim($_POST['refno'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $docutype = trim($_POST['docutype'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);
    $DateRequested = date('Y-m-d H:i:s');

    if (!$refno || !$name || !$docutype || !$amount) {
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

    // ✅ 1. Insert the payment record
    $stmt = $connection->prepare("
        INSERT INTO tblpayment (refno, name, type, address, date, amount, RequestStatus)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("sssssd", $refno, $name, $docutype, $address, $DateRequested, $amount);

    if ($stmt->execute()) {
        // ✅ 2. Update the related document request as 'Paid'
        $update = $connection->prepare("UPDATE docsreqtbl SET PaymentStatus = 'Paid' WHERE refno = ?");
        $update->bind_param("s", $refno);
        $update->execute();

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
