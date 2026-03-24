<?php
// backend/api/volunteers.php
require_once '../config/config.php';
require_once '../config/database.php';

$db = Database::getInstance();
$action = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? 0;

if ($action === 'stats') {
    // If no specific user logic for now, return system stats relevant to volunteers
    // Or personal stats if user_id linked to tasks
    
    // Example: Count open tasks compatible with volunteers
    $openTasks = $db->select("SELECT COUNT(*) as count FROM issues WHERE status = 'assigned' OR status = 'in_progress'");
    $completed = $db->select("SELECT COUNT(*) as count FROM issues WHERE status = 'resolved'");
    
    // Mocking 'Your Impact' if we don't have a direct link yet
    echo json_encode(['success' => true, 'stats' => [
        'available_tasks' => 12, // Mock or real
        'hours_contributed' => 24, // Mock
        'people_helped' => 156, // Mock
        'ranking' => 'Top 5%' // Mock
    ]]);
} 
elseif ($action === 'tasks') {
    // Get tasks assigned to this volunteer OR unassigned tasks in their area
    $sql = "SELECT * FROM issues WHERE status IN ('assigned', 'in_progress') ORDER BY priority DESC LIMIT 10";
    $tasks = $db->select($sql);
    echo json_encode(['success' => true, 'tasks' => $tasks]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
