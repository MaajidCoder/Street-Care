<?php
// backend/api/auth/login.php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Handle JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST; // Fallback to form data
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

$db = Database::getInstance();
$conn = $db->getConnection();

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all fields']);
    exit();
}

// Check user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    exit();
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    exit();
}

// Success
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];

unset($user['password']); // Don't send password back

// Token (Simple implementation)
$token = base64_encode(json_encode([
    'user_id' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'exp' => time() + 86400
]));

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'token' => $token,
    'user' => $user
]);
?>
