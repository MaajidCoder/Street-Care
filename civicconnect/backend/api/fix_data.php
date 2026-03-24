<?php
require_once '../config/config.php';
require_once '../config/database.php';

$db = Database::getInstance();
$db->update("DELETE FROM issues WHERE id = 6");

echo "Deleted issue 6";
?>
