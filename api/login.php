<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

// --- API KEY CHECK ---
$headers = getallheaders();
$apiKey = $headers['X-API-KEY'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');
if ($apiKey !== $_ENV['API_KEY']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
// --- END API KEY CHECK ---

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Detect content type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// Initialize credentials
$username = '';
$password = '';

if (stripos($contentType, 'application/json') !== false) {
    // JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
} else {
    // form-data or x-www-form-urlencoded
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
}

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password required']);
    exit;
}

// Find user
$stmt = $pdo->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Return user info
echo json_encode([
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);
