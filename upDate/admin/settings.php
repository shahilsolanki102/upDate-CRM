<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name  = trim($_POST['site_name'] ?? '');
    $admin_email= trim($_POST['admin_email'] ?? '');
    $currency   = trim($_POST['currency'] ?? '');
    $timezone   = trim($_POST['timezone'] ?? '');

    if ($site_name !== '') {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', '$site_name') ON DUPLICATE KEY UPDATE setting_value='$site_name'");
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_email', '$admin_email') ON DUPLICATE KEY UPDATE setting_value='$admin_email'");
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('currency', '$currency') ON DUPLICATE KEY UPDATE setting_value='$currency'");
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('timezone', '$timezone') ON DUPLICATE KEY UPDATE setting_value='$timezone'");
        
        $msg_status = "System settings updated successfully!";
    }
}

// Fetch existing settings
$settings = [];
$res = $conn->query("SELECT * FROM settings");
if ($res) {
    while($r = $res->fetch_assoc()) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
}
?>

<div class="card" style="max-width: 750px; margin: 0 auto;">
    <h3>⚙️ System Settings</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Configure system wide preferences for upDate CRM.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="settings.php" style="display:grid; gap:16px;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
            <div>
                <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Company / System Name</label>
                <input class="input" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'upDt Education Technology Pvt. Ltd.'); ?>" required>
            </div>
            <div>
                <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">System Admin Email</label>
                <input class="input" type="email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@updtcrm.com'); ?>" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
            <div>
                <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Default Currency</label>
                <select name="currency" class="input">
                    <option value="INR" <?php echo ($settings['currency']??'') === 'INR' ? 'selected':''; ?>>INR (₹) - Indian Rupee</option>
                    <option value="USD" <?php echo ($settings['currency']??'') === 'USD' ? 'selected':''; ?>>USD ($) - US Dollar</option>
                    <option value="EUR" <?php echo ($settings['currency']??'') === 'EUR' ? 'selected':''; ?>>EUR (€) - Euro</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Timezone</label>
                <select name="timezone" class="input">
                    <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                    <option value="UTC">UTC (Coordinated Universal Time)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px;">
            <span>💾 Save Settings</span>
        </button>
    </form>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>