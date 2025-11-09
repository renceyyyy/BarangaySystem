<?php
session_start();
require_once '../Process/db_connection.php';

echo "<h2>Debug: User Activity Logs</h2>";

$conn = getDBConnection();

// Check if table exists
echo "<h3>1. Check if table exists:</h3>";
$result = $conn->query("SHOW TABLES LIKE 'user_activity_logs'");
if ($result && $result->num_rows > 0) {
    echo "✅ Table 'user_activity_logs' EXISTS<br>";
    
    // Show table structure
    echo "<h3>2. Table Structure:</h3>";
    $result = $conn->query("DESC user_activity_logs");
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        echo json_encode($row) . "<br>";
    }
    echo "</pre>";
    
    // Show all records
    echo "<h3>3. Current Records in Table:</h3>";
    $result = $conn->query("SELECT * FROM user_activity_logs ORDER BY id DESC LIMIT 10");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Activity</th><th>Category</th><th>Details</th><th>Timestamp</th></tr>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['activity'] . "</td>";
            echo "<td>" . $row['category'] . "</td>";
            echo "<td>" . substr($row['details'], 0, 50) . "...</td>";
            echo "<td>" . $row['timestamp'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No records found</td></tr>";
    }
    echo "</table>";
    
    // Show total count
    echo "<h3>4. Total Records:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM user_activity_logs");
    $row = $result->fetch_assoc();
    echo "Total: " . $row['count'] . " records<br>";
    
} else {
    echo "❌ Table 'user_activity_logs' DOES NOT EXIST<br>";
    echo "<p>The table needs to be created. Run the create_user_activity_table.sql script.</p>";
}

// Check session
echo "<h3>5. Session Information:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ User NOT logged in<br>";
}

// Test logUserActivity function
echo "<h3>6. Test logUserActivity Function:</h3>";
require_once '../Process/user_activity_logger.php';

if (isset($_SESSION['user_id'])) {
    echo "Testing logUserActivity() function...<br>";
    $result = logUserActivity(
        'Debug Test Activity',
        'debug_test',
        ['test_data' => 'test_value']
    );
    
    if ($result) {
        echo "✅ logUserActivity() returned TRUE<br>";
    } else {
        echo "❌ logUserActivity() returned FALSE<br>";
    }
    
    // Check error logs
    echo "<h3>7. PHP Error Logs:</h3>";
    echo "<p>Check the error_log in the Process or Pages directory for more details.</p>";
} else {
    echo "⚠️ Cannot test logUserActivity() - user not logged in<br>";
}

?>
