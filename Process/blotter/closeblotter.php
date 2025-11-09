<?php
session_start();
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blotter_id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? 'closed';  // Default to 'closed' if no status provided
    if (!$blotter_id) {
        echo "Missing blotter ID";
        exit;
    }
    $conn = getDBConnection();
    if ($conn->connect_error) {
        echo "DB error";
        exit;
    }
    $sql = "UPDATE blottertbl SET status=?, closed_at=NOW() WHERE blotter_id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed";
        exit;
    }
    $stmt->bind_param("ss", $status, $blotter_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Update failed";
    }
    $stmt->close();
    // Singleton connection closed by PHP
} else {
    echo "Invalid request";
}
?>
