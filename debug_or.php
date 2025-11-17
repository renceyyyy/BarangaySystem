<?php
require_once 'Process/db_connection.php';
$conn = getDBConnection();

$refno = '202511177122';

echo "<h2>Debugging OR Number for Refno: {$refno}</h2>";
echo "<style>body{font-family:Arial;padding:20px;}table{border-collapse:collapse;margin:10px 0;}th,td{border:1px solid #ccc;padding:8px;}th{background:#4CAF50;color:white;}.null{color:red;font-weight:bold;}</style>";

// Check docsreqtbl
echo "<h3>1. Check docsreqtbl</h3>";
$sql = "SELECT * FROM docsreqtbl WHERE refno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $refno);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<table><tr>";
    foreach ($row as $key => $value) {
        echo "<th>{$key}</th>";
    }
    echo "</tr><tr>";
    foreach ($row as $key => $value) {
        echo "<td>" . ($value ?? '<span class="null">NULL</span>') . "</td>";
    }
    echo "</tr></table>";
} else {
    echo "<p>No record found</p>";
}
$stmt->close();

// Check tblpayment
echo "<h3>2. Check tblpayment</h3>";
$sql = "SELECT * FROM tblpayment WHERE refno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $refno);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<table><tr>";
    foreach ($row as $key => $value) {
        echo "<th>{$key}</th>";
    }
    echo "</tr><tr>";
    foreach ($row as $key => $value) {
        echo "<td>" . ($value ?? '<span class="null">NULL</span>') . "</td>";
    }
    echo "</tr></table>";
} else {
    echo "<p style='color:red;'><strong>❌ NO PAYMENT RECORD FOUND!</strong></p>";
    echo "<p>This is why OR Number shows as N/A. Payment must be processed first.</p>";
}
$stmt->close();

// Check with JOIN
echo "<h3>3. Test JOIN Query (What UserReports.php uses)</h3>";
$sql = "SELECT d.refno, d.DocuType, d.RequestStatus, p.ORNumber, 
        CASE WHEN p.ORNumber IS NULL THEN 'NULL (No payment)' ELSE p.ORNumber END as or_status
        FROM docsreqtbl d 
        LEFT JOIN tblpayment p ON d.refno = p.refno 
        WHERE d.refno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $refno);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Refno</th><th>DocuType</th><th>Status</th><th>ORNumber</th><th>OR Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['refno']}</td>";
        echo "<td>{$row['DocuType']}</td>";
        echo "<td>{$row['RequestStatus']}</td>";
        echo "<td>" . ($row['ORNumber'] ?? '<span class="null">NULL</span>') . "</td>";
        echo "<td>{$row['or_status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
$stmt->close();

echo "<hr><h3>Summary:</h3>";
echo "<p>If ORNumber is NULL, it means:</p>";
echo "<ul>";
echo "<li>✅ Request exists in docsreqtbl</li>";
echo "<li>❌ NO payment record in tblpayment</li>";
echo "<li>💡 Solution: Process payment through Payment.php to generate OR number</li>";
echo "</ul>";

$conn->close();
?>
