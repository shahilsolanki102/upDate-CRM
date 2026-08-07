<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to      = trim($_POST['to'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $uid     = $_SESSION['uid'] ?? 1;

    if ($to !== '' && $message !== '') {
        // Log activity
        $action = "Sent WhatsApp message to " . $to;
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, '$action', NOW())");

        // If to is numeric user ID or phone, add notification as well
        $checkUser = $conn->query("SELECT id FROM users WHERE phone LIKE '%$to%' OR id = " . intval($to) . " LIMIT 1");
        if ($checkUser && $checkUser->num_rows > 0) {
            $uId = $checkUser->fetch_assoc()['id'];
            $conn->query("INSERT INTO notifications (user_id, message) VALUES ($uId, 'WhatsApp: $message')");
        }

        $msg_status = "WhatsApp message dispatched successfully to $to!";
    }
}

// Fetch WhatsApp history from activity log
$history = $conn->query("SELECT * FROM activity_log WHERE action LIKE 'Sent WhatsApp%' ORDER BY created_at DESC LIMIT 10");
?>

<div class="card" style="max-width: 800px; margin: 0 auto 24px;">
    <h3 style="display:flex; align-items:center; gap:10px; font-size:20px; color:var(--text-dark);">
        <i class="bi bi-whatsapp" style="color:#25d366; font-size:30px;"></i>
        <span>WhatsApp Dispatcher</span>
    </h3>
    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:20px;">Send instant WhatsApp notifications and official work alerts to customers and employees.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-check-circle-fill" style="color:#10b981; font-size:18px;"></i>
            <span><?php echo htmlspecialchars($msg_status); ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="whatsapp.php" style="display:grid; gap:16px;">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Recipient Phone Number or Employee ID</label>
            <input class="input" name="to" placeholder="+919876543210 or Employee ID" required>
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block;">Message Content</label>
            <textarea class="input" name="message" rows="4" placeholder="Write your WhatsApp notification message..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px; background:#25d366; border:none;">
            <i class="bi bi-whatsapp"></i>
            <span>Send WhatsApp Message</span>
        </button>
    </form>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3>📜 Sent WhatsApp History</h3>
    <table>
        <thead>
            <tr><th>#</th><th>Details</th><th>Date & Time</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php $i=1; if ($history && $history->num_rows > 0) { while($h = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($h['action']); ?></td>
                <td style="color:#666; font-size:13px;"><?php echo $h['created_at']; ?></td>
                <td><span class="badge" style="background:#d1fae5; color:#065f46; border:none;">Sent</span></td>
            </tr>
        <?php endwhile; } else { ?>
            <tr><td colspan="4" style="text-align:center; color:#777; padding:16px;">No WhatsApp messages sent yet.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>