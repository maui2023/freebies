<?php
// Connect DB
$db = new PDO('mysql:host=localhost;dbname=chat', 'chat', '4747c5d14a033');
session_start();
if (!isset($_SESSION['miqa_sid'])) {
    $_SESSION['miqa_sid'] = bin2hex(random_bytes(16));
}
$sid = $_SESSION['miqa_sid'];

// Handle fetch history
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id=? ORDER BY id ASC");
    $stmt->execute([$sid]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $msg = trim($data['message']);
    if (!$msg) exit;

    // Save user message
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'user', ?)");
    $stmt->execute([$sid, $msg]);

    // Send to LLM API
    $apiUrl = "https://deepbaqhang.my/api/chat/completions";
    $apiKey = "sk-a1baa3d9109c48c094ad575590166ea3";
    $payload = json_encode([
        "model" => "sailor2:1b",
        "messages" => [["role" => "user", "content" => $msg]]
    ]);
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $result = curl_exec($ch);
    $resp = json_decode($result, true);
    $reply = $resp['choices'][0]['message']['content'] ?? "Sorry, I didn’t get that.";

    // Save AI message
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)");
    $stmt->execute([$sid, $reply]);

    echo json_encode(['reply' => $reply]);
    exit;
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

}
?>
