<?php
// Include database connection module
require_once 'db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_news'])) {
    $conn = getDBConnection();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($content === '') {
        die('Content is required.');
    }

    // Upload directory (web-accessible). Adjust if your public path differs.
    $uploadDir = __DIR__ . '/../Assets/announcements/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $savedPath = null;
    if (!empty($_FILES['newsimage']['name']) && $_FILES['newsimage']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['newsimage']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (!isset($allowed[$mime])) {
            die('Invalid image type.');
        }
        if ($_FILES['newsimage']['size'] > 5 * 1024 * 1024) {
            die('Image too large (max 5MB).');
        }

        $ext = $allowed[$mime];
        $name = 'announcement_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $uploadDir . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            die('Failed to move uploaded file.');
        }

        // Store web-relative path in DB (adjust prefix to your public URL if needed)
        $savedPath = 'Assets/announcements/' . $name;
    }

    $stmt = $conn->prepare("INSERT INTO news (Newsinfo, NewsImage, DatedReported) VALUES (?, ?, NOW())");
    if (!$stmt) die('Prepare failed: ' . $conn->error);

    $stmt->bind_param('ss', $content, $savedPath);
    if ($stmt->execute()) {
        header("Location: ../Pages/Adminpage.php?panel=announcementPanel&message=added");
        exit;
    } else {
        die('DB insert error: ' . htmlspecialchars($stmt->error));
    }
}

// Fetch news items for display
$conn = getDBConnection();
$result = $conn->query("SELECT id, Newsinfo, Newsimage, DatedReported FROM news ORDER BY DatedReported DESC LIMIT 3");

$newsItems = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $newsItems[] = $row;
    }
    $result->free();
}
?>