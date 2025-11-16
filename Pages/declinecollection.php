<?php
// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

// Get CollectionID from URL
if (isset($_GET['id'])) {
  $id = $connection->real_escape_string($_GET['id']);

  // Update the RequestStatus to 'Approved'
  $sql = "UPDATE tblpayment SET RequestStatus = 'Declined' WHERE CollectionID = '$id'";

  if ($connection->query($sql) === TRUE) {
    // Redirect back to the main page (adjust filename as needed)
header("Location: FinancePage.php?message=approved&panel=collectionPanel");
    exit();
  } else {
    echo "Error updating record: " . $connection->error;
  }
} else {
  echo "Invalid request.";
}

// Singleton connection - don't close
?>
