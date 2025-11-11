<?php
session_start();
require_once '../db_connection.php'; // Update this path to your DB connection file

$conn = getDBConnection();

function generateParticipantId($conn, $blotter_id)
{
    $prefix = "PTCP-$blotter_id-";
    $sql = "SELECT blotter_participant_id FROM blotter_participantstbl WHERE blotter_participant_id LIKE ? ORDER BY blotter_participant_id DESC LIMIT 1";
    $like = $prefix . '%';
    $stmt = $conn->prepare($sql);
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

function generateBlotterId($conn)
{
    $year = date('Y');
    $prefix = $year . '-';
    $sql = "SELECT blotter_id FROM blottertbl WHERE blotter_id LIKE ? ORDER BY blotter_id DESC LIMIT 1";
    $like = $prefix . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $last_id = null;
    $stmt->bind_result($last_id);
    $stmt->fetch();
    $stmt->close();

    if ($last_id) {
        // Extract the sequence number after the dash
        $last_seq = intval(substr($last_id, strlen($prefix)));
        $next_seq = $last_seq + 1;
    } else {
        $next_seq = 1;
    }
    return $prefix . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
}

//for generating file id
function generateFileId($conn)
{
    $last_id = null;
    $date = date('Ymd');
    $prefix = "FILE-$date-";
    $sql = "SELECT file_id FROM blotter_filestbl WHERE file_id LIKE ? ORDER BY file_id DESC LIMIT 1";
    $like = $prefix . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $like);
    $stmt->execute();
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check total file size limit (5MB)
    $maxTotalSize = 5 * 1024 * 1024; // 5MB in bytes
    $totalSize = 0;
    if (isset($_FILES['blotter_files']) && count($_FILES['blotter_files']['name']) > 0) {
        foreach ($_FILES['blotter_files']['size'] as $size) {
            $totalSize += $size;
        }
        if ($totalSize > $maxTotalSize) {
            echo "<script>alert('Total file size exceeds 5MB. Please select smaller files and try again.'); window.history.back();</script>";
            exit;
        }
    }

    // Get officer on duty from session (adjust key as needed)
    $officer_on_duty = $_SESSION['fullname'] ?? 'Unknown Officer';

    $sql_part = "INSERT INTO blotter_participantstbl (
    blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, alias, address, age, contact_no, email
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Build reported_by string from all complainants
    $reported_by_arr = [];
    if (!empty($_POST['complainant_lastname'])) {
        foreach ($_POST['complainant_lastname'] as $i => $c_lastname) {
            $c_firstname = $_POST['complainant_firstname'][$i] ?? '';
            $c_middlename = $_POST['complainant_middlename'][$i] ?? '';
            $name = trim($c_lastname) . ', ' . trim($c_firstname) . ' ' . trim($c_middlename);
            $reported_by_arr[] = $name;
        }
    }
    $reported_by = implode(', and ', $reported_by_arr);

    // Incident details
    $datetime_of_incident = $_POST['incident_datetime'] ?? date('Y-m-d H:i:s');
    $location_of_incident = trim($_POST['incident_location'] ?? '');
    $incident_type = trim($_POST['incident_type'] ?? '');
    if ($incident_type === 'Other') {
        $incident_type = trim($_POST['incident_type_other'] ?? '');
    }
    $blotter_details = trim($_POST['incident_description'] ?? '');

    // Generate unique blotter_id
    $blotter_id = generateBlotterId($conn);

    // Insert into blottertbl
    $sql = "INSERT INTO blottertbl (
        blotter_id, officer_on_duty, reported_by, datetime_of_incident, location_of_incident, incident_type, blotter_details, status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, 'active', NOW()
    )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssss",
        $blotter_id,
        $officer_on_duty,
        $reported_by,
        $datetime_of_incident,
        $location_of_incident,
        $incident_type,
        $blotter_details
    );
    $stmt->execute();

    // Insert complainant into blotter_participantstbl
    $blotter_participant_id = generateParticipantId($conn, $blotter_id);
    if (!empty($_POST['complainant_lastname'])) {
        foreach ($_POST['complainant_lastname'] as $i => $c_lastname) {
            $c_firstname = $_POST['complainant_firstname'][$i] ?? '';
            $c_middlename = $_POST['complainant_middlename'][$i] ?? '';
            $c_address = $_POST['complainant_address'][$i] ?? '';
            $c_age = ($_POST['complainant_age'][$i] === '' || !isset($_POST['complainant_age'][$i])) ? null : intval($_POST['complainant_age'][$i]);
            $c_contact_no = ($_POST['complainant_contact_no'][$i] === '' || !isset($_POST['complainant_contact_no'][$i])) ? null : $_POST['complainant_contact_no'][$i];
            $c_email = $_POST['complainant_email'][$i] ?? '';
            $c_alias = null;
            $c_participant_type = 'complainant';
            $blotter_participant_id = generateParticipantId($conn, $blotter_id);

            $stmt_part = $conn->prepare($sql_part);
            $stmt_part->bind_param(
                "ssssssssiis",
                $blotter_participant_id,
                $blotter_id,
                $c_participant_type,
                $c_lastname,
                $c_firstname,
                $c_middlename,
                $c_alias,
                $c_address,
                $c_age,
                $c_contact_no,
                $c_email
            );
            $stmt_part->execute();
            $stmt_part->close();
        }
    }

    // Insert witnesses
    if (!empty($_POST['witness_lastname'])) {
        foreach ($_POST['witness_lastname'] as $i => $w_lastname) {
            $w_firstname = $_POST['witness_firstname'][$i] ?? '';
            $w_middlename = $_POST['witness_middlename'][$i] ?? '';
            $w_address = $_POST['witness_address'][$i] ?? '';
            $w_age = ($_POST['witness_age'][$i] === '' || !isset($_POST['witness_age'][$i])) ? null : intval($_POST['witness_age'][$i]);
            $w_contact_no = ($_POST['witness_contact_no'][$i] === '' || !isset($_POST['witness_contact_no'][$i])) ? null : $_POST['witness_contact_no'][$i];
            $w_email = $_POST['witness_email'][$i] ?? '';

            // Skip if all fields are blank
            if (
                trim($w_lastname) === '' &&
                trim($w_firstname) === '' &&
                trim($w_middlename) === '' &&
                trim($w_address) === '' &&
                $w_age === null &&
                ($w_contact_no === null || trim($w_contact_no) === '') &&
                trim($w_email) === ''
            ) {
                continue;
            }

            $w_alias = Null;
            $w_participant_type = 'witness';
            $blotter_participant_id = generateParticipantId($conn, $blotter_id);

            $stmt_part = $conn->prepare($sql_part);
            $stmt_part->bind_param(
                "ssssssssiis",
                $blotter_participant_id,
                $blotter_id,
                $w_participant_type,
                $w_lastname,
                $w_firstname,
                $w_middlename,
                $w_alias,
                $w_address,
                $w_age,
                $w_contact_no,
                $w_email
            );
            $stmt_part->execute();
            $stmt_part->close();
        }
    }

    // Insert accused
    if (!empty($_POST['accused_lastname'])) {
        foreach ($_POST['accused_lastname'] as $i => $a_lastname) {
            $a_firstname = $_POST['accused_firstname'][$i] ?? '';
            $a_middlename = $_POST['accused_middlename'][$i] ?? '';
            $a_alias = $_POST['accused_alias'][$i] ?? '';
            $a_address = $_POST['accused_address'][$i] ?? '';
            $a_age = ($_POST['accused_age'][$i] === '' || !isset($_POST['accused_age'][$i])) ? null : intval($_POST['accused_age'][$i]);
            $a_contact_no = ($_POST['accused_contact_no'][$i] === '' || !isset($_POST['accused_contact_no'][$i])) ? null : $_POST['accused_contact_no'][$i];
            $a_email = $_POST['accused_email'][$i] ?? '';
            $a_participant_type = 'accused';
            $blotter_participant_id = generateParticipantId($conn, $blotter_id);

            $stmt_part = $conn->prepare($sql_part);
            $stmt_part->bind_param(
                "ssssssssiis",
                $blotter_participant_id,
                $blotter_id,
                $a_participant_type,
                $a_lastname,
                $a_firstname,
                $a_middlename,
                $a_alias,
                $a_address,
                $a_age,
                $a_contact_no,
                $a_email
            );
            $stmt_part->execute();
            $stmt_part->close();
        }
    }

    // ====================================
    // FILE UPLOAD SECTION
    // ====================================

    $uploadDir = __DIR__ . '/../../uploads/blotters/' . $blotter_id . '/';
    $relativeDir = 'uploads/blotters/' . $blotter_id . '/';

    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['blotter_files']) && count($_FILES['blotter_files']['name']) > 0) {
        for ($i = 0; $i < count($_FILES['blotter_files']['name']); $i++) {
            $fileTmpPath = $_FILES['blotter_files']['tmp_name'][$i];
            $fileName = basename($_FILES['blotter_files']['name'][$i]);
            $fileSize = $_FILES['blotter_files']['size'][$i];

            // Only process if a file was actually uploaded
            if (empty($fileTmpPath) || !is_uploaded_file($fileTmpPath)) {
                continue;
            }
            $fileType = mime_content_type($fileTmpPath);

            // Generate safe filename to avoid special chars
            $safeFileName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
            $destPath = $uploadDir . $safeFileName;
            $dbFilePath = $relativeDir . $safeFileName;

            // Allowed file types
            $allowedMime = [
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            if (in_array($fileType, $allowedMime)) {
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Insert file info into blotter_filestbl
                    $file_id = generateFileId($conn);
                    $stmt_file = $conn->prepare("
                    INSERT INTO blotter_filestbl (file_id, blotter_id, file_name, file_path, file_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                    $stmt_file->bind_param("sssss", $file_id, $blotter_id, $fileName, $dbFilePath, $fileType);
                    $stmt_file->execute();
                    $stmt_file->close();
                }
            } else {
                echo "<script>alert('File type not allowed: $fileName');</script>";
            }
        }
    }

    $stmt->close();
    $conn->close();
    // $stmt_part->close();
    // Singleton connection closed by PHP

    echo "<script>alert('Blotter report created successfully.'); window.location.href = '../../Pages/Adminpage.php?panel=blotterComplaintPanel';</script>";
    exit;
} else {
    header("Location: ../../Pages/Adminpage.php");
    exit;
}
?>
