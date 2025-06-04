<?php
$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $role = $_POST['role'] ?? '';
    $data = ['role' => $role];

    $ch = curl_init("http://freebies.pc:8080/api/admin/user_role.php?id=" . urlencode($id));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-KEY: your_secret_key_here',
        'X-USER-ROLE: admin'
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
    <title>Admin: Change User Role Test</title>
</head>
<body>
    <h2>Test /api/admin/user_role.php</h2>
    <form method="post">
        <label>User ID: <input name="id" required></label><br>
        <label>New Role:
            <select name="role" required>
                <option value="client">client</option>
                <option value="vendor">vendor</option>
                <option value="admin">admin</option>
            </select>
        </label><br>
        <button type="submit">Change Role</button>
    </form>
    <?php if ($response): ?>
        <div style="margin-top:20px;padding:10px;border:1px solid #ccc;">
            <strong>API Response:</strong><br>
            <?= $response ?>
        </div>
    <?php endif; ?>
</body>
</html>