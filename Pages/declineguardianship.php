<?php
// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
$reason = isset($_GET['reason']) ? $connection->real_escape_string(urldecode($_GET['reason'])) : '';

    // Update status and reason
    $sql = "UPDATE guardianshiptbl SET RequestStatus = 'Declined', Reason = '$reason' WHERE id = $id";
    
    if ($connection->query($sql) === TRUE) {
        header("Location: Adminpage.php?message=declined&panel=guardianshipPanel");
        exit();
    } else {
        echo "Error updating record: " . $connection->error;
    }
} else {
    echo "No request ID provided.";
}

$connection->close();
?>
