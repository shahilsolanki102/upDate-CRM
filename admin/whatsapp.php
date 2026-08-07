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
    <h3>🟢 WhatsApp Dispatcher</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Send instant WhatsApp notifications to customers and employees.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="whatsapp.php" style="display:grid; gap:14px;">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Recipient Phone Number or User ID</label>
            <input class="input" name="to" placeholder="+919876543210 or User ID" required>
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Message Content</label>
            <textarea class="input" name="message" rows="4" placeholder="Write your WhatsApp notification message..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px;">
            <span>🟢 Send WhatsApp Message</span>
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