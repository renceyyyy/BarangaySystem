<?php
// Include the database connection file
require_once '../Process/db_connection.php';

// Get the database connection
$connection = getDBConnection();

if (isset($_GET['id'])) {
    $collectionId = intval($_GET['id']);

    // Step 1: Get the refno for this payment
    $stmt = $connection->prepare("SELECT refno FROM tblpayment WHERE CollectionID = ?");
    $stmt->bind_param("i", $collectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $refno = $row['refno'] ?? null;
    $stmt->close();

    if ($refno) {
        // Step 2: Update the tblpayment table (mark as Paid)
        $stmt2 = $connection->prepare("UPDATE tblpayment SET RequestStatus = 'Paid' WHERE CollectionID = ?");
        $stmt2->bind_param("i", $collectionId);
        $stmt2->execute();
        $stmt2->close();

        // Step 3: Update the docsreqtbl (mark PaymentStatus as Paid)
        $stmt3 = $connection->prepare("UPDATE docsreqtbl SET PaymentStatus = 'Paid' WHERE refno = ?");
        $stmt3->bind_param("s", $refno);
        $stmt3->execute();
        $stmt3->close();

        // Step 4: Redirect back with confirmation
        header("Location: FinancePage.php?message=paid&panel=collectionPanel");
        exit();
    } else {
        echo "Error: Reference number not found for this collection record.";
    }
} else {
    echo "Invalid request.";
}

$connection->close();
?>
