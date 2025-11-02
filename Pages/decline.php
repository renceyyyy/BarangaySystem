<?php
// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $reqId = intval($_GET['id']);

    $stmt = $connection->prepare("UPDATE docsreqtbl SET RequestStatus = 'Declined', Reason =? WHERE ReqId = ?");
    $stmt->bind_param("si", $reason, $reqId);
    $reason = isset($_GET['reason']) ? db_escape(urldecode($_GET['reason'])) : '';

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            $urlParams = parse_url($referrer, PHP_URL_QUERY) ?? '';
            $redirectUrl = 'Adminpage.php';
            if (!empty($urlParams)) {
                $redirectUrl .= '?' . $urlParams;
            }
            $redirectUrl .= (strpos($redirectUrl, '?') === false ? '?' : '&') . 'message=declined&panel=governmentDocumentPanel';
            header("Location: " . $redirectUrl);
            exit();
        } else {
            echo "Error updating record: " . $connection->error;
        }
    }
} else {
    echo "No request ID provided.";
}

$connection->close();
?>
