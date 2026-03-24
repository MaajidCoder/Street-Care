<?php
// backend/api/users.php
require_once '../config/database.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$conn = $db->getConnection();

switch ($method) {
    case 'GET':
        // Get all users
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT id, username, email, full_name, phone, address, role, status, created_at FROM users WHERE id = ?";
            $result = $db->select($sql, [$id]);
            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'error' => 'User not found']);
            }
        } elseif (isset($_GET['role'])) {
            $role = $_GET['role'];
            $sql = "SELECT id, username, email, full_name, phone, address, role, status, created_at FROM users WHERE role = ? ORDER BY created_at DESC";
            $result = $db->select($sql, [$role]);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            $sql = "SELECT id, username, email, full_name, phone, address, role, status, created_at FROM users ORDER BY created_at DESC";
            $result = $db->select($sql);
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    case 'POST':
        // Update user status or role (Admin feature) or Delete
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['action'])) {
            if ($data['action'] === 'update_status' && isset($data['user_id'], $data['status'])) {
                $userId = intval($data['user_id']);
                $status = $conn->real_escape_string($data['status']);
                
                $sql = "UPDATE users SET status = ? WHERE id = ?";
                if ($db->update($sql, [$status, $userId])) {
                    echo json_encode(['success' => true, 'message' => 'User status updated']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
                }
            } elseif ($data['action'] === 'update_profile' && isset($data['user_id'])) {
                $userId = intval($data['user_id']);
                $fullName = $data['full_name'] ?? '';
                $phone = $data['phone'] ?? '';
                $address = $data['address'] ?? '';
                $location = $data['location'] ?? ''; // New field
                $email = $data['email'] ?? ''; // New field

                // Check if email is already taken by another user
                if (!empty($email)) {
                    $checkEmailSql = "SELECT id FROM users WHERE email = ? AND id != ?";
                    $checkEmail = $db->select($checkEmailSql, [$email, $userId]);
                    if (!empty($checkEmail)) {
                        echo json_encode(['success' => false, 'error' => 'Email is already in use by another account']);
                        exit;
                    }
                }
                
                // Update query to include email and location
                $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, location = ?, email = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $fullName, $phone, $address, $location, $email, $userId);
                
                if ($stmt->execute()) {
                     // Return updated user data so frontend can update localStorage
                     // Actually need to fetch it
                     $u = $db->select("SELECT id, username, email, full_name, phone, address, role, status, created_at FROM users WHERE id = ?", [$userId]);
                     echo json_encode(['success' => true, 'message' => 'Profile updated', 'user' => $u[0]]);
                } else {
                     echo json_encode(['success' => false, 'error' => 'Failed to update']);
                }
            } elseif ($data['action'] === 'delete' && isset($data['user_id'])) {
                 $userId = intval($data['user_id']);
                 $sql = "DELETE FROM users WHERE id = ?";
                 $stmt = $conn->prepare($sql);
                 $stmt->bind_param("i", $userId);
                 if ($stmt->execute()) {
                     echo json_encode(['success' => true, 'message' => 'User deleted']);
                 } else {
                     echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
                 }
            } elseif ($data['action'] === 'create') {
                $fullName = $data['full_name'];
                $email = $data['email'];
                $username = explode('@', $email)[0] . rand(100, 999);
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                $role = $data['role'];

                if (empty($fullName) || empty($email) || empty($data['password'])) {
                    echo json_encode(['success' => false, 'error' => 'All fields required']);
                    exit;
                }

                $sql = "INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                     echo json_encode(['success' => false, 'error' => 'Database error: Prepare failed']);
                     exit;
                }

                $stmt->bind_param("sssss", $username, $email, $password, $fullName, $role);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'User created']);
                } else {
                    if ($conn->errno === 1062) {
                        echo json_encode(['success' => false, 'error' => 'Email already exists']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to create user']);
                    }
                }
            } else {
                echo json_encode(['error' => 'Invalid action']);
            }
        } else {
            echo json_encode(['error' => 'No action specified']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
// End of file
