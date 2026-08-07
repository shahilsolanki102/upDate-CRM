<?php
require_once "../config.php";
$requireLogin = 'admin';
include __DIR__ . "/../includes/header.php";

$msg_status = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $conn->query("DELETE FROM knowledge WHERE id=$delId");
    $msg_status = "Article deleted successfully!";
}

// Handle Add Article
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? 'General');
    $icon        = trim($_POST['icon'] ?? 'bi-book');
    $description = trim($_POST['description'] ?? '');

    if ($title !== '' && $description !== '') {
        $stmt = $conn->prepare("INSERT INTO knowledge (title, category, icon, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $title, $category, $icon, $description);
        $stmt->execute();
        $stmt->close();
        $msg_status = "Knowledge base documentation published successfully!";
    }
}

$articles = $conn->query("SELECT * FROM knowledge ORDER BY id DESC");
?>

<div class="card" style="max-width: 900px; margin: 0 auto 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">📚 Admin Knowledge Base & System Documentation Manager</h3>
            <div style="font-size:13px; color:var(--text-muted);">Publish SOP manuals, shift rules, and system guides for all employees.</div>
        </div>
        <span class="badge" style="background:#e0e7ff; color:#3730a3;">Documentation Manager</span>
    </div>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:12px; font-size:14px; margin-bottom:18px; font-weight:600;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="knowledge.php" style="display:grid; gap:16px; background:#f8fafc; padding:22px; border-radius:18px; border:1.5px solid #e2e8f0;">
        <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:16px;">
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Article Title</label>
                <input class="input" name="title" placeholder="e.g. How to Request Emergency Punch-Out Approval" required>
            </div>
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Category</label>
                <select name="category" class="input">
                    <option value="Attendance & Shift">⏱️ Attendance & Shift</option>
                    <option value="Task Tickets">🎟️ Task Tickets</option>
                    <option value="Communication">💬 Communication</option>
                    <option value="Security">🔐 Security</option>
                    <option value="General">📘 General SOP</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Icon</label>
                <select name="icon" class="input">
                    <option value="bi-stopwatch">⏱️ Stopwatch</option>
                    <option value="bi-ticket-perforated">🎟️ Ticket</option>
                    <option value="bi-whatsapp">💬 WhatsApp</option>
                    <option value="bi-shield-lock">🔐 Security</option>
                    <option value="bi-book">📖 Book</option>
                </select>
            </div>
        </div>

        <div>
            <label style="font-size:13px; font-weight:700; margin-bottom:4px; display:block;">Documentation Content / Instructions</label>
            <textarea class="input" name="description" rows="5" placeholder="Write detailed step-by-step documentation..." required style="resize:vertical;"></textarea>
        </div>

        <button type="submit" class="btn" style="width:fit-content; padding:12px 26px;">
            <i class="bi bi-journal-plus"></i> Publish Official Documentation
        </button>
    </form>
</div>

<!-- Published Documentation List -->
<div class="card" style="max-width: 900px; margin: 0 auto;">
    <h3 style="font-size:18px; margin-bottom:16px;">📖 Published Documentation Articles</h3>

    <?php if ($articles && $articles->num_rows > 0): ?>
        <div style="display:grid; gap:16px;">
            <?php while($a = $articles->fetch_assoc()): 
                $cat = $a['category'] ?? 'General';
                $icon = $a['icon'] ?? 'bi-book';
            ?>
                <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:18px; padding:20px; box-shadow:var(--shadow-sm);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:12px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:18px;">
                                <i class="bi <?php echo htmlspecialchars($icon); ?>"></i>
                            </div>
                            <div>
                                <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700;">
                                    <?php echo htmlspecialchars($cat); ?>
                                </span>
                                <h4 style="margin:2px 0 0 0; color:var(--text-dark); font-size:17px; font-weight:700;"><?php echo htmlspecialchars($a['title']); ?></h4>
                            </div>
                        </div>

                        <a href="knowledge.php?delete=<?php echo $a['id']; ?>" onclick="return confirm('Delete this article?')" class="btn danger-btn" style="padding:6px 12px; font-size:12px;">
                            <i class="bi bi-trash-fill"></i> Delete
                        </a>
                    </div>

                    <div style="color:#475569; font-size:14px; line-height:1.6; background:#f8fafc; padding:14px; border-radius:12px; border:1px solid #e2e8f0;">
                        <?php echo nl2br(htmlspecialchars($a['description'] ?? $a['content'] ?? '')); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:#777; margin-top:10px;">No knowledge base articles published yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>