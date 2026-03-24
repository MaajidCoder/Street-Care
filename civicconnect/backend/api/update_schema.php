<?php
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Add volunteer_id column to issues table
$sql = "ALTER TABLE issues ADD COLUMN volunteer_id INT DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column volunteer_id added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
