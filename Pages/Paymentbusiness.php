<?php
// Set staff session name BEFORE starting session
session_name('BarangayStaffSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic sanitization (optional, since prepared statements are used)
    $refno    = trim($_POST['refno'] ?? '');
    $ownername     = trim($_POST['owner'] ?? '');
    $requesttype = trim($_POST['type'] ?? '');
    $requesteddate  = trim($_POST['date'] ?? '');
    $amount   = floatval($_POST['amount'] ?? 0);

    // ✅ Get finance user's name if logged in
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'finance') {
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Finance User';
    } else {
        // fallback if not finance
        $paymentReceivedBy = $_SESSION['fullname'] ?? 'Unknown User';
    }


    // Validate required fields
    if (!$refno || !$ownername || !$requesttype || !$amount) {
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

    // Generate unique OR Number (Official Receipt Number)
    $orNumber = 'OR-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    // Check if OR Number already exists, regenerate if it does
    $checkStmt = $connection->prepare("SELECT ORNumber FROM tblpayment WHERE ORNumber = ?");
    if ($checkStmt) {
        $checkStmt->bind_param("s", $orNumber);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        // Keep generating until we get a unique OR Number
        while ($checkStmt->num_rows > 0) {
            $orNumber = 'OR-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $checkStmt->bind_param("s", $orNumber);
            $checkStmt->execute();
            $checkStmt->store_result();
        }
        $checkStmt->close();
    }

    $stmt = $connection->prepare("
        INSERT INTO tblpayment 
        (refno, name, type, date, amount, PaymentDateReceived, RequestStatus, PaymentReceivedBy, ORNumber)
        VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?, ?)
    ");

    if (!$stmt) {
        http_response_code(500);
        echo "Failed to prepare statement.";
        $connection->close();
        exit;
    }

    $stmt->bind_param("ssssdsss", $refno, $ownername, $requesttype, $requesteddate, $amount, $paymentDateReceived, $paymentReceivedBy, $orNumber);

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
