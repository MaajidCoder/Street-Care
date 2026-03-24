<?php
// backend/api/departments.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$conn = $db->getConnection();

if ($method === 'GET') {
    // Return stats grouped by category
    
    // 1. Get counts
    $sql = "SELECT category, count(*) as total, 
            SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_count
            FROM issues GROUP BY category";
    
    $stats = $db->select($sql);
    
    $data = [];
    foreach ($stats as $row) {
        $cat = $row['category'];
        $data[$cat] = [
            'total' => $row['total'],
            'resolved_count' => $row['resolved_count'],
            'resolution_rate' => $row['total'] > 0 ? round(($row['resolved_count'] / $row['total']) * 100) : 0
        ];
    }
    
    // 2. Get recent activity (last 5 issues per category? or just generally last 20 mixed)
    // For the modal, we need activity specific to that "Department".
    // Let's just return raw category stats here. The modal can fetch DETAILS or we can return all issues and filter frontend.
    // Given the scale, fetching all issues for the admin dashboard is okay (as we did in dashboard.html).
    // So let's return a summary here.
    
    echo json_encode(['success' => true, 'data' => $data]);

} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
