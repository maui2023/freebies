<?php
$response = '';
$headers = getallheaders();
$apiKey = $headers['X-API-KEY'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'email'    => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    $ch = curl_init('http://freebies.pc:8080/api/register.php');
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
    <title>Register Test Form</title>
</head>
<body>
    <h2>Test /api/register</h2>
    <form method="post">
        <label>Username: <input name="username" required></label><br>
        <label>Email: <input name="email" type="email" required></label><br>
        <label>Password: <input name="password" type="password" required></label><br>
        <button type="submit">Register</button>
    </form>
    <?php if ($response): ?>
        <div style="margin-top:20px;padding:10px;border:1px solid #ccc;">
            <strong>API Response:</strong><br>
            <?= $response ?>
        </div>
    <?php endif; ?>
</body>
</html>