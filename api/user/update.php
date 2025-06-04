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

// --- ALLOW POST/PUT ---
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// --- READ INPUT (form-data or JSON) ---
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $username     = trim($input['username'] ?? '');
    $oldPassword  = $input['old_password'] ?? '';
    $newPassword  = $input['new_password'] ?? '';
    $fullName     = $input['full_name'] ?? '';
    $dateOfBirth  = $input['date_of_birth'] ?? '';
    $phone        = $input['phone'] ?? '';
    $address      = $input['address'] ?? '';
    $profileImage = $input['profile_image'] ?? ''; // URL or file path
} else {
    $username     = trim($_POST['username'] ?? '');
    $oldPassword  = $_POST['old_password'] ?? '';
    $newPassword  = $_POST['new_password'] ?? '';
    $fullName     = $_POST['full_name'] ?? '';
    $dateOfBirth  = $_POST['date_of_birth'] ?? '';
    $phone        = $_POST['phone'] ?? '';
    $address      = $_POST['address'] ?? '';
    $profileImage = $_POST['profile_image'] ?? ''; // URL or file path
}

if (!$username) {
    http_response_code(400);
    echo json_encode(['error' => 'Username is required']);
    exit;
}

// --- FIND USER ID ---
$stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$userId = $user['id'];

// --- HANDLE PASSWORD CHANGE ---
if (!empty($oldPassword) && !empty($newPassword)) {
    if (!password_verify($oldPassword, $user['password'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Old password incorrect']);
        exit;
    }

    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$newHashedPassword, $userId]);
}

// --- UPDATE user_profiles ---
$stmt = $pdo->prepare('
    INSERT INTO user_profiles (user_id, full_name, date_of_birth, phone, address, profile_image)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        full_name = VALUES(full_name),
        date_of_birth = VALUES(date_of_birth),
        phone = VALUES(phone),
        address = VALUES(address),
        profile_image = VALUES(profile_image)
');
$stmt->execute([
    $userId,
    $fullName,
    $dateOfBirth,
    $phone,
    $address,
    $profileImage
]);

http_response_code(200);
echo json_encode(['message' => 'Profile updated']);
exit;
