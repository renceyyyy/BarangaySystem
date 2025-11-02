<?php
// filepath: d:\xampp\htdocs\Capston\Capstones\Capstones\Process\blotter\close_blotter.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blotter_id = $_POST['id'] ?? '';
    if (!$blotter_id) {
        echo "Missing blotter ID";
        exit;
    }
    $conn = new mysqli("localhost", "root", "", "barangayDb");
    if ($conn->connect_error) {
        echo "DB error";
        exit;
    }
    $sql = "UPDATE blottertbl SET status='closed', closed_at=NOW() WHERE blotter_id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed";
        exit;
    }
    $stmt->bind_param("s", $blotter_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Update failed";
    }
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>