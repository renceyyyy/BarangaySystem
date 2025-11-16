<?php
session_name('BarangayStaffSession');
session_start();
require_once '../Process/db_connection.php';

// Only allow admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$connection = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    
    if (!$id || !$status) {
        http_response_code(400);
        echo "Missing required parameters";
        exit;
    }

    // Add ReleasedBy if status is Released
    $releasedBy = '';
    if ($status === 'Released') {
        $releasedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown';
    }

    // Use prepared statement to prevent SQL injection
    if ($status === 'Released') {
        $stmt = $connection->prepare("UPDATE no_birthcert_tbl SET RequestStatus = ?, ReleasedBy = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $releasedBy, $id);
    } else {
        $stmt = $connection->prepare("UPDATE no_birthcert_tbl SET RequestStatus = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to update status: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>