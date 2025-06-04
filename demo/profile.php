<?php
// --- CONFIG ---
$apiBase = 'http://freebies.pc:8080/api/user/';
$apiKey = 'your_secret_key_here'; // <-- Replace with actual key

// --- SESSION FOR DEMO LOGIN ---
session_start();
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: profile.php');
    exit;
}
if (isset($_POST['login_username'])) {
    $_SESSION['username'] = $_POST['login_username'];
}
$username = $_SESSION['username'] ?? '';

// --- FETCH PROFILE ---
$profile = null;
$profileError = '';
if ($username) {
    $url = $apiBase . 'profile.php?username=' . urlencode($username);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . $apiKey
    ]);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode === 200) {
        $profile = json_decode($result, true)['profile'] ?? null;
    } else {
        $profileError = htmlspecialchars($result);
    }
}

// --- HANDLE PROFILE UPDATE ---
$updateMsg = '';
if (isset($_POST['update_profile']) && $username) {
    $data = [
        'username' => $username,
        'full_name' => $_POST['full_name'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'profile_image' => $_POST['profile_image'] ?? '',
    ];
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        $data['old_password'] = $_POST['old_password'];
        $data['new_password'] = $_POST['new_password'];
    }
    $json = json_encode($data);
    $ch = curl_init($apiBase . 'update.php');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json),
        'X-API-KEY: ' . $apiKey
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        $updateMsg = json_decode($response, true)['message'] ?? 'Profile updated successfully.';
    } else {
        $updateMsg = 'Error: ' . htmlspecialchars($response);
    }

    // Refresh profile data
    $url = $apiBase . 'profile.php?username=' . urlencode($username);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . $apiKey
    ]);
    $result = curl_exec($ch);
    $profile = json_decode($result, true)['profile'] ?? $profile;
    curl_close($ch);
}

// --- HANDLE UPGRADE TO VENDOR ---
$upgradeMsg = '';
if (isset($_POST['upgrade_vendor']) && $username) {
    $data = ['username' => $username];
    $ch = curl_init($apiBase . 'upgrade_to_vendor.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-KEY: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $upgradeMsg = curl_exec($ch);
    curl_close($ch);

    // Refresh profile again after upgrade
    $url = $apiBase . 'profile.php?username=' . urlencode($username);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . $apiKey
    ]);
    $result = curl_exec($ch);
    $profile = json_decode($result, true)['profile'] ?? $profile;
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
</head>
<body>
    <h2>User Profile Management</h2>
    <?php if (!$username): ?>
        <form method="post">
            <label>Login as Username: <input name="login_username" required></label>
            <button type="submit">Login</button>
        </form>
    <?php else: ?>
        <form method="post" style="float:right;">
            <button name="logout" type="submit">Logout</button>
        </form>
        <h3>Welcome, <?= htmlspecialchars($username) ?></h3>

        <?php if ($profileError): ?>
            <div style="color:red"><?= $profileError ?></div>
        <?php elseif ($profile): ?>
            <form method="post">
                <label>Full Name: <input name="full_name" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>"></label><br>
                <label>Date of Birth: <input name="date_of_birth" type="date" value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>"></label><br>
                <label>Phone: <input name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"></label><br>
                <label>Address: <input name="address" value="<?= htmlspecialchars($profile['address'] ?? '') ?>"></label><br>
                <label>Profile Image URL: <input name="profile_image" value="<?= htmlspecialchars($profile['profile_image'] ?? '') ?>"></label><br>
                <hr>
                <strong>Change Password:</strong><br>
                <label>Old Password: <input name="old_password" type="password"></label><br>
                <label>New Password: <input name="new_password" type="password"></label><br>
                <button name="update_profile" type="submit">Update Profile</button>
            </form>

            <form method="post" style="margin-top:20px;">
                <?php if (($profile['role'] ?? '') !== 'vendor'): ?>
                    <button name="upgrade_vendor" type="submit">Upgrade to Vendor</button>
                <?php else: ?>
                    <span style="color:green;">You are a vendor.</span>
                <?php endif; ?>
            </form>

            <?php if ($updateMsg): ?>
                <div style="margin-top:10px;color:blue"><?= htmlspecialchars($updateMsg) ?></div>
            <?php endif; ?>

            <?php if ($upgradeMsg): ?>
                <div style="margin-top:10px;color:green"><?= htmlspecialchars($upgradeMsg) ?></div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
