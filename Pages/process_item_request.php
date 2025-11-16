<?php
// process_item_request.php
// Handles AJAX submission for adding new item requests.

// Start session for CSRF and any session-based checks
session_start();

// Include your database connection (adjust path as needed)
require_once 'dashboard.php'; // Assumes $connection is defined here

// Set headers for JSON response and CORS (if needed for AJAX)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for security in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to log errors (for debugging; adjust to your logging system)
function logError($message) {
    error_log("process_item_request.php Error: " . $message);
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// Get and sanitize inputs
$residentName = sanitize($_POST['residentName'] ?? '');
$itemSelect = sanitize($_POST['itemSelect'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 0);
$purpose = sanitize($_POST['purpose'] ?? '');
$eventDatetime = sanitize($_POST['eventDatetime'] ?? '');

// Validation: Check required fields
if (empty($residentName) || empty($itemSelect) || $quantity <= 0 || empty($purpose) || empty($eventDatetime)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required and must be valid.']);
    exit;
}

// Validate date (must be in the future)
$eventTimestamp = strtotime($eventDatetime);
if (!$eventTimestamp || $eventTimestamp <= time()) {
    echo json_encode(['success' => false, 'message' => 'Event date and time must be in the future.']);
    exit;
}

// Check stock availability
try {
    // Get current inventory for the item
    $stmt = $connection->prepare("SELECT total_stock, on_loan FROM inventory WHERE item_name = ?");
    $stmt->bind_param("s", $itemSelect);
    $stmt->execute();
    $invResult = $stmt->get_result();
    if ($invResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Item not found in inventory.']);
        $stmt->close();
        exit;
    }
    $inventory = $invResult->fetch_assoc();
    $stmt->close();

    // Calculate reserved quantity for the same item and datetime (pending/approved/on loan)
    $stmt = $connection->prepare("SELECT SUM(quantity) AS reserved FROM tblitemrequest WHERE item = ? AND RequestStatus IN ('Pending', 'Approved', 'On Loan') AND event_datetime = ?");
    $stmt->bind_param("ss", $itemSelect, $eventDatetime);
    $stmt->execute();
    $reservedResult = $stmt->get_result();
    $reserved = (int)($reservedResult->fetch_assoc()['reserved'] ?? 0);
    $stmt->close();

    $available = $inventory['total_stock'] - $inventory['on_loan'] - $reserved;
    if ($quantity > $available) {
        echo json_encode(['success' => false, 'message' => "Request denied. Only $available $itemSelect(s) available for this date/time."]);
        exit;
    }

    // Insert the request
    $stmt = $connection->prepare("INSERT INTO tblitemrequest (name, Purpose, item, quantity, event_datetime, date, RequestStatus) VALUES (?, ?, ?, ?, ?, NOW(), 'Pending')");
    $stmt->bind_param("sssiss", $residentName, $purpose, $itemSelect, $quantity, $eventDatetime);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item request submitted successfully.']);
    } else {
        logError("DB Insert failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to submit request. Please try again.']);
    }
    $stmt->close();

} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

// Close connection if not handled globally
if (isset($connection)) {
    $connection->close();
}
?>
