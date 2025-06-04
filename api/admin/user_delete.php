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

// Get user ID from query (?id=123) or path (for demo, use query)
$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID required']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
if ($stmt->execute([$id])) {
    echo json_encode(['message' => 'User deleted']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Delete failed']);
}