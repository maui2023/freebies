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

$qr = $_POST['qr_code'] ?? null;
$user = $_POST['username'] ?? null;
if (!$qr || !$user) {
    http_response_code(400);
    echo json_encode(['error' => 'QR code and username required']);
    exit;
}

// Validate QR code
$qrStmt = $pdo->prepare('SELECT product_id, status FROM qr_codes WHERE token = ?');
$qrStmt->execute([$qr]);
$qrData = $qrStmt->fetch(PDO::FETCH_ASSOC);
if (!$qrData || $qrData['status'] !== 'active') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid QR code']);
    exit;
}

// Validate user
$userStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$userStmt->execute([$user]);
$userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
if (!$userRow) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Reduce stock
$stockStmt = $pdo->prepare('UPDATE products SET stock = stock - 1 WHERE id = ? AND stock > 0');
$stockStmt->execute([$qrData['product_id']]);
if ($stockStmt->rowCount() === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Out of stock']);
    exit;
}

// Mark QR claimed
$updateQr = $pdo->prepare('UPDATE qr_codes SET status = "claimed" WHERE token = ?');
$updateQr->execute([$qr]);

// Insert claim record
$claim = $pdo->prepare('INSERT INTO claims (user_id, product_id, claim_time, claim_token) VALUES (?, ?, NOW(), ?)');
$claim->execute([$userRow['id'], $qrData['product_id'], $qr]);

http_response_code(200);
echo json_encode(['message' => 'Product claimed']);
