<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';
$uid = $_SESSION['uid'] ?? 0;
$uname = $_SESSION['name'] ?? 'Employee';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($subject !== '' && $message !== '') {
        $conn->query("INSERT INTO notifications (user_id, message) VALUES (1, 'Contact Request from $uname ($subject): $message')");
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($uid, 'Contacted admin regarding: $subject', NOW())");
        $msg_status = "Your message has been sent to Admin successfully!";
    }
}
?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <h3>🟢 Contact Admin</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Need help or have an inquiry? Send a direct message to system admin.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="contact_admin.php" style="display:grid; gap:14px;">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Subject / Reason</label>
            <input class="input" name="subject" placeholder="e.g. Leave request, Technical issue, Query" required>
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Message</label>
            <textarea class="input" name="message" rows="5" placeholder="Type your message to admin..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px;">
            <span>✉️ Send Message to Admin</span>
        </button>
    </form>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>