<?php
// Set staff session name BEFORE starting session
session_name('BarangayStaffSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic sanitization
    $refno    = trim($_POST['refno'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $docutype = trim($_POST['docutype'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);
    $DateRequested = date('Y-m-d H:i:s');

    // ✅ Get finance user's name if logged in
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'finance') {
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Finance User';
    } else {
        // fallback if not finance
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Unknown User';
    }

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

    
    // set payment received timestamp (when the record is created)
    $paymentDateReceived = date('Y-m-d H:i:s');

    // ✅ Insert payment record (include PaymentDateReceived)
    $stmt = $connection->prepare("
        INSERT INTO tblpayment 
            (refno, name, type, address, date, amount, PaymentDateReceived, RequestStatus, PaymentReceivedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Paid', ?)
    ");

    if (!$stmt) {
        http_response_code(500);
        echo "Failed to prepare statement.";
        $connection->close();
        exit;
    }

    $stmt->bind_param("sssssdss", $refno, $name, $docutype, $address, $DateRequested, $amount, $paymentDateReceived, $paymentReceivedBy);

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
