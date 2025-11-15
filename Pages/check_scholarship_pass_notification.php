<?php
// check_scholarship_pass_notification.php
// This file checks if the logged-in user has a newly approved scholarship that hasn't been notified yet

session_start();
require_once '../Process/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasNotification' => false]);
    exit();
}

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Check if user has an approved scholarship that hasn't been notified yet
$sql = "SELECT ApplicationID, EducationLevel, ScholarshipGrant
        FROM scholarship
        WHERE UserID = ?
        AND RequestStatus = 'Approved'
        AND (PassedNotified = 0 OR PassedNotified IS NULL)
        LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // User has a newly approved scholarship
        echo json_encode([
            'hasNotification' => true,
            'applicationId' => $row['ApplicationID'],
            'educationLevel' => $row['EducationLevel'],
            'grantAmount' => number_format((float)$row['ScholarshipGrant'], 2)
        ]);
    } else {
        echo json_encode(['hasNotification' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['hasNotification' => false]);
}

$conn->close();
