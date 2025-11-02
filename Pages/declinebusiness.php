<?php
// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

// Check if 'id' is provided
if (isset($_GET['id'])) {
    $BsnssID = intval($_GET['id']);
    $reason = isset($_GET['reason']) ? urldecode($_GET['reason']) : '';

    // Prepare the SQL update statement using the helper function
    $stmt = db_prepare("
        UPDATE businesstbl 
        SET RequestStatus = 'Declined', Reason = ? 
        WHERE BsnssID = ?
    ");

    if (!$stmt) {
        // Get the connection to check for errors
        $conn = getDBConnection();
        die("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("si", $reason, $BsnssID);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Handle redirect and preserve URL params if possible
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            $urlParams = parse_url($referrer, PHP_URL_QUERY) ?? '';
            $redirectUrl = 'Adminpage.php';

            if (!empty($urlParams)) {
                $redirectUrl .= '?' . $urlParams;
            }

            $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . 'message=declined&panel=BusinessPermitPanel';
            
            header("Location: $redirectUrl");
            exit();
        } else {
            echo "No record updated — check if the Business ID exists.";
        }
    } else {
        echo "Error executing update: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "No request ID provided.";
}

// Note: No need to manually close the connection here, as it's a singleton.
// If you want to close it explicitly (not recommended for every script), use:
// DatabaseConnection::getInstance()->closeConnection();
?>
