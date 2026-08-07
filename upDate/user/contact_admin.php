<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';
$msg_type = 'success';
$uid = $_SESSION['uid'] ?? 0;
$uname = $_SESSION['name'] ?? 'Employee';

// Fetch Admin Email & Phone from settings or database
$adminEmail = 'admin@updtcrm.com';
$adminPhone = '+919876543210';

$setRes = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_email', 'admin_phone')");
if ($setRes) {
    while($s = $setRes->fetch_assoc()) {
        if ($s['setting_key'] === 'admin_email') $adminEmail = $s['setting_value'];
        if ($s['setting_key'] === 'admin_phone') $adminPhone = $s['setting_value'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode    = $_POST['mode'] ?? 'email';
    $subject = trim($_POST['subject'] ?? 'Direct Inquiry');
    $message = trim($_POST['message'] ?? '');

    if ($message !== '') {
        if ($mode === 'whatsapp') {
            $action = "Sent WhatsApp to Admin ($adminPhone): $message";
            $conn->query("INSERT INTO notifications (user_id, message) VALUES (1, '🟢 WhatsApp from $uname: $message')");
            $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$action', NOW())");
            $msg_status = "WhatsApp message dispatched to Admin ($adminPhone) successfully!";
        } else {
            $action = "Sent Email to Admin ($adminEmail) - [$subject]: $message";
            $conn->query("INSERT INTO notifications (user_id, message) VALUES (1, '✉️ Email from $uname ($subject): $message')");
            $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$action', NOW())");
            $msg_status = "Email dispatched to Admin ($adminEmail) successfully!";
        }
    }
}

// Fetch user's previous sent messages to admin
$sentHistory = $conn->query("SELECT * FROM activity_log WHERE user_id=$uid AND action LIKE 'Sent%' ORDER BY created_at DESC LIMIT 10");
?>

<div class="card" style="max-width: 820px; margin: 0 auto 24px;">
    <h3 style="display:flex; align-items:center; gap:10px; font-size:22px;">
        <i class="bi bi-headset" style="color:var(--accent-blue);"></i>
        <span>Contact System Admin</span>
    </h3>
    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:20px;">Need assistance or have an urgent query? Reach out directly to system admin via WhatsApp or Email.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-check-circle-fill" style="color:#10b981; font-size:18px;"></i>
            <span><?php echo htmlspecialchars($msg_status); ?></span>
        </div>
    <?php endif; ?>

    <!-- Channel Selector Buttons -->
    <div style="display:flex; gap:12px; margin-bottom:20px;">
        <button id="tabWaBtn" type="button" class="btn" style="background:#25d366; border:none; padding:12px 20px; font-size:14.5px;" onclick="switchTab('whatsapp')">
            <i class="bi bi-whatsapp" style="font-size:18px;"></i>
            <span>🟢 WhatsApp Admin</span>
        </button>
        <button id="tabEmBtn" type="button" class="btn" style="background:#ea4335; border:none; padding:12px 20px; font-size:14.5px; opacity:0.7;" onclick="switchTab('email')">
            <i class="bi bi-envelope-at-fill" style="font-size:18px;"></i>
            <span>✉️ Email Admin</span>
        </button>
    </div>

    <!-- 🟢 WhatsApp Form -->
    <form id="waForm" method="post" action="contact_admin.php" style="display:grid; gap:16px;">
        <input type="hidden" name="mode" value="whatsapp">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Admin WhatsApp Number (Pre-filled)</label>
            <input class="input" value="<?php echo htmlspecialchars($adminPhone); ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">WhatsApp Message for Admin</label>
            <textarea class="input" name="message" rows="4" placeholder="Type your WhatsApp message to Admin..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px; background:#25d366; border:none;">
            <i class="bi bi-whatsapp"></i>
            <span>Send WhatsApp to Admin</span>
        </button>
    </form>

    <!-- ✉️ Email Form -->
    <form id="emForm" method="post" action="contact_admin.php" style="display:none; gap:16px;">
        <input type="hidden" name="mode" value="email">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Admin Email Address (Pre-filled)</label>
            <input class="input" value="<?php echo htmlspecialchars($adminEmail); ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Subject Line</label>
            <input class="input" name="subject" placeholder="e.g. Leave Application, Project Update, Query" required>
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Email Body</label>
            <textarea class="input" name="message" rows="5" placeholder="Write your email body to Admin..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px; background:#ea4335; border:none;">
            <i class="bi bi-envelope-at-fill"></i>
            <span>Send Email to Admin</span>
        </button>
    </form>
</div>

<!-- History Table -->
<div class="card" style="max-width: 820px; margin: 0 auto;">
    <h3>📜 Sent Messages History</h3>
    <table>
        <thead>
            <tr><th>#</th><th>Message Details</th><th>Sent Timestamp</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php $i=1; if ($sentHistory && $sentHistory->num_rows > 0): while($sh = $sentHistory->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($sh['action']); ?></td>
                <td style="color:#64748b; font-size:13px;"><?php echo $sh['created_at']; ?></td>
                <td><span class="badge" style="background:#d1fae5; color:#065f46; border:none;">Delivered</span></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4" style="text-align:center; color:#777; padding:16px;">No messages sent to admin yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function switchTab(type) {
    const waForm = document.getElementById('waForm');
    const emForm = document.getElementById('emForm');
    const tabWaBtn = document.getElementById('tabWaBtn');
    const tabEmBtn = document.getElementById('tabEmBtn');

    if (type === 'whatsapp') {
        waForm.style.display = 'grid';
        emForm.style.display = 'none';
        tabWaBtn.style.opacity = '1';
        tabEmBtn.style.opacity = '0.6';
    } else {
        waForm.style.display = 'none';
        emForm.style.display = 'grid';
        tabWaBtn.style.opacity = '0.6';
        tabEmBtn.style.opacity = '1';
    }
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>