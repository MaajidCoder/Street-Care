<?php
// backend/api/debug_auth.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Resolve path to database.php
$dbPath = __DIR__ . '/../config/database.php';
if (!file_exists($dbPath)) {
    die("Database config not found at: $dbPath\n");
}

require_once $dbPath;

echo "Database config loaded.\n";

try {
    $db = Database::getInstance();
    echo "Database instance created.\n";
    
    // Simulate overview action
    echo "Running queries...\n";
    
    // Query 1: Total
    $sql1 = "SELECT COUNT(*) as c FROM issues";
    $total = $db->select($sql1)[0]['c'];
    echo "Total: $total\n";
    
    // Query 2: Pending
    $sql2 = "SELECT COUNT(*) as c FROM issues WHERE status='pending' OR status='under_review'";
    $pending = $db->select($sql2)[0]['c'];
    echo "Pending: $pending\n";
    
    // Query 3: Resolved
    $sql3 = "SELECT COUNT(*) as c FROM issues WHERE status='resolved' OR status='closed'";
    $resolved = $db->select($sql3)[0]['c'];
    echo "Resolved: $resolved\n";
    
    // Query 4: Critical
    $sql4 = "SELECT COUNT(*) as c FROM issues WHERE priority='critical'";
    $critical = $db->select($sql4)[0]['c'];
    echo "Critical: $critical\n";
    
    // Query 5: Volunteers
    $sql5 = "SELECT COUNT(*) as c FROM users WHERE role='volunteer'";
    $volunteers = $db->select($sql5)[0]['c'];
    echo "Volunteers: $volunteers\n";
    
    $resolution_rate = $total > 0 ? round(($resolved / $total) * 100) : 0;
    echo "Rate: $resolution_rate%\n";
    
    echo "Success.\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
