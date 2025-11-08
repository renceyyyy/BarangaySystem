<?php
session_start();
require_once '../db_connection.php';
$conn = getDBConnection();

// Helper: Generate file ID
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

// Helper: Generate participant ID
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $blotter_id = $_POST['blotter_id'] ?? '';
    if (!$blotter_id) {
        echo "<script>alert('Missing blotter ID'); window.location.href = '../../Pages/Adminpage.php?panel=blotterComplaintPanel';</script>";
        exit;
    }

    // --- 1. Update blottertbl ---
    $datetime_of_incident = $_POST['incident_datetime'] ?? date('Y-m-d H:i:s');
    $location_of_incident = trim($_POST['incident_location'] ?? '');
    $incident_type = trim($_POST['incident_type'] ?? '');
    $blotter_details = trim($_POST['incident_description'] ?? '');

    $sql = "UPDATE blottertbl SET
        datetime_of_incident = ?,
        location_of_incident = ?,
        incident_type = ?,
        blotter_details = ?
        WHERE blotter_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $datetime_of_incident, $location_of_incident, $incident_type, $blotter_details, $blotter_id);
    $stmt->execute();
    $stmt->close();

    // --- 2. Update, Insert, Delete complainants ---
    $existing_complainants = [];
    $sql = "SELECT blotter_participant_id FROM blotter_participantstbl WHERE blotter_id=? AND participant_type='complainant'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $blotter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $existing_complainants[] = $row['blotter_participant_id'];
    }
    $stmt->close();

    $form_complainant_ids = $_POST['complainant_id'] ?? [];
    $form_complainant_lastname = $_POST['complainant_lastname'] ?? [];
    $form_complainant_firstname = $_POST['complainant_firstname'] ?? [];
    $form_complainant_middlename = $_POST['complainant_middlename'] ?? [];
    $form_complainant_address = $_POST['complainant_address'] ?? [];
    $form_complainant_age = $_POST['complainant_age'] ?? [];
    $form_complainant_contact_no = $_POST['complainant_contact_no'] ?? [];
    $form_complainant_email = $_POST['complainant_email'] ?? [];

    $handled_complainant_ids = [];
    foreach ($form_complainant_lastname as $i => $lastname) {
        $id = $form_complainant_ids[$i] ?? '';
        $firstname = $form_complainant_firstname[$i] ?? '';
        $middlename = $form_complainant_middlename[$i] ?? '';
        $address = $form_complainant_address[$i] ?? '';
        $age = (isset($form_complainant_age[$i]) && $form_complainant_age[$i] !== '') ? intval($form_complainant_age[$i]) : null;
        $contact_no = (isset($form_complainant_contact_no[$i]) && $form_complainant_contact_no[$i] !== '') ? $form_complainant_contact_no[$i] : null;
        $email = $form_complainant_email[$i] ?? '';

        if ($id && in_array($id, $existing_complainants)) {
            // Update
            $sql = "UPDATE blotter_participantstbl SET
            lastname=?, firstname=?, middlename=?, address=?, age=?, contact_no=?, email=?
            WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiiss", $lastname, $firstname, $middlename, $address, $age, $contact_no, $email, $id);
            $stmt->execute();
            $stmt->close();
            $handled_complainant_ids[] = $id;
        } else {
            // Insert new
            $new_id = generateParticipantId($conn, $blotter_id);
            $participant_type = 'complainant';
            $sql = "INSERT INTO blotter_participantstbl (
            blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, alias, address, age, contact_no, email
        ) VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssiis", $new_id, $blotter_id, $participant_type, $lastname, $firstname, $middlename, $address, $age, $contact_no, $email);
            $stmt->execute();
            $stmt->close();
            $handled_complainant_ids[] = $new_id;
        }
    }
    // Delete complainants not present in form
    foreach ($existing_complainants as $id) {
        if (!in_array($id, $handled_complainant_ids)) {
            $sql = "DELETE FROM blotter_participantstbl WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 3. Update, Insert, Delete accused ---
    // Get all existing accused IDs for this blotter
    $existing_accused = [];
    $sql = "SELECT blotter_participant_id FROM blotter_participantstbl WHERE blotter_id=? AND participant_type='accused'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $blotter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $existing_accused[] = $row['blotter_participant_id'];
    }
    $stmt->close();

    // Get accused from form
    $form_accused_ids = $_POST['accused_id'] ?? []; // hidden input for each accused row
    $form_accused_lastname = $_POST['accused_lastname'] ?? [];
    $form_accused_firstname = $_POST['accused_firstname'] ?? [];
    $form_accused_middlename = $_POST['accused_middlename'] ?? [];
    $form_accused_alias = $_POST['accused_alias'] ?? [];
    $form_accused_address = $_POST['accused_address'] ?? [];
    $form_accused_age = $_POST['accused_age'] ?? [];
    $form_accused_contact_no = $_POST['accused_contact_no'] ?? [];
    $form_accused_email = $_POST['accused_email'] ?? [];

    $handled_accused_ids = [];
    foreach ($form_accused_lastname as $i => $lastname) {
        $id = $form_accused_ids[$i] ?? '';
        $firstname = $form_accused_firstname[$i] ?? '';
        $middlename = $form_accused_middlename[$i] ?? '';
        $alias = $form_accused_alias[$i] ?? '';
        $address = $form_accused_address[$i] ?? '';
        $age = (isset($form_accused_age[$i]) && $form_accused_age[$i] !== '') ? intval($form_accused_age[$i]) : null;
        $contact_no = (isset($form_accused_contact_no[$i]) && $form_accused_contact_no[$i] !== '') ? $form_accused_contact_no[$i] : null;
        $email = $form_accused_email[$i] ?? '';

        if ($id && in_array($id, $existing_accused)) {
            // Update
            $sql = "UPDATE blotter_participantstbl SET
                lastname=?, firstname=?, middlename=?, alias=?, address=?, age=?, contact_no=?, email=?
                WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiiss", $lastname, $firstname, $middlename, $alias, $address, $age, $contact_no, $email, $id);
            $stmt->execute();
            $stmt->close();
            $handled_accused_ids[] = $id;
        } else {
            // Insert new
            $new_id = generateParticipantId($conn, $blotter_id);
            $participant_type = 'accused';
            $sql = "INSERT INTO blotter_participantstbl (
                blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, alias, address, age, contact_no, email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssiis", $new_id, $blotter_id, $participant_type, $lastname, $firstname, $middlename, $alias, $address, $age, $contact_no, $email);
            $stmt->execute();
            $stmt->close();
            $handled_accused_ids[] = $new_id;
        }
    }
    // Delete accused not present in form
    foreach ($existing_accused as $id) {
        if (!in_array($id, $handled_accused_ids)) {
            $sql = "DELETE FROM blotter_participantstbl WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 4. Update, Insert, Delete witnesses ---
    $existing_witness = [];
    $sql = "SELECT blotter_participant_id FROM blotter_participantstbl WHERE blotter_id=? AND participant_type='witness'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $blotter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $existing_witness[] = $row['blotter_participant_id'];
    }
    $stmt->close();

    $form_witness_ids = $_POST['witness_id'] ?? [];
    $form_witness_lastname = $_POST['witness_lastname'] ?? [];
    $form_witness_firstname = $_POST['witness_firstname'] ?? [];
    $form_witness_middlename = $_POST['witness_middlename'] ?? [];
    $form_witness_address = $_POST['witness_address'] ?? [];
    $form_witness_age = $_POST['witness_age'] ?? [];
    $form_witness_contact_no = $_POST['witness_contact_no'] ?? [];
    $form_witness_email = $_POST['witness_email'] ?? [];

    $handled_witness_ids = [];
    foreach ($form_witness_lastname as $i => $lastname) {
        $id = $form_witness_ids[$i] ?? '';
        $firstname = $form_witness_firstname[$i] ?? '';
        $middlename = $form_witness_middlename[$i] ?? '';
        $address = $form_witness_address[$i] ?? '';
        $age = (isset($form_witness_age[$i]) && $form_witness_age[$i] !== '') ? intval($form_witness_age[$i]) : null;
        $contact_no = (isset($form_witness_contact_no[$i]) && $form_witness_contact_no[$i] !== '') ? $form_witness_contact_no[$i] : null;
        $email = $form_witness_email[$i] ?? '';

        if ($id && in_array($id, $existing_witness)) {
            // Update
            $sql = "UPDATE blotter_participantstbl SET
                lastname=?, firstname=?, middlename=?, address=?, age=?, contact_no=?, email=?
                WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiiss", $lastname, $firstname, $middlename, $address, $age, $contact_no, $email, $id);
            $stmt->execute();
            $stmt->close();
            $handled_witness_ids[] = $id;
        } else {
            // Insert new
            $new_id = generateParticipantId($conn, $blotter_id);
            $participant_type = 'witness';
            $sql = "INSERT INTO blotter_participantstbl (
                blotter_participant_id, blotter_id, participant_type, lastname, firstname, middlename, alias, address, age, contact_no, email
            ) VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssiis", $new_id, $blotter_id, $participant_type, $lastname, $firstname, $middlename, $address, $age, $contact_no, $email);
            $stmt->execute();
            $stmt->close();
            $handled_witness_ids[] = $new_id;
        }
    }
    // Delete witnesses not present in form
    foreach ($existing_witness as $id) {
        if (!in_array($id, $handled_witness_ids)) {
            $sql = "DELETE FROM blotter_participantstbl WHERE blotter_participant_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- 5. Upload new files ---
    $uploadDir = __DIR__ . '/../../uploads/blotters/' . $blotter_id . '/';
    $relativeDir = 'uploads/blotters/' . $blotter_id . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    if (isset($_FILES['blotter_files']) && count($_FILES['blotter_files']['name']) > 0) {
        for ($i = 0; $i < count($_FILES['blotter_files']['name']); $i++) {
            $fileTmpPath = $_FILES['blotter_files']['tmp_name'][$i];
            $fileName = basename($_FILES['blotter_files']['name'][$i]);

            // Skip if no file was uploaded in this slot
            if (empty($fileTmpPath) || !is_uploaded_file($fileTmpPath)) {
                continue;
            }

            $fileType = mime_content_type($fileTmpPath);

            $safeFileName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
            $destPath = $uploadDir . $safeFileName;
            $dbFilePath = $relativeDir . $safeFileName;

            $allowedMime = [
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            if (in_array($fileType, $allowedMime)) {
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $file_id = generateFileId($conn);
                    $stmt_file = $conn->prepare("
                    INSERT INTO blotter_filestbl (file_id, blotter_id, file_name, file_path, file_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                    $stmt_file->bind_param("sssss", $file_id, $blotter_id, $fileName, $dbFilePath, $fileType);
                    $stmt_file->execute();
                    $stmt_file->close();
                }
            }
        }
    }

    $conn->close();
    echo "<script>alert('Blotter report updated successfully.'); window.location.href = '../../Pages/Adminpage.php?panel=blotterComplaintPanel';</script>";
    exit;
} else {
    header("Location: ../../Pages/Adminpage.php");
    exit;
}
?>