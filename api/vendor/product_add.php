<?php
require_once __DIR__ . '/../../../bootstrap.php';

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

$vendor = trim($_POST['vendor'] ?? '');
$name = trim($_POST['name'] ?? '');
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$category = trim($_POST['category'] ?? '');

if (!$vendor || !$name) {
    http_response_code(400);
    echo json_encode(['error' => 'Vendor and name required']);
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

$insert = $pdo->prepare('INSERT INTO products (vendor_id, name, price, stock, category, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
if ($insert->execute([$user['id'], $name, $price, $stock, $category])) {
    http_response_code(201);
    echo json_encode(['message' => 'Product added']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Insert failed']);
}
