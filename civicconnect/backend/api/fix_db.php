<?php
// backend/api/fix_db.php
require_once '../config/database.php';
require_once '../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents('fix_log.txt', "Starting fix_db.php at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $updates = [];
    
    // Check if 'assigned_to' exists in 'issues'
    $check = $conn->query("SHOW COLUMNS FROM issues LIKE 'assigned_to'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE issues ADD COLUMN assigned_to INT DEFAULT NULL";
        if ($conn->query($sql)) {
            $updates[] = "Added 'assigned_to' column";
        } else {
             throw new Exception("Error adding 'assigned_to': " . $conn->error);
        }
    }
    
    // Check if 'assigned_at' exists
    $check = $conn->query("SHOW COLUMNS FROM issues LIKE 'assigned_at'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE issues ADD COLUMN assigned_at TIMESTAMP NULL";
        if ($conn->query($sql)) {
             $updates[] = "Added 'assigned_at' column";
        } else {
             throw new Exception("Error adding 'assigned_at': " . $conn->error);
        }
    }
    
    // Check 'volunteer_id' - if it exists, maybe we should drop it or just ignore?
    // Let's leave it to avoid data loss if it was used.
    
    if (empty($updates)) {
        echo json_encode(['success' => true, 'message' => 'Database schema is already up to date.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Database updated: ' . implode(', ', $updates)]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
