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

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$qr = $_GET['qr_code'] ?? null;
if (!$qr) {
    http_response_code(400);
    echo json_encode(['error' => 'QR code required']);
    exit;
}

$stmt = $pdo->prepare('SELECT product_id, status FROM qr_codes WHERE token = ?');
$stmt->execute([$qr]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) {
    http_response_code(404);
    echo json_encode(['error' => 'QR code not found']);
    exit;
}

$productStmt = $pdo->prepare('SELECT name, stock FROM products WHERE id = ?');
$productStmt->execute([$data['product_id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode(['product' => $product, 'qr_status' => $data['status']]);
