<?php
session_start();
require_once '../Process/db_connection.php';
require_once '../Process/user_activity_logger.php';

echo "<h1>Complete Activity Logging Debug</h1>";

// 1. Check session
echo "<h2>1. Session Status</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in: " . $_SESSION['user_id'] . "<br>";
    $currentUserId = $_SESSION['user_id'];
} else {
    echo "❌ User NOT logged in<br>";
    echo "<p><strong>This is the problem!</strong> You must be logged in for activity logging to work.</p>";
    echo "<a href='../Login/login.php'>Go to Login</a>";
    exit;
}

// 2. Database connection test
echo "<h2>2. Database Connection</h2>";
$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connected<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// 3. Table existence and structure
echo "<h2>3. Table Verification</h2>";
$result = $conn->query("SHOW TABLES LIKE 'user_activity_logs'");
if ($result && $result->num_rows > 0) {
    echo "✅ Table exists<br>";
    
    // Show structure
    $structure = $conn->query("DESC user_activity_logs");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Table does not exist<br>";
    exit;
}

// 4. Current record count
echo "<h2>4. Current Records</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM user_activity_logs");
$row = $result->fetch_assoc();
$beforeCount = $row['count'];
echo "Records before test: <strong>$beforeCount</strong><br>";

// 5. Test direct SQL insert
echo "<h2>5. Test Direct SQL Insert</h2>";
$testSql = "INSERT INTO user_activity_logs (user_id, activity, category, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($testSql);
if ($stmt) {
    $testUserId = $currentUserId;
    $testActivity = 'Direct SQL Test';
    $testCategory = 'debug_test';
    $testDetails = json_encode(['test' => 'direct insert', 'time' => date('Y-m-d H:i:s')]);
    $testIP = '127.0.0.1';
    $testUA = 'Debug Script';
    
    $stmt->bind_param("isssss", $testUserId, $testActivity, $testCategory, $testDetails, $testIP, $testUA);
    
    if ($stmt->execute()) {
        echo "✅ Direct SQL insert successful - ID: " . $stmt->insert_id . "<br>";
    } else {
        echo "❌ Direct SQL insert failed: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "❌ Could not prepare SQL statement: " . $conn->error . "<br>";
}

// 6. Test logUserActivity function
echo "<h2>6. Test logUserActivity() Function</h2>";
echo "Calling logUserActivity()...<br>";

// Enable error reporting for this test
error_reporting(E_ALL);
ini_set('display_errors', 1);

$logResult = logUserActivity(
    'Function Test Activity',
    'debug_function_test',
    ['test' => 'function call', 'user_id' => $currentUserId, 'time' => date('Y-m-d H:i:s')]
);

if ($logResult === true) {
    echo "✅ logUserActivity() returned TRUE<br>";
} elseif ($logResult === false) {
    echo "❌ logUserActivity() returned FALSE<br>";
} else {
    echo "⚠️ logUserActivity() returned unexpected value: " . var_export($logResult, true) . "<br>";
}

// 7. Check record count after tests
echo "<h2>7. Records After Tests</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM user_activity_logs");
$row = $result->fetch_assoc();
$afterCount = $row['count'];
echo "Records after test: <strong>$afterCount</strong><br>";
echo "Records added: <strong>" . ($afterCount - $beforeCount) . "</strong><br>";

// 8. Show recent records
echo "<h2>8. Recent Activity Records</h2>";
$recent = $conn->query("SELECT * FROM user_activity_logs ORDER BY id DESC LIMIT 5");
if ($recent->num_rows > 0) {
    echo "<table border='1' style='width:100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Activity</th><th>Category</th><th>Details</th><th>Timestamp</th></tr>";
    while ($row = $recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['activity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td style='max-width:200px; overflow:hidden;'>" . htmlspecialchars(substr($row['details'], 0, 50)) . "</td>";
        echo "<td>" . $row['timestamp'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No records found<br>";
}

// 9. Test with current session
echo "<h2>9. Session Variables Debug</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// 10. Check error log
echo "<h2>10. Check for Errors</h2>";
echo "<p>If there are PHP errors, they would appear in the Apache error log.</p>";
echo "<p>Error log location: C:\\xampp\\apache\\logs\\error.log</p>";

?>