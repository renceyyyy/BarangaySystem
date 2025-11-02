<?php
// Endpoint: Process/blotter/record_hearing.php
// Accepts multipart form data with: blotter_id, schedule_start (optional), mediator_name, hearing_notes, outcome, hearing_id (optional), hearing_files[] (optional files)
header('Content-Type: application/json');

// Load DB connection
$dbPath = __DIR__ . '/../db_connection.php';
if (!file_exists($dbPath)) {
    echo json_encode(['success' => false, 'message' => 'Database connection file not found.']);
    exit;
}
require_once $dbPath;

// Read form data
$blotter_id = $_POST['blotter_id'] ?? '';
$schedule_start = $_POST['schedule_start'] ?? '';
$mediator_name = $_POST['mediator_name'] ?? '';
$hearing_notes = $_POST['hearing_notes'] ?? '';
$outcome = $_POST['outcome'] ?? '';
$hearing_id_input = $_POST['hearing_id'] ?? '';

if (empty($blotter_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. blotter_id is required.']);
    exit;
}

// Sanitize / trim
$blotter_id = trim($blotter_id);
$mediator_name = !empty($mediator_name) ? trim($mediator_name) : null;
$hearing_notes = !empty($hearing_notes) ? trim($hearing_notes) : null;
$outcome = !empty($outcome) ? trim($outcome) : null;
$hearing_id_input = !empty($hearing_id_input) ? trim($hearing_id_input) : null;

// Initialize DB connection
if (function_exists('getDBConnection')) {
    $conn = getDBConnection();
} elseif (isset($connection) && $connection) {
    $conn = $connection;
} elseif (isset($conn) && $conn) {
    // already available
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection not available.']);
    exit;
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Check if blotter exists
$check = $conn->prepare("SELECT blotter_id FROM blottertbl WHERE blotter_id = ? LIMIT 1");
$check->bind_param('s', $blotter_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Blotter not found.']);
    exit;
}
$check->close();

// Function to generate unique file ID
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


// Handle hearing update/insert
$hearing_id = null;
$hearing_no = null;
$schedule_end = null;

if ($hearing_id_input) {
    // Try to update existing hearing
    $sel = $conn->prepare("SELECT hearing_id, hearing_no FROM blotter_hearingstbl WHERE hearing_id = ? AND blotter_id = ? LIMIT 1");
    if ($sel) {
        $sel->bind_param('ss', $hearing_id_input, $blotter_id);
        $sel->execute();
        $res = $sel->get_result();
        $existing = $res->fetch_assoc();
        $sel->close();

        if ($existing && isset($existing['hearing_id'])) {
            $update = $conn->prepare("UPDATE blotter_hearingstbl SET schedule_end = ?, mediator_name = ?, hearing_notes = ?, outcome = ?, updated_at = ? WHERE hearing_id = ? AND blotter_id = ?");
            if (!$update) {
                echo json_encode(['success' => false, 'message' => 'DB prepare failed (update): ' . $conn->error]);
                exit;
            }
            $nowUpdate = date('Y-m-d H:i:s');
            $schedule_end = $nowUpdate;
            $bind = $update->bind_param('sssssss', $schedule_end, $mediator_name, $hearing_notes, $outcome, $nowUpdate, $hearing_id_input, $blotter_id);
            if ($bind === false) {
                echo json_encode(['success' => false, 'message' => 'DB bind_param failed (update): ' . $update->error]);
                exit;
            }
            if ($update->execute()) {
                $hearing_id = $hearing_id_input;
                $hearing_no = $existing['hearing_no'];
                $update->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed: ' . $update->error]);
                $update->close();
                exit;
            }
        }
    }
}

// If no update happened, insert new hearing
if (!$hearing_id) {
    // Determine next hearing_no
    $stmt = $conn->prepare("SELECT MAX(hearing_no) as max_no FROM blotter_hearingstbl WHERE blotter_id = ?");
    $stmt->bind_param('s', $blotter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $next_no = 1;
    if ($row && isset($row['max_no']) && $row['max_no'] !== null) {
        $next_no = intval($row['max_no']) + 1;
    }
    $stmt->close();

    try {
        $hearing_id = bin2hex(random_bytes(12));
    } catch (Exception $e) {
        $hearing_id = uniqid('hr_', true);
    }

    $now = date('Y-m-d H:i:s');
    if (!empty($schedule_start)) {
        $dt = date_create($schedule_start);
        $schedule_start = $dt ? $dt->format('Y-m-d H:i:s') : $now;
    } else {
        $schedule_start = $now;
    }

    $insert_sql = "INSERT INTO blotter_hearingstbl (hearing_id, blotter_id, hearing_no, schedule_start, schedule_end, mediator_name, hearing_notes, outcome, created_at) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?)";
    $insert = $conn->prepare($insert_sql);
    if (!$insert) {
        echo json_encode(['success' => false, 'message' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }

    $bindOk = $insert->bind_param('ssisssss', $hearing_id, $blotter_id, $next_no, $schedule_start, $mediator_name, $hearing_notes, $outcome, $now);
    if ($bindOk === false) {
        echo json_encode(['success' => false, 'message' => 'DB bind_param failed: ' . $insert->error]);
        exit;
    }

    if ($insert->execute()) {
        $hearing_no = $next_no;
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $insert->error]);
        $insert->close();
        exit;
    }
    $insert->close();
}

// Handle file uploads (after hearing is saved)
$uploadErrors = [];
$uploadDir = __DIR__ . '/../../uploads/blotters/' . $blotter_id . '/';
$relativeDir = 'uploads/blotters/' . $blotter_id . '/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES['hearing_files']) && count($_FILES['hearing_files']['name']) > 0) {
    for ($i = 0; $i < count($_FILES['hearing_files']['name']); $i++) {
        $fileTmpPath = $_FILES['hearing_files']['tmp_name'][$i];
        $fileName = basename($_FILES['hearing_files']['name'][$i]);

        if (empty($fileTmpPath) || !is_uploaded_file($fileTmpPath)) {
            continue;
        }

        $fileType = mime_content_type($fileTmpPath);
        $allowedMime = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!in_array($fileType, $allowedMime)) {
            $uploadErrors[] = "Invalid file type for '$fileName'. Allowed: JPG, PNG, PDF, DOC, DOCX.";
            continue;
        }

        $safeFileName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
        $destPath = $uploadDir . $safeFileName;
        $dbFilePath = $relativeDir . $safeFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $file_id = generateFileId($conn);
            $stmt_file = $conn->prepare("INSERT INTO blotter_filestbl (file_id, blotter_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
            $stmt_file->bind_param("sssss", $file_id, $blotter_id, $fileName, $dbFilePath, $fileType);
            if (!$stmt_file->execute()) {
                $uploadErrors[] = "Failed to save '$fileName' to database.";
            }
            $stmt_file->close();
        } else {
            $uploadErrors[] = "Failed to upload '$fileName'.";
        }
    }
}

// Prepare response
$message = 'Hearing saved';
if (!empty($uploadErrors)) {
    $message .= ' (Some files failed: ' . implode('; ', $uploadErrors) . ')';
} elseif (isset($_FILES['hearing_files']) && count($_FILES['hearing_files']['name']) > 0) {
    $message .= ' and files uploaded';
}

echo json_encode([
    'success' => true,
    'message' => $message,
    'hearing_id' => $hearing_id,
    'hearing_no' => $hearing_no,
    'schedule_end' => $schedule_end
]);

exit;
?>