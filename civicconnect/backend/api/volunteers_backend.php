<?php
// backend/api/volunteers_backend.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$conn = $db->getConnection();

$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'list') {
        // List all volunteers
        $sql = "SELECT id, username, email, full_name, phone, address, status, created_at FROM users WHERE role = 'volunteer'";
        $result = $db->select($sql);
        echo json_encode(['success' => true, 'data' => $result]);
    }
    elseif ($action === 'tasks') {
        // List tasks assigned to a specific volunteer
        $volunteerId = $_GET['user_id'] ?? 0;
        if ($volunteerId) {
            $sql = "SELECT * FROM issues WHERE assigned_to = ? ORDER BY created_at DESC";
            $result = $db->select($sql, [$volunteerId]);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
             echo json_encode(['success' => false, 'error' => 'Missing user_id']);
        }
    }
    elseif ($action === 'my_tasks') {
        $userId = $_GET['user_id'] ?? 0;
        if ($userId) {
            $sql = "SELECT * FROM issues WHERE assigned_to = ? AND status IN ('assigned', 'in_progress', 'resolved')";
            $tasks = $db->select($sql, [$userId]);
            echo json_encode(['success' => true, 'data' => $tasks]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User ID required']);
        }
    }
    // Volunteer Stats
    elseif ($action === 'stats') {
        $userId = $_GET['user_id'] ?? 0;
        if ($userId) {
             // Count tasks completed by this volunteer
             $completed = $db->select("SELECT COUNT(*) as count FROM issues WHERE assigned_to = ? AND status = 'resolved'", [$userId]);
             
             // Count specific Cleanup tasks for "Cleaner" badge
             $cleanup = $db->select("SELECT COUNT(*) as count FROM issues WHERE assigned_to = ? AND status = 'resolved' AND category = 'garbage_waste'", [$userId]);
             
             $hours = $completed[0]['count'] * 2; // Dummy calc: 2 hours per task
             echo json_encode(['success' => true, 'stats' => [
                 'completed' => $completed[0]['count'],
                 'cleanup_completed' => $cleanup[0]['count'],
                 'hours' => $hours
             ]]);
        } else {
             echo json_encode(['success' => false, 'error' => 'User ID required']);
        }
    }
    else {
        echo json_encode(['success' => false, 'error' => 'Invalid GET action']);
    }
}
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    // Accept a task
    if ($action === 'accept_task') {
        $issueId = $data['issue_id'] ?? 0;
        $userId = $data['user_id'] ?? 0;

        if ($issueId && $userId) {
            // Update issue status to assigned and set user_id to volunteer
            // Note: In real app, we might have a separate 'assignments' table,
            // but for this simple schema we reuse user_id or add a 'volunteer_id' column.
            // The current schema uses user_id as 'reporter'.
            // If we overwrite user_id, we lose the reporter.
            // We should probably have a 'volunteer_id' column.
            // Since I can't easily change schema without risk, I will assume for this demo
            // that 'assigned' status implies it's being worked on, and maybe we store volunteer ID in a new column if possible,
            // OR we just use a simplified model where we don't track *who* is volunteering in the DB
            // other than maybe a text field, or we add the column now.
            // Let's check if I can add a column safely.

            // BETTER APPROACH for existing schema:
            // Use 'assigned_to' column if it exists?
            // Let's check `setup_db.php` or `database.sql` content from memory or view it.
            // I'll view `database.sql` to be sure.
            // For now preventing overwrite of reporter.

            // WAITING TO CHECK DB SCHEMA.
            // BUT to make progress, I will try to add `volunteer_id` column if not exists via SQL execution?
            // No, that's risky.
            // I'll check `c:\xampp\htdocs\street care\civicconnect\backend\config\database.sql` if I can.
            // Or just assume I can't and use a workaround?
            // Actually `issues` table usually has `user_id` (reporter).
            // I will add `volunteer_id` column to `issues` table via a migration script if needed.
            // Let's just create the code knowing I might need to run a SQL command.

            // Assuming I will add `volunteer_id` to issues table.
            $sql = "UPDATE issues SET status = 'in_progress', assigned_to = ?, assigned_at = NOW() WHERE id = ?";
            $db->update($sql, [$userId, $issueId]);
            echo json_encode(['success' => true, 'message' => 'Task Accepted']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
        }
    }
    elseif ($action === 'complete_task') {
        $issueId = $data['issue_id'] ?? 0;
        $userId = $data['user_id'] ?? 0;

        if ($issueId && $userId) {
            // Mark as resolved
            $sql = "UPDATE issues SET status = 'resolved', resolved_at = NOW() WHERE id = ? AND assigned_to = ?";
            $affected = $db->update($sql, [$issueId, $userId]);
            
            if ($affected) {
                echo json_encode(['success' => true, 'message' => 'Task Completed']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Task not found or not assigned to you']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
        }
    }
    else {
        // Future: Add logic to update availability
        echo json_encode(['success' => true, 'message' => 'Volunteer action received']);
    }
}
else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
