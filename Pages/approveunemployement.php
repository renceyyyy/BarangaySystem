<?php
// approve.php

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Update status
    $stmt = $connection->prepare("UPDATE unemploymenttbl SET RequestStatus = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Get referrer URL to preserve search params (e.g., ?search_refno=ABC)
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            parse_str(parse_url($referrer, PHP_URL_QUERY) ?? '', $urlParams);
            
            // Build redirect URL
            $redirectUrl = 'Adminpage.php';
            if (!empty($urlParams)) {
                // Preserve existing params but override/ensure panel
                $urlParams['panel'] = 'businessUnemploymentCertificatePanel';
                $redirectUrl .= '?' . http_build_query($urlParams);
            } else {
                $redirectUrl .= '?panel=businessUnemploymentCertificatePanel';
            }
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=approved';
header("Location: ". $redirectUrl);
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
