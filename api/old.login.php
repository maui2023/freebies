<?php
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');

// --- API KEY CHECK ---
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$apiKey = $headers['x-api-key'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');
if ($apiKey !== $_ENV['API_KEY']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// --- Only allow POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// --- Read from form-data ---
$username = trim($_POST['username'] ?? '');

if (!$username) {
    http_response_code(400);
    echo json_encode(['error' => 'Username required']);
    exit;
}

// --- Update role to 'vendor' ---
$stmt = $pdo->prepare('UPDATE users SET role = "vendor" WHERE username = ?');
if ($stmt->execute([$username])) {
    http_response_code(200);
    echo json_encode(['message' => 'User upgraded to vendor']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Upgrade failed']);
}
