<?php
require_once __DIR__ . '/../../bootstrap.php';

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

// --- ADMIN ROLE CHECK ---
$role = $headers['X-USER-ROLE'] ?? ($_SERVER['HTTP_X_USER_ROLE'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
// --- END ADMIN ROLE CHECK ---

// Allow PUT or POST
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get user ID from query (?id=123) or path (for demo, use query)
$id = $_GET['id'] ?? null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$newRole = '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $newRole = $input['role'] ?? '';
} else {
    $newRole = $_POST['role'] ?? '';
}

if (!$id || !in_array($newRole, ['client', 'vendor', 'admin'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
if ($stmt->execute([$newRole, $id])) {
    echo json_encode(['message' => 'Role updated']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed']);
}