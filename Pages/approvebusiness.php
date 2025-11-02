<?php
// approvebusiness.php

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $BsnssID = intval($_GET['id']);

    // Use prepared statement for security
    $stmt = $connection->prepare("UPDATE businesstbl SET RequestStatus = 'Approved' WHERE BsnssID = ?");
    $stmt->bind_param("i", $BsnssID);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Get referrer URL to preserve search params (e.g., ?search_refno=ABC)
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            parse_str(parse_url($referrer, PHP_URL_QUERY) ?? '', $urlParams);
            
            // Build redirect URL
            $redirectUrl = 'Adminpage.php';
            if (!empty($urlParams)) {
                // Preserve existing params but override/ensure panel
                $urlParams['panel'] = 'businessPermitPanel';
                $redirectUrl .= '?' . http_build_query($urlParams);
            } else {
                $redirectUrl .= '?panel=businessPermitPanel';
            }
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=approved';
            
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
