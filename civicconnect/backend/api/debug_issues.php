<?php
require_once '../config/config.php';
require_once '../config/database.php';

$db = Database::getInstance();
$issues = $db->select("SELECT * FROM issues");

header('Content-Type: application/json');
echo json_encode($issues, JSON_PRETTY_PRINT);
?>
