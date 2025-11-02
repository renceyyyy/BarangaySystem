<?php
// approve.php

// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $UserID = intval($_GET['id']);

    // Update status
    $sql = "UPDATE userloginfo SET AccountStatus = 'verified' WHERE UserID = $UserID";
    
    if ($connection->query($sql) === TRUE) {
        // Redirect back after approval
header("Location: Adminpage.php?message=approved&&panel=residencePanel");
        exit();
    } else {
        echo "Error updating record: " . $connection->error;
    }
} else {
    echo "No request ID provided.";
}

$connection->close();
?>
