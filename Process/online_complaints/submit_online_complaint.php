<?php
session_name("BarangayStaffSession");
session_start();

date_default_timezone_set('Asia/Manila');
require_once "../db_connection.php";
$conn = getDBConnection();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../Pages/Adminpage.php");
    exit;
}

// required fields
$required = ['lastname', 'firstname', 'incident_datetime', 'age', 'address', 'location', 'incident_type', 'description'];
$errors = [];
foreach ($required as $f) {
    if (empty(trim($_POST[$f] ?? ''))) $errors[] = "$f is required";
}



// handle incident_type -> other_incident_type
$incident_type = trim($_POST['incident_type'] ?? '');
if ($incident_type === 'Other') {
    $other = trim($_POST['other_incident_type'] ?? '');
    if ($other === '') $errors[] = "Please specify incident type when 'Other' selected";
    else $incident_type = $other;
}

if (!empty($errors)) {
    // simple error response, redirect back with message
    $_SESSION['online_complaint_errors'] = $errors;
    header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
    exit;
}

// prepare fields
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$middlename = trim($_POST['middlename'] ?? '') ?: null;
$age = trim($_POST['age']);
$address = trim($_POST['address']);
$contact_no = trim($_POST['contact_no']??'') ?: null;
$email = trim($_POST['email']??'') ?: null;
$description = trim($_POST['description']);
$incident_datetime = trim($_POST['incident_datetime'] ?? '');
if ($incident_datetime !== '') {
    // convert "YYYY-MM-DDTHH:MM" or "YYYY-MM-DDTHH:MM:SS" -> "YYYY-MM-DD HH:MM:SS"
    $incident_datetime = str_replace('T', ' ', $incident_datetime);
    if (!preg_match('/:\d{2}$/', $incident_datetime)) {
        $incident_datetime .= ':00';
    }
}

$location = trim($_POST['location']);
$refno = (int)(date('Ymd') . rand(1000, 9999));

// optional file handling
$evidenceData = null;
if (!empty($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
    $allowed = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $type = mime_content_type($_FILES['evidence_file']['tmp_name']);
    if (!in_array($type, $allowed)) {
        $_SESSION['online_complaint_errors'] = ['Uploaded file type not allowed.'];
        header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
        exit;
    }
    if ($_FILES['evidence_file']['size'] > 5 * 1024 * 1024) {
        $_SESSION['online_complaint_errors'] = ['Uploaded file exceeds 5MB limit.'];
        header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
        exit;
    }
    $evidenceData = file_get_contents($_FILES['evidence_file']['tmp_name']);
}

// insert to complaintbl
if ($evidenceData !== null) {
    $sql = "INSERT INTO complaintbl (Firstname, Lastname, Middlename, age, address, contact_no, email, Complain, Evidencepic, refno, DateTimeofIncident, LocationofIncident, IncidentType)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("DB prepare error: " . $conn->error);
        $_SESSION['online_complaint_errors'] = ['Database error'];
        header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
        exit;
    }
    // bind params; pass NULL placeholder for blob then send_long_data for the actual bytes
    $null = null;
    $stmt->bind_param(
        "sssissssbisss",
        $firstname,
        $lastname, 
        $middlename,      
        $age,
        $address,
        $contact_no,
        $email,
        $description,
        $null,          // evidence placeholder, will be sent via send_long_data
        $refno,
        $incident_datetime,
        $location,
        $incident_type
    );
    // send blob (5th param index = 8)
    $stmt->send_long_data(8, $evidenceData);
    $ok = $stmt->execute();
    $stmt->close();
} else {
    $sql = "INSERT INTO complaintbl (Firstname, Lastname, Middlename, age, address, contact_no, email, Complain, refno, DateTimeofIncident, LocationofIncident, IncidentType)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("DB prepare error: " . $conn->error);
        $_SESSION['online_complaint_errors'] = ['Database error'];
        header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
        exit;
    }
    // NOTE: DateTimeofIncident must be bound as string (s) — previously used 'i' which caused 0000-00-00 00:00:00
    $stmt->bind_param(
        "sssissssisss",
        $firstname,
        $lastname,
        $middlename,
        $age,
        $address,
        $contact_no,
        $email,
        $description,
        $refno,
        $incident_datetime,
        $location,
        $incident_type
    );
    $ok = $stmt->execute();
    $stmt->close();
}

if ($ok) {
    // success
    $_SESSION['online_complaint_success'] = "Complaint created (refno: $refno).";
    header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
    exit;
} else {
    error_log("DB insert error: " . $conn->error);
    $_SESSION['online_complaint_errors'] = ['Failed to save complaint.'];
    header("Location: ../../Pages/Adminpage.php?panel=onlineComplaintsPanel");
    exit;
}
