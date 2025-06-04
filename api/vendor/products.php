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

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$vendor = trim($_GET['vendor'] ?? '');
if (!$vendor) {
    http_response_code(400);
    echo json_encode(['error' => 'Vendor username required']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND role = "vendor"');
$stmt->execute([$vendor]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Vendor not found']);
    exit;
}

$productsStmt = $pdo->prepare('SELECT id, name, price, stock, category FROM products WHERE vendor_id = ?');
$productsStmt->execute([$user['id']]);
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode(['products' => $products]);
