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

// Fetch vendor id
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND role = "vendor"');
$stmt->execute([$vendor]);
$vendorRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vendorRow) {
    http_response_code(404);
    echo json_encode(['error' => 'Vendor not found']);
    exit;
}
$vendorId = $vendorRow['id'];

$countStmt = $pdo->prepare('SELECT COUNT(*) AS product_count FROM products WHERE vendor_id = ?');
$countStmt->execute([$vendorId]);
$stats = $countStmt->fetch(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode(['dashboard' => $stats]);
