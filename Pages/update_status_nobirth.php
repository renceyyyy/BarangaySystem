<?php
session_start();
require_once '../Process/db_connection.php';

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    die("Missing required parameters");
}

$id = $_POST['id'];
$status = $_POST['status'];
$admin = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';

$connection = getDBConnection();

if ($status === "Released") {
    $sql = "UPDATE no_birthcert_tbl SET RequestStatus = ?, ReleasedBy = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $status, $admin, $id);
} else {
    $sql = "UPDATE no_birthcert_tbl SET RequestStatus = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $status, $id);
}

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error updating status: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>