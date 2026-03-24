<?php
// backend/api/auth/register.php
require_once '../../config/config.php';
require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$full_name = $input['full_name'] ?? '';
$email = $input['email'] ?? '';
$phone = $input['phone'] ?? '';
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';
$role = $input['role'] ?? 'citizen';
$address = $input['address'] ?? '';
// Note: location field in DB is 'location', prompt map used 'location' as prompt input? 
// Prompt code used $location = $_POST['location'] ?? ''. Let's map it.
$location = $input['location'] ?? '';

// Validation
$errors = [];
if (empty($full_name)) $errors[] = 'Full name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($password)) $errors[] = 'Password is required';
if ($password !== $confirm_password) $errors[] = 'Passwords do not match';

$db = Database::getInstance();
$conn = $db->getConnection();

// Check email
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered']);
    exit();
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$username = explode('@', $email)[0] . rand(100, 999);

try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $full_name, $phone, $address, $role, $location);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
    } else {
        throw new Exception("Execution failed: " . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
