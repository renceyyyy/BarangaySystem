<?php

require_once '../db_connection.php';
header('Content-Type: application/json');

$conn = getDBConnection();

$blotter_id = $_GET['id'] ?? '';
if (!$blotter_id) {
    echo json_encode(['error' => 'No blotter ID']);
    exit;
}

// Get blotter details
$sql = "SELECT * FROM blottertbl WHERE blotter_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$result = $stmt->get_result();
$blotter = $result->fetch_assoc();
$stmt->close();

if (!$blotter) {
    echo json_encode(['error' => 'Blotter not found']);
    exit;
}

// Get participants
$sql = "SELECT * FROM blotter_participantstbl WHERE blotter_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$res = $stmt->get_result();
$participants = [];
while ($row = $res->fetch_assoc()) {
    $participants[] = $row;
}
$stmt->close();


// Get ALL hearings (for history), ordered by hearing_no ascending // NEWLY ADDED
$sql = "SELECT * FROM blotter_hearingstbl WHERE blotter_id = ? ORDER BY hearing_no ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$res = $stmt->get_result();
$hearings = [];
while ($row = $res->fetch_assoc()) {
    $hearings[] = $row;
}
$stmt->close();
// Get latest hearing (for editing in details section, if needed)
$hearing = null;
if (!empty($hearings)) {
    $hearing = end($hearings); // Last element is the latest
}



// Get latest hearing (if any)
$sql = "SELECT * FROM blotter_hearingstbl WHERE blotter_id = ? ORDER BY hearing_no DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$res = $stmt->get_result();
$hearing = $res->fetch_assoc();
$stmt->close();



// getting uploaded files (BAGONG DAGDAG)
$sql = "SELECT * FROM blotter_filestbl WHERE blotter_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$res = $stmt->get_result();
$files = [];
while ($row = $res->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
$conn->close();






echo json_encode([
    'blotter' => $blotter,
    'participants' => $participants,
    'files' => $files, // BAGONG DAGDAG
    'hearing' => $hearing,
    'hearings' => $hearings // NEWLY ADDED
    

]);
?>