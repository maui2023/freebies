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

// --- GET USERNAME FROM QUERY ---
$username = trim($_GET['username'] ?? '');
if (!$username) {
    http_response_code(400);
    echo json_encode(['error' => 'Username is required']);
    exit;
}

// --- FETCH USER PROFILE ---
$stmt = $pdo->prepare('
    SELECT 
        u.id, u.username, u.email, u.role, 
        p.full_name, p.date_of_birth, p.phone, p.address, p.profile_image
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    WHERE u.username = ?
');
$stmt->execute([$username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// --- RETURN PROFILE OR ERROR ---
if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

http_response_code(200);
echo json_encode([
    'message' => 'Profile found',
    'profile' => $profile
]);
exit;
