<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';
$uid   = $_SESSION['uid'] ?? 0;
$user  = null;

// Ensure uploads/profiles/ directory exists
if (!file_exists(__DIR__ . '/../uploads/profiles/')) {
    mkdir(__DIR__ . '/../uploads/profiles/', 0777, true);
}

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
    $photoPath = $user['profile_pic'] ?? '';

    // Profile Photo Upload Handling
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $fileName = 'user_' . $uid . '_' . time() . '.' . strtolower($ext);
        $target = __DIR__ . '/../uploads/profiles/' . $fileName;
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
            $photoPath = $fileName;
        }
    }

    if ($name !== '' && $uid > 0) {
        // Ensure profile_pic column exists
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_pic VARCHAR(255) NULL");

        if ($pass !== '') {
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET name='$name', phone='$phone', password='$newHash', profile_pic='$photoPath' WHERE id=$uid");
        } else {
            $conn->query("UPDATE users SET name='$name', phone='$phone', profile_pic='$photoPath' WHERE id=$uid");
        }

        // Log remote worker activity into activity_log so Admin sees on dashboard
        $actionLog = "Remote Employee $name updated profile details & photo.";
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$actionLog', NOW())");

        $_SESSION['name'] = $name;
        $msg_status = "Profile details & avatar updated successfully!";

        $res = $conn->query("SELECT * FROM users WHERE id = $uid");
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
        }
    }
}
?>

<div class="card" style="max-width: 680px; margin: 0 auto;">
    <h3>👤 My Profile & Account Settings</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Manage your personal profile, upload your photo, and update security credentials.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="profile.php" enctype="multipart/form-data" style="display:grid; gap:16px;">
        <div style="display:flex; align-items:center; gap:20px; padding:16px; background:#f8fafc; border-radius:14px; border:1px solid #e2e8f0;">
            <?php if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/../uploads/profiles/' . $user['profile_pic'])): ?>
                <img src="../uploads/profiles/<?php echo urlencode($user['profile_pic']); ?>" style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--brand-primary); box-shadow:0 4px 10px rgba(0,0,0,0.15);" alt="Profile Avatar">
            <?php else: ?>
                <div class="avatar-sm" style="width:72px; height:72px; font-size:28px;">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div>
                <label style="font-size:13px; font-weight:700; color:var(--text-dark); display:block; margin-bottom:4px;">Upload Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" class="input" style="padding:8px 12px; font-size:13px;">
            </div>
        </div>

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
            <span>💾 Save Profile & Photo</span>
        </button>
    </form>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>