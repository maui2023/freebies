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
$username         = trim($_POST['username'] ?? '');
$old_password     = $_POST['old_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// --- Validate required fields ---
if (!$username || !$old_password || !$new_password || !$confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// --- Validate new password minimum length ---
if (strlen($new_password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'New password must be at least 8 characters']);
    exit;
}

// --- Check confirm password match ---
if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'New password and confirmation do not match']);
    exit;
}

// --- Find user ---
$stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($old_password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// --- Hash new password ---
$new_hash = password_hash($new_password, PASSWORD_BCRYPT);

// --- Update password ---
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
if ($stmt->execute([$new_hash, $user['id']])) {

    // --- Log to audit table ---
    $stmtLog = $pdo->prepare('INSERT INTO password_logs (user_id, changed_by, ip_address, user_agent) VALUES (?, ?, ?, ?)');
    $stmtLog->execute([
        $user['id'],
        $username,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    http_response_code(200);
    echo json_encode(['message' => 'Password updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Password update failed']);
}
