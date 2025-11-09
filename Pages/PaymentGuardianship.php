<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic sanitization (optional, since prepared statements are used)
    $refno    = trim($_POST['refno'] ?? '');
    $applicant_name     = trim($_POST['applicant_name'] ?? '');
    $request_type = trim($_POST['request_type'] ?? '');
    $request_date  = trim($_POST['request_date'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);

     // ✅ Get finance user's name if logged in
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'finance') {
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Finance User';
    } else {
        // fallback if not finance
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Unknown User';
    }

    // Validate required fields
    if (!$refno || !$applicant_name || !$request_type || !$amount) {
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
        INSERT INTO tblpayment 
        (refno, name, type, date, amount, RequestStatus, PaymentReceivedBy) 
        VALUES (?, ?, ?, ?, ?, 'Paid', ?)
    ");

    if (!$stmt) {
        http_response_code(500);
        echo "Failed to prepare statement.";
        $connection->close();
        exit;
    }

    $stmt->bind_param("ssssds", $refno, $applicant_name, $request_type, $request_date, $amount, $paymentReceivedBy);

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
