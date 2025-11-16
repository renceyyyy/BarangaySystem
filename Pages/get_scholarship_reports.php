<?php
// Initialize role-based session
require_once __DIR__ . '/../config/session_config.php';
initRoleBasedSession('sk');

header('Content-Type: application/json');

// Check if user is logged in with SK role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'sk') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated or insufficient permissions']);
    exit();
}

// Database connection
$connection = new mysqli("localhost", "root", "", "barangaydb");
if ($connection->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$response = [
    'success' => true,
    'budgetAllocation' => [],
    'totalBudget' => 0,
    'juniorHighSchool' => [],
    'seniorHighSchool' => [],
    'college' => [],
    'septemberToFebruary' => []
];

// 1. Calculate budget allocation by education level
// Get sum of ScholarshipGrant grouped by EducationLevel for approved applications
$budgetQuery = "SELECT
    EducationLevel,
    SUM(COALESCE(ScholarshipGrant, 0)) as TotalGrant,
    COUNT(*) as StudentCount
FROM scholarship
WHERE RequestStatus = 'Approved'
AND ScholarshipGrant IS NOT NULL
AND ScholarshipGrant > 0
GROUP BY EducationLevel";

$budgetResult = $connection->query($budgetQuery);
if ($budgetResult) {
    $totalBudget = 0;
    $allocationData = [];

    while ($row = $budgetResult->fetch_assoc()) {
        $educationLevel = $row['EducationLevel'];
        $totalGrant = intval($row['TotalGrant']);
        $studentCount = intval($row['StudentCount']);

        // Categorize education levels
        $category = 'Administrative'; // Default
        if (
            stripos($educationLevel, 'Junior') !== false || stripos($educationLevel, 'Grade 7') !== false ||
            stripos($educationLevel, 'Grade 8') !== false || stripos($educationLevel, 'Grade 9') !== false ||
            stripos($educationLevel, 'Grade 10') !== false
        ) {
            $category = 'Junior High School';
        } elseif (
            stripos($educationLevel, 'Senior') !== false || stripos($educationLevel, 'Grade 11') !== false ||
            stripos($educationLevel, 'Grade 12') !== false
        ) {
            $category = 'Senior High School';
        } elseif (
            stripos($educationLevel, 'College') !== false || stripos($educationLevel, '1st Year') !== false ||
            stripos($educationLevel, '2nd Year') !== false || stripos($educationLevel, '3rd Year') !== false ||
            stripos($educationLevel, '4th Year') !== false
        ) {
            $category = 'College';
        }

        if (!isset($allocationData[$category])) {
            $allocationData[$category] = 0;
        }
        $allocationData[$category] += $totalGrant;
        $totalBudget += $totalGrant;
    }

    foreach ($allocationData as $category => $amount) {
        $response['budgetAllocation'][] = [
            'category' => $category,
            'amount' => $amount
        ];
    }

    $response['totalBudget'] = $totalBudget;
}

// 2. Get Junior High School students
$jhsQuery = "SELECT
    CONCAT(Firstname, ' ', Lastname) as Name,
    RequestStatus as Status,
    EducationLevel,
    DATE_FORMAT(DateApplied, '%Y-%m-%d') as DateApplied,
    ApplicationID,
    DATE_FORMAT(DateOfResubmitting, '%Y-%m-%d') as DateOfResubmitting
FROM scholarship
WHERE RequestStatus != 'Rejected'
AND (EducationLevel LIKE '%Junior%' OR EducationLevel LIKE '%Grade 7%' OR
     EducationLevel LIKE '%Grade 8%' OR EducationLevel LIKE '%Grade 9%' OR
     EducationLevel LIKE '%Grade 10%')
ORDER BY DateApplied DESC";

$jhsResult = $connection->query($jhsQuery);
if ($jhsResult) {
    while ($row = $jhsResult->fetch_assoc()) {
        $response['juniorHighSchool'][] = $row;
    }
}

// 3. Get Senior High School students
$shsQuery = "SELECT
    CONCAT(Firstname, ' ', Lastname) as Name,
    RequestStatus as Status,
    EducationLevel,
    DATE_FORMAT(DateApplied, '%Y-%m-%d') as DateApplied,
    ApplicationID,
    DATE_FORMAT(DateOfResubmitting, '%Y-%m-%d') as DateOfResubmitting
FROM scholarship
WHERE RequestStatus != 'Rejected'
AND (EducationLevel LIKE '%Senior%' OR EducationLevel LIKE '%Grade 11%' OR
     EducationLevel LIKE '%Grade 12%')
ORDER BY DateApplied DESC";

$shsResult = $connection->query($shsQuery);
if ($shsResult) {
    while ($row = $shsResult->fetch_assoc()) {
        $response['seniorHighSchool'][] = $row;
    }
}

// 4. Get College students
$collegeQuery = "SELECT
    CONCAT(Firstname, ' ', Lastname) as Name,
    RequestStatus as Status,
    EducationLevel,
    DATE_FORMAT(DateApplied, '%Y-%m-%d') as DateApplied,
    ApplicationID,
    DATE_FORMAT(DateOfResubmitting, '%Y-%m-%d') as DateOfResubmitting
FROM scholarship
WHERE RequestStatus != 'Rejected'
AND (EducationLevel LIKE '%College%' OR EducationLevel LIKE '%1st Year%' OR
     EducationLevel LIKE '%2nd Year%' OR EducationLevel LIKE '%3rd Year%' OR
     EducationLevel LIKE '%4th Year%')
ORDER BY DateApplied DESC";

$collegeResult = $connection->query($collegeQuery);
if ($collegeResult) {
    while ($row = $collegeResult->fetch_assoc()) {
        $response['college'][] = $row;
    }
}

// 5. Get scholars from September 2025 to February 2026 (approved scholars only)
$septFebQuery = "SELECT
    CONCAT(Firstname, ' ', Lastname) as Name,
    RequestStatus as Status,
    EducationLevel
FROM scholarship
WHERE RequestStatus = 'Approved'
AND (
    (MONTH(DateApplied) >= 9 AND YEAR(DateApplied) = 2025) OR
    (MONTH(DateApplied) <= 2 AND YEAR(DateApplied) = 2026)
)
ORDER BY EducationLevel, Name";

$septFebResult = $connection->query($septFebQuery);
if ($septFebResult) {
    while ($row = $septFebResult->fetch_assoc()) {
        $response['septemberToFebruary'][] = $row;
    }
}

$connection->close();
echo json_encode($response);
