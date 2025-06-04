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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Username or email already exists']);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
if ($stmt->execute([$username, $email, $hash])) {
    http_response_code(201);
    echo json_encode(['message' => 'Registration successful']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}