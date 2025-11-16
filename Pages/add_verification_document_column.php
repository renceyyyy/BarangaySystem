<?php
/**
 * Database Migration: Add VerificationDocument column to scholarship table
 * This column stores the filename of uploaded verification documents for College A/B classification
 * Run this file once to update the database schema
 */

session_start();
require_once '../Process/db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    die("Error: Not authenticated. Please login as admin first.");
}

echo "<h2>Database Migration: Add VerificationDocument Column</h2>";
echo "<p>Starting migration...</p>";

$conn = getDBConnection();

try {
    // Check if column already exists
    $checkQuery = "SHOW COLUMNS FROM scholarship LIKE 'VerificationDocument'";
    $result = $conn->query($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Column 'VerificationDocument' already exists. No migration needed.</p>";
    } else {
        // Add the column
        $alterQuery = "ALTER TABLE scholarship 
                       ADD COLUMN VerificationDocument VARCHAR(255) NULL 
                       COMMENT 'Filename of uploaded verification document for College A/B classification' 
                       AFTER ScholarshipGrant";
        
        if ($conn->query($alterQuery) === TRUE) {
            echo "<p style='color: green;'>✅ Successfully added 'VerificationDocument' column to scholarship table!</p>";
            echo "<p><strong>Column Details:</strong></p>";
            echo "<ul>";
            echo "<li>Type: VARCHAR(255)</li>";
            echo "<li>Nullable: Yes (NULL allowed)</li>";
            echo "<li>Purpose: Store verification document filenames for College level verification</li>";
            echo "<li>Location: After ScholarshipGrant column</li>";
            echo "</ul>";
        } else {
            throw new Exception("Error adding column: " . $conn->error);
        }
    }
    
    // Verify the column exists now
    $verifyQuery = "SHOW COLUMNS FROM scholarship LIKE 'VerificationDocument'";
    $verifyResult = $conn->query($verifyQuery);
    
    if ($verifyResult && $verifyResult->num_rows > 0) {
        echo "<p style='color: green;'>✅ Migration verified successfully!</p>";
        $columnInfo = $verifyResult->fetch_assoc();
        echo "<pre>";
        print_r($columnInfo);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();

echo "<hr>";
echo "<p><a href='SKpage.php'>← Back to SK Page</a></p>";
echo "<p><em>Note: You can safely delete this migration file after running it successfully.</em></p>";
?>
