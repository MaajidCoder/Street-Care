<?php
// backend/api/authority_backend.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

$action = $_GET['action'] ?? '';

if ($method === 'GET') {
     if ($action === 'overview') {
         // Get stats for authority dashboard
         $total = $db->select("SELECT COUNT(*) as c FROM issues")[0]['c'];
         $pending = $db->select("SELECT COUNT(*) as c FROM issues WHERE status='pending' OR status='under_review'")[0]['c'];
         $resolved = $db->select("SELECT COUNT(*) as c FROM issues WHERE status='resolved' OR status='closed'")[0]['c'];
         $critical = $db->select("SELECT COUNT(*) as c FROM issues WHERE priority='critical'")[0]['c'];
         // New: Count volunteers
         $volunteers = $db->select("SELECT COUNT(*) as c FROM users WHERE role='volunteer'")[0]['c'];
         
         // Calc resolution rate
         $resolution_rate = $total > 0 ? round(($resolved / $total) * 100) : 0;
         
         echo json_encode([
             'success' => true,
             'stats' => [
                 'total' => $total,
                 'pending' => $pending,
                 'resolved' => $resolved,
                 'critical' => $critical,
                 'volunteers' => $volunteers,
                 'resolution_rate' => $resolution_rate
             ]
         ]);
     }
     elseif ($action === 'work_orders') {
         // Fetch all issues that are not closed (for management)
         $sql = "SELECT * FROM issues ORDER BY created_at DESC";
         $result = $db->select($sql);
         echo json_encode(['success' => true, 'data' => $result]);
     }
}
elseif ($method === 'POST') {
    // Authority actions like updating status are handled in issues.php mostly, 
    // but specific authority actions can go here.
    echo json_encode(['success' => true, 'message' => 'Authority action received']);
}
else {
    echo json_encode(['error' => 'Invalid request']);
}
// End of file
