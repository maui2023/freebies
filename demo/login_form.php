<?php
$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    $ch = curl_init('http://freebies.pc:8080/api/login.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-KEY: your_secret_key_here'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $response = "HTTP $httpcode<br>" . htmlspecialchars($response);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test Form</title>
</head>
<body>
    <h2>Test /api/login</h2>
    <form method="post">
        <label>Username or Email: <input name="username" required></label><br>
        <label>Password: <input name="password" type="password" required></label><br>
        <button type="submit">Login</button>
    </form>
    <?php if ($response): ?>
        <div style="margin-top:20px;padding:10px;border:1px solid #ccc;">
            <strong>API Response:</strong><br>
            <?= $response ?>
        </div>
    <?php endif; ?>
</body>
</html>