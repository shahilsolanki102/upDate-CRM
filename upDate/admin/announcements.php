<?php
require_once "../config.php";
$requireLogin = 'admin'; 
include __DIR__."/../includes/header.php";

$msg_status = '';

// Delete Announcement
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id) {
        $conn->query("DELETE FROM announcements WHERE id=$id");
        $msg_status = "Announcement deleted successfully!";
    }
}

// Post New Announcement & Broadcast Notifications to All Users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $message   = trim($_POST['message'] ?? '');
    $category  = trim($_POST['category'] ?? 'General');
    $priority  = trim($_POST['priority'] ?? 'Normal');
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

    if ($title && $message) {
        $conn->query("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS priority VARCHAR(20) DEFAULT 'Normal'");
        $conn->query("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'General'");
        $conn->query("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS is_pinned TINYINT(1) DEFAULT 0");

        $stmt = $conn->prepare("INSERT INTO announcements (title, message, category, priority, is_pinned, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssi", $title, $message, $category, $priority, $is_pinned);
        $stmt->execute();
        $stmt->close();

        // Broadcast notification to all users
        $userRes = $conn->query("SELECT id FROM users");
        if ($userRes) {
            while ($u = $userRes->fetch_assoc()) {
                $uid = $u['id'];
                $notifMsg = "📢 New Company Announcement: " . $title;
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ($uid, '$notifMsg')");
            }
        }

        // Log activity
        $adminId = $_SESSION['uid'] ?? 1;
        $conn->query("INSERT INTO activity_log (user_id, action, created_at) VALUES ($adminId, 'Broadcasted company announcement: $title', NOW())");

        $msg_status = "Announcement broadcasted to all employees successfully!";
    }
}

// Fetch all announcements
$res = $conn->query("SELECT * FROM announcements ORDER BY is_pinned DESC, created_at DESC");
?>

<div class="card" style="max-width: 900px; margin: 0 auto 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">📢 Company Announcements & Broadcast Publisher</h3>
            <div style="font-size:13px; color:var(--text-muted);">Publish official news, policy updates, and urgent broadcasts to all staff.</div>
        </div>
        <span class="badge" style="background:#e0e7ff; color:#3730a3;">Live Broadcast Hub</span>
    </div>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:18px; font-weight:600;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="announcements.php" style="display:grid; gap:16px; background:#f8fafc; padding:22px; border-radius:18px; border:1.5px solid #e2e8f0;">
        <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:16px;">
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Announcement Title</label>
                <input class="input" name="title" placeholder="e.g. Q3 Town Hall & Performance Reviews" required>
            </div>
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Category</label>
                <select name="category" class="input">
                    <option value="HR & Policy">🏢 HR & Policy</option>
                    <option value="Tech & Product">🚀 Tech & Product</option>
                    <option value="Events & News">🎉 Events & News</option>
                    <option value="General">📢 General News</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Priority</label>
                <select name="priority" class="input">
                    <option value="Urgent">🔥 Urgent Broadcast</option>
                    <option value="Important">📌 Important Notice</option>
                    <option value="Normal">🌱 Normal Priority</option>
                </select>
            </div>
        </div>

        <div>
            <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Announcement Message Content</label>
            <textarea class="input" name="message" rows="4" placeholder="Write company news or policy details..." required style="resize:vertical;"></textarea>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13.5px; font-weight:600; cursor:pointer;">
                <input type="checkbox" name="is_pinned" value="1"> 📌 Pin Announcement to Top
            </label>

            <button type="submit" class="btn" style="padding:12px 26px;">
                <i class="bi bi-megaphone-fill"></i> Broadcast to All Employees
            </button>
        </div>
    </form>
</div>

<!-- Published Announcements List -->
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <h3 style="font-size:18px; margin-bottom:16px;">📜 Broadcasted Company Announcements</h3>

    <?php if ($res && $res->num_rows > 0): ?>
        <div style="display:grid; gap:16px;">
            <?php while($a = $res->fetch_assoc()): 
                $p = $a['priority'] ?? 'Normal';
                $cat = $a['category'] ?? 'General';
                $isPinned = !empty($a['is_pinned']);
            ?>
                <div style="background:<?php echo $isPinned?'#fffbeb':'#ffffff'; ?>; border:1.5px solid <?php echo $isPinned?'#fde68a':'#e2e8f0'; ?>; border-radius:18px; padding:20px; box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if ($isPinned): ?>
                                <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:800;">📌 Pinned to Top</span>
                            <?php endif; ?>

                            <span class="badge" style="background:<?php echo $p==='Urgent'?'#fee2e2':($p==='Important'?'#ffedd5':'#f1f5f9'); ?>; color:<?php echo $p==='Urgent'?'#991b1b':($p==='Important'?'#c2410c':'#334155'); ?>; font-weight:800;">
                                <?php echo $p==='Urgent'?'🔥 Urgent':($p==='Important'?'📌 Important':'🌱 Normal'); ?>
                            </span>

                            <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:700;">
                                <?php echo htmlspecialchars($cat); ?>
                            </span>
                        </div>

                        <a href="announcements.php?del=<?php echo $a['id']; ?>" onclick="return confirm('Delete this announcement?')" class="btn danger-btn" style="padding:6px 12px; font-size:12px;">
                            <i class="bi bi-trash-fill"></i> Delete
                        </a>
                    </div>

                    <h4 style="margin:4px 0 8px 0; color:var(--text-dark); font-size:18px; font-weight:700;"><?php echo htmlspecialchars($a['title']); ?></h4>
                    
                    <div style="color:#334155; font-size:14.5px; line-height:1.6; background:#f8fafc; padding:16px; border-radius:14px; border:1px solid #e2e8f0; margin-bottom:10px;">
                        <?php echo nl2br(htmlspecialchars($a['message'])); ?>
                    </div>

                    <div style="font-size:12px; color:var(--text-muted); display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="bi bi-person-fill"></i> Posted by Admin</span>
                        <span><i class="bi bi-clock-history"></i> <?php echo date('d M Y, h:i A', strtotime($a['created_at'])); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:#777; margin-top:10px;">No company announcements published yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
