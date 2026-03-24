<?php
// backend/api/issues.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Helper to get input
function getInput() {
    $json = json_decode(file_get_contents('php://input'), true);
    return $json ? $json : $_POST;
}

if ($action === 'list') {
    $status = $_GET['status'] ?? '';
    // Fix: Allow filtering by user_id for Citizen Dashboard
    $user_id = $_GET['user_id'] ?? ''; 
    
    $sql = "SELECT i.*, u.full_name as reporter_name FROM issues i LEFT JOIN users u ON i.user_id = u.id WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND i.status = ?";
        $params[] = $status;
    }
    
    // Add user_id filter
    if ($user_id) {
        $sql .= " AND i.user_id = ?";
        $params[] = intval($user_id);
    }

    // Add assigned_to filter
    if (isset($_GET['assigned_to'])) {
        $sql .= " AND i.assigned_to = ?";
        $params[] = intval($_GET['assigned_to']);
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    
    $issues = $db->select($sql, $params);
    echo json_encode(['success' => true, 'data' => $issues]);
}
elseif ($action === 'create' && $method === 'POST') {
    $input = getInput();
    $userId = $input['user_id'] ?? ($_SESSION['user_id'] ?? 0); 
    
    if (!$userId && isset($input['user_id'])) $userId = $input['user_id'];

    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $category = $input['category'] ?? '';
    $priority = $input['priority'] ?? 'medium';
    $area = $input['area'] ?? '';
    $address = $input['address'] ?? '';

    $sql = "INSERT INTO issues (user_id, title, description, category, priority, area, location_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $id = $db->insert($sql, [$userId, $title, $description, $category, $priority, $area, $address]);

    if ($id) {
        echo json_encode(['success' => true, 'message' => 'Issue created', 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create issue']);
    }
}
elseif ($action === 'details') {
    $id = $_GET['id'] ?? 0;
    $issues = $db->select("SELECT * FROM issues WHERE id = ?", [$id]);
    if ($issues) {
        echo json_encode(['success' => true, 'issue' => $issues[0]]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Not found']);
    }
}
elseif ($action === 'update' && ($method === 'POST' || $method === 'PUT')) {
     $input = getInput();
     $id = $_GET['id'] ?? ($input['id'] ?? 0);
     $status = $input['status'] ?? null;
     $priority = $input['priority'] ?? null;
     
     if ($id) {
         if ($status) {
            $db->update("UPDATE issues SET status = ? WHERE id = ?", [$status, $id]);
         }
         if ($priority) {
            $db->update("UPDATE issues SET priority = ? WHERE id = ?", [$priority, $id]);
         }
         echo json_encode(['success' => true, 'message' => 'Updated']);
     } else {
         echo json_encode(['success' => false, 'error' => 'Missing data']);
     }
}
elseif ($action === 'assign') {
     $input = getInput();
     $issueId = $input['issue_id'] ?? 0;
     $assigneeId = $input['assignee_id'] ?? 0;
     if ($issueId && $assigneeId) {
         $db->update("UPDATE issues SET assigned_to = ?, status = 'assigned' WHERE id = ?", [$assigneeId, $issueId]);
         echo json_encode(['success' => true, 'message' => 'Assigned']);
     }
}
elseif ($action === 'delete' && ($method === 'POST' || $method === 'DELETE')) {
    // New Action for deleting reports (Citizen Dashboard)
    $input = getInput();
    $id = $_GET['id'] ?? ($input['id'] ?? 0);
    
    if ($id) {
        $sql = "DELETE FROM issues WHERE id = ?";
        // Using direct query/prepare since generic update/insert might not cover DELETE easily if not methodized
        // But using $db->query is safer if available public
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
             echo json_encode(['success' => true, 'message' => 'Issue deleted']);
        } else {
             echo json_encode(['success' => false, 'error' => 'Failed to delete issue']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing ID']);
    }
}
else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
// End of file
