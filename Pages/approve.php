<?php
// approve.php

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $reqId = intval($_GET['id']);

    // Use prepared statement for security
    $stmt = $connection->prepare("UPDATE docsreqtbl SET RequestStatus = 'Approved' WHERE ReqId = ?");
    $stmt->bind_param("i", $reqId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Get referrer URL to preserve search params (e.g., ?search_lastname=Smith)
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            $urlParams = parse_url($referrer, PHP_URL_QUERY) ?? '';
            $redirectUrl = 'Adminpage.php';
            if (!empty($urlParams)) {
                $redirectUrl .= '?' . $urlParams;
            }
            $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . 'message=approved&panel=governmentDocumentPanel';
            
            header("Location: " . $redirectUrl);
            exit();
        } else {
            echo "No record found to approve.";
        }
    } else {
        echo "Error updating record: " . $connection->error;
    }
    $stmt->close();
} else {
    echo "No request ID provided.";
}

$connection->close();
?>
