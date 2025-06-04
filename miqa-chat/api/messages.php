<?php
// Connect DB
$db = new PDO('mysql:host=localhost;dbname=chat', 'chat', '4747c5d14a033');
session_start();

if (!isset($_SESSION['miqa_sid'])) {
    $_SESSION['miqa_sid'] = bin2hex(random_bytes(16));
}
$sid = $_SESSION['miqa_sid'];

// Log file (in same directory)
function write_log($message) {
    $log_file = __DIR__ . '/chat_log.txt';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, "$timestamp $message\n", FILE_APPEND);
}

// Handle GET: Load chat history
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id=? ORDER BY id ASC");
    $stmt->execute([$sid]);
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Handle POST: Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $msg = trim($data['message'] ?? '');

    if (!$msg) {
        echo json_encode(['reply' => "Mesej kosong tidak dibenarkan."]);
        exit;
    }

    // Simpan mesej pengguna
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'user', ?)");
    $stmt->execute([$sid, $msg]);

    // Hantar ke LLM API
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
        CURLOPT_TIMEOUT => 10
    ]);

    // ⚠️ Bypass SSL (for debug only — disable this in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        write_log("cURL Error: " . curl_error($ch));
        echo json_encode(['reply' => "Maaf, terdapat ralat semasa menghubungi server."]);
        exit;
    }

    curl_close($ch);
    write_log("API Response: $result");

    $resp = json_decode($result, true);
    if (isset($resp['choices'][0]['message']['content'])) {
        $reply = $resp['choices'][0]['message']['content'];
    } else {
        write_log("Unexpected API structure: " . print_r($resp, true));
        $reply = "Maaf, saya tak dapat memahami balasan dari server.";
    }

    // Simpan balasan AI
    $stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)");
    $stmt->execute([$sid, $reply]);

    echo json_encode(['reply' => $reply]);
    exit;
}
?>
