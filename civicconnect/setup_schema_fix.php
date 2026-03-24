<?php
// setup_schema_fix.php
// Fix database schema via web request

// Disable error display for JSON output, but log them
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Adjust path to config based on location (root)
require_once 'backend/config/config.php';
require_once 'backend/config/database.php';

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

    // Check if 'resolved_at' exists
    $check = $conn->query("SHOW COLUMNS FROM issues LIKE 'resolved_at'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE issues ADD COLUMN resolved_at TIMESTAMP NULL";
        if ($conn->query($sql)) {
             $updates[] = "Added 'resolved_at' column";
        } else {
             throw new Exception("Error adding 'resolved_at': " . $conn->error);
        }
    }
    


    // Update status ENUM to include all statuses
    $sql = "ALTER TABLE issues MODIFY COLUMN status ENUM('pending', 'under_review', 'assigned', 'in_progress', 'resolved', 'closed', 'rejected') DEFAULT 'pending'";
    if ($conn->query($sql)) {
         $updates[] = "Updated 'status' column ENUM definition";
    } else {
         throw new Exception("Error updating 'status' ENUM: " . $conn->error);
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => true, 'message' => 'Database schema is already up to date.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Database updated: ' . implode(', ', $updates)]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
