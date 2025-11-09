<?php
// Connect to database
$connection = new mysqli("localhost", "root", "", "barangaydb");

// Get age distribution (10-20, 21-30, etc.)
// Get age group distribution (only 10–60, exclude empty groups)
$ageQuery = "
    SELECT 
        CASE 
            WHEN age BETWEEN 10 AND 20 THEN '10-20'
            WHEN age BETWEEN 21 AND 30 THEN '21-30'
            WHEN age BETWEEN 31 AND 40 THEN '31-40'
            WHEN age BETWEEN 41 AND 50 THEN '41-50'
            WHEN age BETWEEN 51 AND 60 THEN '51-60'
        END AS age_group,
        COUNT(*) AS total
    FROM userloginfo
    WHERE age BETWEEN 10 AND 60
    GROUP BY age_group
    HAVING total > 0
    ORDER BY MIN(age)
";

$ageResult = $connection->query($ageQuery);

$ageLabels = [];
$ageCounts = [];

while ($row = $ageResult->fetch_assoc()) {
    $ageLabels[] = $row['age_group'];
    $ageCounts[] = $row['total'];
}


// Get civil status distribution (only Single, Married, Widowed, Divorced)
$civilQuery = "
    SELECT CivilStatus, COUNT(*) AS total 
    FROM userloginfo 
    WHERE CivilStatus IN ('Single', 'Married', 'Widowed', 'Divorced')
    GROUP BY CivilStatus
";
$civilResult = $connection->query($civilQuery);

$civilLabels = [];
$civilCounts = [];

while ($row = $civilResult->fetch_assoc()) {
    $civilLabels[] = $row['CivilStatus'];
    $civilCounts[] = $row['total'];
}


$businessQuery = "SELECT RequestType, COUNT(*) AS total FROM businesstbl GROUP BY RequestType";
$businessResult = $connection->query($businessQuery);

$businessLabels = [];
$businessCounts = [];

while ($row = $businessResult->fetch_assoc()) {
    $businessLabels[] = $row['RequestType'];
    $businessCounts[] = $row['total'];
}

// Get gender distribution
$genderQuery = "
    SELECT Gender, COUNT(*) AS total 
    FROM userloginfo 
    WHERE Gender IN ('Male', 'Female') 
    GROUP BY Gender
";
$genderResult = $connection->query($genderQuery);

$genderLabels = [];
$genderCounts = [];

while ($row = $genderResult->fetch_assoc()) {
    $genderLabels[] = $row['Gender'];
    $genderCounts[] = $row['total'];
}


// Get gender distribution
$unemploymentQuery = "SELECT certificate_type, COUNT(*) AS total FROM unemploymenttbl GROUP BY certificate_type";
$unemploymentResult = $connection->query($unemploymentQuery);

$unemploymentLabels = [];
$unemploymentCounts = [];

while ($row = $unemploymentResult->fetch_assoc()) {
    $unemploymentLabels[] = $row['certificate_type'];
    $unemploymentCounts[] = $row['total'];
}

// Get document requests status
$DocutypeQuery = "SELECT Docutype, COUNT(*) AS total FROM docsreqtbl GROUP BY Docutype";
$DocutypeResult = $connection->query($DocutypeQuery);

$DocutypeLabels = [];
$DocutypeCounts = [];

while ($row = $DocutypeResult->fetch_assoc()) {
    $DocutypeLabels[] = $row['Docutype'];
    $DocutypeCounts[] = $row['total'];
}

// Fetch Scholarship Counts
$scholarshipCount = 0;
$pendingScholarshipCount = 0;
$forExaminationCount = 0;
$approvedScholarshipCount = 0;
$rejectedScholarshipCount = 0;

// Total scholarship applications
$sqlScholar = "SELECT COUNT(*) as total FROM scholarship";
$resultScholar = $connection->query($sqlScholar);
if ($resultScholar && $row = $resultScholar->fetch_assoc()) {
    $scholarshipCount = $row['total'];
}

