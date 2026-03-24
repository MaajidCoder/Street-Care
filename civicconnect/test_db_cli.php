<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$logFile = 'db_test_result.log';
file_put_contents($logFile, "Starting DB Test...\n");

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP
$port = 3307; // Verified port
$db = 'civicconnect';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    file_put_contents($logFile, "Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "Connected successfully to database '$db'.\n", FILE_APPEND);
    
    // Check if tables exist
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        file_put_contents($logFile, "Tables in '$db':\n", FILE_APPEND);
        while ($row = $result->fetch_row()) {
            file_put_contents($logFile, " - " . $row[0] . "\n", FILE_APPEND);
        }
    }
}
$conn->close();
file_put_contents($logFile, "Test Complete.\n", FILE_APPEND);
?>
