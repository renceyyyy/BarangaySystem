<?php
session_name('BarangayResidentSession');
session_start();

header('Content-Type: application/json');

require_once '../db_connection.php';
$conn = getDBConnection();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'pending' => []]);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Find complaints awaiting verification that match this user's details
// Match by: Firstname, Lastname, Middlename (flexible), and complaint is awaiting_verification
$sql = "SELECT 
            c.CmpID,
            c.refno,
            c.Firstname,
            c.Lastname,
            c.IncidentType,
            c.LocationofIncident,
            c.DateComplained,
            cl.Cmp_log_id,
            cl.performed_by,
            cl.brgy_solution_logs,
            cl.created_at as solution_date
        FROM complaintbl c
        INNER JOIN userloginfo u ON u.UserID = ?
        INNER JOIN complaint_logstbl cl ON cl.CmpID = c.CmpID AND cl.user_verification = 'pending'
        WHERE c.RequestStatus = 'awaiting_verification'
        AND LOWER(TRIM(c.Firstname)) = LOWER(TRIM(u.Firstname))
        AND LOWER(TRIM(c.Lastname)) = LOWER(TRIM(u.Lastname))
        AND (
            (c.Middlename IS NULL AND (u.Middlename IS NULL OR u.Middlename = '' OR u.Middlename = 'uncompleted'))
            OR LOWER(TRIM(COALESCE(c.Middlename, ''))) = LOWER(TRIM(COALESCE(u.Middlename, '')))
        )
        ORDER BY cl.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pending = [];
while ($row = $result->fetch_assoc()) {
    $pending[] = [
        'complaint_id' => $row['CmpID'],
        'log_id' => $row['Cmp_log_id'],
        'refno' => $row['refno'],
        'firstname' => $row['Firstname'],
        'lastname' => $row['Lastname'],
        'incident_type' => $row['IncidentType'],
        'location' => $row['LocationofIncident'],
        'date_complained' => $row['DateComplained'],
        'performed_by' => $row['performed_by'],
        'solution' => $row['brgy_solution_logs'],
        'solution_date' => $row['solution_date']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'pending' => $pending
]);
?>