// Pending scholarship applications
$sqlPending = "SELECT COUNT(*) as total FROM scholarship WHERE RequestStatus = 'Pending'";
$resultPending = $connection->query($sqlPending);
if ($resultPending && $row = $resultPending->fetch_assoc()) {
    $pendingScholarshipCount = $row['total'];
}

// For Examination scholarship applications (these are the ones that were approved but need final approval)
$sqlForExamination = "SELECT COUNT(*) as total FROM scholarship WHERE RequestStatus = 'For Examination'";
$resultForExamination = $connection->query($sqlForExamination);
if ($resultForExamination && $row = $resultForExamination->fetch_assoc()) {
    $forExaminationCount = $row['total'];
}

// Approved scholarship applications (final approved status)
$sqlApproved = "SELECT COUNT(*) as total FROM scholarship WHERE RequestStatus = 'Approved'";
$resultApproved = $connection->query($sqlApproved);
if ($resultApproved && $row = $resultApproved->fetch_assoc()) {
    $approvedScholarshipCount = $row['total'];
}

// Rejected scholarship applications
$sqlRejected = "SELECT COUNT(*) as total FROM scholarship WHERE RequestStatus = 'Rejected'";
$resultRejected = $connection->query($sqlRejected);
if ($resultRejected && $row = $resultRejected->fetch_assoc()) {
    $rejectedScholarshipCount = $row['total'];
}

$RegisterCount = 0;
$sqlRegister = "SELECT COUNT(*) as total FROM userloginfo";
$resultRegister = $connection->query($sqlRegister);
if ($resultRegister && $row = $resultRegister->fetch_assoc()) {
    $RegisterCount = $row['total'];
}

$BlotterCount = 0;
$sqlBlotter = "SELECT COUNT(*) as total FROM blottertbl";
$resultBlotter = $connection->query($sqlBlotter);
if ($resultBlotter && $row = $resultBlotter->fetch_assoc()) {
    $BlotterCount = $row['total'];
}

// Get monthly scholarship applications data (for the chart)
$monthlyQuery = "
    SELECT 
        MONTH(DateApplied) as month_num,
        MONTHNAME(DateApplied) as month_name,
        COUNT(*) as total
    FROM scholarship 
    WHERE YEAR(DateApplied) = YEAR(CURDATE())
    GROUP BY MONTH(DateApplied), MONTHNAME(DateApplied)
    ORDER BY month_num
";
$monthlyResult = $connection->query($monthlyQuery);

$monthlyLabels = [];
$monthlyCounts = [];

// Initialize all months with zero
$allMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

foreach ($allMonths as $month) {
    $monthlyLabels[] = substr($month, 0, 3); // Short month names
    $monthlyCounts[] = 0; // Initialize with zero
}

// Update with actual data
if ($monthlyResult) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthIndex = $row['month_num'] - 1; // Convert to zero-based index
        if (isset($monthlyCounts[$monthIndex])) {
            $monthlyCounts[$monthIndex] = $row['total'];
        }
    }
}
 // Get monthly payment data (for the chart) - Added for tblpayment
$paymentMonthlyQuery = "
    SELECT 
        MONTH(date) as month_num,
        MONTHNAME(date) as month_name,
        COUNT(*) as total
    FROM tblpayment 
    WHERE YEAR(date) = YEAR(CURDATE())
    GROUP BY MONTH(date), MONTHNAME(date)
    ORDER BY month_num
";
$paymentMonthlyResult = $connection->query($paymentMonthlyQuery);
$paymentMonthlyLabels = [];
$paymentMonthlyCounts = [];
// Initialize all months with zero
foreach ($allMonths as $month) {
    $paymentMonthlyLabels[] = substr($month, 0, 3); // Short month names
    $paymentMonthlyCounts[] = 0; // Initialize with zero
}
// Update with actual data
if ($paymentMonthlyResult) {
    while ($row = $paymentMonthlyResult->fetch_assoc()) {
        $monthIndex = $row['month_num'] - 1; // Convert to zero-based index
        if (isset($paymentMonthlyCounts[$monthIndex])) {
            $paymentMonthlyCounts[$monthIndex] = $row['total'];
        }
    }
}

?>
