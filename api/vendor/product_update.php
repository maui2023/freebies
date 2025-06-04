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

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'])) {
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

$name = $_POST['name'] ?? null;
$price = $_POST['price'] ?? null;
$stock = $_POST['stock'] ?? null;
$category = $_POST['category'] ?? null;

$fields = [];
$params = [];
if ($name !== null) { $fields[] = 'name = ?'; $params[] = $name; }
if ($price !== null) { $fields[] = 'price = ?'; $params[] = $price; }
if ($stock !== null) { $fields[] = 'stock = ?'; $params[] = $stock; }
if ($category !== null) { $fields[] = 'category = ?'; $params[] = $category; }

if (!$fields) {
    http_response_code(400);
    echo json_encode(['error' => 'No fields to update']);
    exit;
}

$params[] = $id;
$sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?';
$stmt = $pdo->prepare($sql);

if ($stmt->execute($params)) {
    echo json_encode(['message' => 'Product updated']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed']);
}
