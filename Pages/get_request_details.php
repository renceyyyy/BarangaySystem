<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "barangayDb");
if ($conn->connect_error) {
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];
  $stmt = $conn->prepare("SELECT * FROM tblitemrequest WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'Request not found']);
  }
  $stmt->close();
} else {
  echo json_encode(['error' => 'Invalid request']);
}
$conn->close();
?>