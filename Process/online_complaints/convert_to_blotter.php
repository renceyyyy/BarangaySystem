<?php
// filepath: d:\xampp\htdocs\BarangaySampaguita\BarangaySystem\Process\online_complaints\convert_to_blotter.php
session_name('BarangayStaffSession');
session_start();
header('Content-Type: application/json');

require_once '../db_connection.php';
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$complaint_id = isset($input['complaint_id']) ? intval($input['complaint_id']) : 0;
$accused = $input['accused'] ?? [];
$witnesses = $input['witnesses'] ?? [];

if (!$complaint_id) {
    echo json_encode(['success' => false, 'error' => 'Missing complaint ID']);
    exit;
}

if (empty($accused)) {
    echo json_encode(['success' => false, 'error' => 'At least one respondent is required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Fetch complaint details
    $sql = "SELECT c.*, u.Address, u.Age, u.ContactNo, u.Email 
            FROM complaintbl c 
            LEFT JOIN userloginfo u ON c.UserId = u.UserID 
            WHERE c.CmpID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Complaint not found');
    }

    $complaint = $result->fetch_assoc();
    $stmt->close();

    // 2. Generate blotter ID
    // Use year-based format (e.g., 2025-001)
    function generateBlotterId($conn)
    {
        $year = date('Y');
        $prefix = $year . '-';
        $sql = "SELECT blotter_id FROM blottertbl WHERE blotter_id LIKE ? ORDER BY blotter_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $like = $prefix . '%';
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $last_id = null;
        $stmt->bind_result($last_id);
        $stmt->fetch();
        $stmt->close();

        if ($last_id) {
            $last_seq = intval(substr($last_id, strlen($prefix)));
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }
        return $prefix . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
    }

    $blotter_id = generateBlotterId($conn);


    // 3. Insert blotter record
    $officer_on_duty = $_SESSION['fullname'] ?? 'Admin';
    $reported_by = trim($complaint['Firstname'] . ' ' . $complaint['Middlename'] . ' ' . $complaint['Lastname']);
    $datetime_of_incident = $complaint['DateTimeofIncident'] ?? date('Y-m-d H:i:s');
    $location_of_incident = $complaint['LocationofIncident'] ?? '';
    $incident_type = $complaint['IncidentType'] ?? '';
    $blotter_details = $complaint['Complain'] ?? '';
    $status = 'Active';

    $sql = "INSERT INTO blottertbl 
            (blotter_id, officer_on_duty, reported_by, datetime_of_incident, location_of_incident, incident_type, blotter_details, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $blotter_id, $officer_on_duty, $reported_by, $datetime_of_incident, $location_of_incident, $incident_type, $blotter_details, $status);
    $stmt->execute();
    $stmt->close();

    // 4. Helper function to generate participant ID
    function generateParticipantId($conn, $blotter_id)
    {
        $prefix = "PTCP-$blotter_id-";
        $sql = "SELECT blotter_participant_id FROM blotter_participantstbl WHERE blotter_participant_id LIKE ? ORDER BY blotter_participant_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $like = $prefix . '%';
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $last_id = null;
        $stmt->bind_result($last_id);
        $stmt->fetch();
        $stmt->close();

        if ($last_id) {
            $last_seq = intval(substr($last_id, -3));
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }
        return $prefix . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
    }

    // 5. Insert complainant (from complaint data)
    $participant_id = generateParticipantId($conn, $blotter_id);
    $sql = "INSERT INTO blotter_participantstbl 
            (blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, address, age, contact_no, email, created_at) 
            VALUES (?, ?, 'complainant', ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $age = !empty($complaint['Age']) ? intval($complaint['Age']) : null;
    $contact = !empty($complaint['ContactNo']) ? $complaint['ContactNo'] : null;
    $stmt->bind_param(
        "ssssssiis",
        $participant_id,
        $blotter_id,
        $complaint['Lastname'],
        $complaint['Firstname'],
        $complaint['Middlename'],
        $complaint['Address'],
        $age,
        $contact,
        $complaint['Email']
    );
    $stmt->execute();
    $stmt->close();

    // 6. Insert accused/respondents

    foreach ($accused as $idx => $acc) {
        $acc_lastname = trim($acc['lastname'] ?? '');
        $acc_firstname = trim($acc['firstname'] ?? '');
        $acc_address = trim($acc['address'] ?? '');
        $acc_age = isset($acc['age']) ? intval($acc['age']) : 0;

        if ($acc_lastname === '' || $acc_firstname === '' || $acc_address === '' || $acc_age <= 0) {
            throw new Exception("Respondent #" . ($idx + 1) . " requires Lastname, Firstname, Address and valid Age (>=1).");
        }
    }

    foreach ($accused as $acc) {
        $participant_id = generateParticipantId($conn, $blotter_id);
        $sql = "INSERT INTO blotter_participantstbl 
            (blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, alias, address, age, contact_no, email, created_at) 
            VALUES (?, ?, 'accused', ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $age = intval($acc['age']);
        $contact = !empty($acc['contact_no']) ? $acc['contact_no'] : null;
        $stmt->bind_param(
            "ssssssiiss",
            $participant_id,
            $blotter_id,
            $acc['lastname'],
            $acc['firstname'],
            $acc['middlename'],
            $acc['alias'],
            $acc['address'],
            $age,
            $contact,
            $acc['email']
        );
        $stmt->execute();
        $stmt->close();
    }

    // 7. Insert witnesses (if any)
    foreach ($witnesses as $wit) {
        if (empty($wit['lastname']) && empty($wit['firstname'])) continue; // Skip empty witnesses

        $participant_id = generateParticipantId($conn, $blotter_id);
        $sql = "INSERT INTO blotter_participantstbl 
                (blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, address, age, contact_no, email, created_at) 
                VALUES (?, ?, 'witness', ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $age = !empty($wit['age']) ? intval($wit['age']) : null;
        $contact = !empty($wit['contact_no']) ? $wit['contact_no'] : null;
        $stmt->bind_param(
            "ssssssiis",
            $participant_id,
            $blotter_id,
            $wit['lastname'],
            $wit['firstname'],
            $wit['middlename'],
            $wit['address'],
            $age,
            $contact,
            $wit['email']
        );
        $stmt->execute();
        $stmt->close();
    }

    // 8. Save evidence image to uploads folder if exists
    if (!empty($complaint['Evidencepic'])) {
        $uploadDir = __DIR__ . '/../../uploads/blotters/' . $blotter_id . '/';
        $relativeDir = 'uploads/blotters/' . $blotter_id . '/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = 'complaint_evidence_' . $complaint_id . '.jpg';
        $filepath = $uploadDir . $filename;
        $dbFilePath = $relativeDir . $filename;

        // Write blob to file
        file_put_contents($filepath, $complaint['Evidencepic']);

        // Insert file record
        $date = date('Ymd');
        $file_prefix = "FILE-$date-";
        $sql = "SELECT file_id FROM blotter_filestbl WHERE file_id LIKE ? ORDER BY file_id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $like = $file_prefix . '%';
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $stmt->bind_result($last_file_id);
        $stmt->fetch();
        $stmt->close();

        if ($last_file_id) {
            $last_seq = intval(substr($last_file_id, -3));
            $next_seq = $last_seq + 1;
        } else {
            $next_seq = 1;
        }
        $file_id = $file_prefix . str_pad($next_seq, 3, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO blotter_filestbl (file_id, blotter_id, file_name, file_path, file_type) 
                VALUES (?, ?, ?, ?, 'image/jpeg')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $file_id, $blotter_id, $filename, $dbFilePath);
        $stmt->execute();
        $stmt->close();
    }

    // 9. Update complaint status to 'Approved' (converted)
    $sql = "UPDATE complaintbl SET RequestStatus = 'Approved', Reason = 'Converted to Blotter Report' WHERE CmpID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Complaint successfully converted to blotter report',
        'blotter_id' => $blotter_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
