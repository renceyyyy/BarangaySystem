<?php
// Connect to database
$connection = new mysqli("localhost", "root", "", "barangaydb");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get the application ID and reason from URL parameters
$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';

if ($applicationId > 0 && !empty($reason)) {
    // Persist the rejection reason (column was added). Update status and RejectReason.
    $updateQuery = "UPDATE scholarship SET RequestStatus = 'Rejected', RejectReason = ? WHERE ApplicationID = ? AND RequestStatus IN ('Pending', 'For Examination')";
    $stmt = $connection->prepare($updateQuery);
    if (!$stmt) {
        header("Location: SKpage.php?error=database_prepare_failed");
        $connection->close();
        exit();
    }

    $stmt->bind_param("si", $reason, $applicationId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Success - redirect back to SK page with success message
            $stmt->close();
            $connection->close();
            header("Location: SKpage.php?success=rejected");
            exit();
        } else {
            // No rows affected - application not found or already processed
            $stmt->close();
            $connection->close();
            header("Location: SKpage.php?error=not_found");
            exit();
        }
    } else {
        // Database error executing update
        $stmt->close();
        $connection->close();
        header("Location: SKpage.php?error=database_error");
        exit();
    }

} else {
    // Invalid application ID or missing reason
    header("Location: SKpage.php?error=invalid_data");
    $connection->close();
    exit();
}

?>
