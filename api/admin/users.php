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

// --- ADMIN ROLE CHECK (for demo, via header) ---
$role = $headers['X-USER-ROLE'] ?? ($_SERVER['HTTP_X_USER_ROLE'] ?? '');
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
// --- END ADMIN ROLE CHECK ---

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['users' => $users]);