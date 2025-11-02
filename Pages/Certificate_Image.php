<?php
// ...existing code...
ini_set('display_errors', 0);
error_reporting(E_ALL);

// DB config
$servername = "localhost";
$username = "root";
$password = "";
$database = "barangayDb";

$connection = new mysqli($servername, $username, $password, $database);
if ($connection->connect_error) {
    http_response_code(500);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit;
}

$id = (int) $_GET['id'];

$stmt = $connection->prepare("SELECT CertificateImage FROM docsreqtbl WHERE ReqId = ?");
if (!$stmt) {
    http_response_code(500);
    $connection->close();
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $connection->close();
    http_response_code(404);
    exit;
}

$stmt->bind_result($imageData);
$stmt->fetch();
$stmt->close();
$connection->close();

if ($imageData === null || $imageData === '') {
    http_response_code(404);
    exit;
}

// If value is a data URI with base64: data:image/...;base64,....
if (preg_match('#^data:(image/[^;]+);base64,(.*)$#i', $imageData, $m)) {
    $mime = $m[1];
    $imageData = base64_decode($m[2]);
} else {
    // Try to detect mime of raw binary
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? @finfo_buffer($finfo, $imageData) : false;
    if ($finfo) @finfo_close($finfo);

    // If detection failed and the DB holds base64 text, decode and re-detect
    if (!$mime || stripos($mime, 'text/') === 0) {
        $decoded = base64_decode($imageData, true);
        if ($decoded !== false) {
            $finfo2 = @finfo_open(FILEINFO_MIME_TYPE);
            $mime2 = $finfo2 ? @finfo_buffer($finfo2, $decoded) : false;
            if ($finfo2) @finfo_close($finfo2);

            if ($mime2 && stripos($mime2, 'image/') === 0) {
                $imageData = $decoded;
                $mime = $mime2;
            }
        }
    }

    if (!$mime || stripos($mime, 'image/') !== 0) {
        $mime = 'image/jpeg';
    }
}

// Ensure no stray buffered output
if (ob_get_length()) { @ob_end_clean(); }

// Send headers and binary
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) strlen($imageData));
header('Cache-Control: public, max-age=86400');
echo $imageData;
exit;
// ...existing code...
?>