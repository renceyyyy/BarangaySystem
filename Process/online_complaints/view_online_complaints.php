<?php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');

require_once '../db_connection.php';

$conn = getDBConnection();

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$cmpId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$cmpId) {
    echo json_encode(['error' => 'Invalid complaint ID']);
    exit;
}

// Fetch complaint details with complainant info from userloginfo
$sql = "SELECT c.CmpID, c.Firstname, c.Lastname, c.Middlename, c.Complain, c.Evidencepic, c.refno, 
        c.DateComplained, c.DateTimeofIncident, c.LocationofIncident, c.IncidentType, c.RequestStatus, c.Reason,
        u.Address, u.Age, u.ContactNo, u.Email
        FROM complaintbl c 
        LEFT JOIN userloginfo u ON c.UserId = u.UserID 
        WHERE c.CmpID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cmpId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Complaint not found']);
    exit;
}

$complaint = $result->fetch_assoc();

// Convert blob to base64 if evidence exists
$evidenceData = null;
if ($complaint['Evidencepic']) {
    $evidenceData = base64_encode($complaint['Evidencepic']);
}

echo json_encode([
    'success' => true,
    'complaint' => [
        'CmpID' => $complaint['CmpID'],
        'Firstname' => $complaint['Firstname'],
        'Lastname' => $complaint['Lastname'],
        'Middlename' => $complaint['Middlename'],
        'Complain' => $complaint['Complain'],
        'refno' => $complaint['refno'],
        'DateComplained' => $complaint['DateComplained'],
        'DateTimeofIncident' => $complaint['DateTimeofIncident'],
        'LocationofIncident' => $complaint['LocationofIncident'],
        'IncidentType' => $complaint['IncidentType'],
        'RequestStatus' => $complaint['RequestStatus'],
        'Reason' => $complaint['Reason'],
        'EvidencePic' => $evidenceData,
        'Address' => $complaint['Address'],
        'Age' => $complaint['Age'],
        'ContactNo' => $complaint['ContactNo'],
        'Email' => $complaint['Email']
    ]
]);

$stmt->close();
$conn->close();
?>