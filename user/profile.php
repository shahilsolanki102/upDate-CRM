<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';
$uid   = $_SESSION['uid'] ?? 0;
$user  = null;

if ($uid > 0) {
    $res = $conn->query("SELECT * FROM users WHERE id = $uid");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name !== '' && $uid > 0) {
        if ($pass !== '') {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET name='$name', phone='$phone', password='$newHash' WHERE id=$uid");
        } else {
            $conn->query("UPDATE users SET name='$name', phone='$phone' WHERE id=$uid");
        }
        $_SESSION['name'] = $name;
        $msg_status = "Profile details updated successfully!";
        $res = $conn->query("SELECT * FROM users WHERE id = $uid");
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
        }
    }
}
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <h3>👤 My Profile & Account Settings</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Manage your personal info and update login credentials.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="profile.php" style="display:grid; gap:14px;">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Full Name</label>
            <input class="input" name="name" value="<?php echo htmlspecialchars($user['name'] ?? $_SESSION['name'] ?? ''); ?>" required>
        </div>

        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Email Address (Read-Only)</label>
            <input class="input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
        </div>

        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Phone Number</label>
            <input class="input" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+919876543210">
        </div>

        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">New Password (Leave blank to keep unchanged)</label>
            <input class="input" type="password" name="password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px;">
            <span>💾 Save Profile Changes</span>
        </button>
    </form>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>