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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID required']);
    exit;
}

$stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$id]);
$stock = $stmt->fetchColumn();
if ($stock === false) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

http_response_code(200);
echo json_encode(['stock' => (int)$stock]);
