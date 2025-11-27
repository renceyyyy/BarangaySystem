<?php
// Initialize role-based session for SK users
require_once __DIR__ . '/../config/session_config.php';
initRoleBasedSession('sk');

header('Content-Type: application/json');

// Check if user is logged in with proper role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Security check - only sk users allowed to update resubmit dates
if ($_SESSION['role'] !== 'sk') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['applicationId']) || !isset($data['resubmitDate'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$applicationId = intval($data['applicationId']);
$resubmitDate = $data['resubmitDate'];

// Validate date format
$dateObj = DateTime::createFromFormat('Y-m-d', $resubmitDate);
if (!$dateObj) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Validate date is between September 2025 and February 2026
$minDate = new DateTime('2025-09-01');
$maxDate = new DateTime('2026-02-28');
if ($dateObj < $minDate || $dateObj > $maxDate) {
    echo json_encode(['success' => false, 'message' => 'Date must be between September 2025 and February 2026']);
    exit();
}

// Database connection
$connection = new mysqli("localhost", "root", "", "barangaydb");
if ($connection->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Update DateOfResubmitting
$stmt = $connection->prepare("UPDATE scholarship SET DateOfResubmitting = ? WHERE ApplicationID = ?");
$stmt->bind_param("si", $resubmitDate, $applicationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Date updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update date']);
}

$stmt->close();
$connection->close();
