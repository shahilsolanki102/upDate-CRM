<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$msg_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title !== '' && $description !== '') {
        $stmt = $conn->prepare("INSERT INTO knowledge (title, description, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
        $stmt->close();
        $msg_status = "Knowledge base article published successfully!";
    }
}

$articles = $conn->query("SELECT * FROM knowledge ORDER BY created_at DESC");
?>

<div class="card" style="max-width: 850px; margin: 0 auto 24px;">
    <h3>📚 Knowledge Base & Documentation (Admin)</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Add internal documentation, user guides, and system manuals.</p>

    <?php if ($msg_status): ?>
        <div style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:10px 14px; border-radius:10px; font-size:14px; margin-bottom:16px;">
            ✓ <?php echo htmlspecialchars($msg_status); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="knowledge.php" style="display:grid; gap:14px;">
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Article Title</label>
            <input class="input" name="title" placeholder="e.g. How to manage customer records in upDate CRM" required>
        </div>
        <div>
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Article Content / Description</label>
            <textarea class="input" name="description" rows="5" placeholder="Write guide details..." required style="resize:vertical;"></textarea>
        </div>
        <button type="submit" class="btn" style="width:fit-content; padding:12px 24px;">
            <span>➕ Publish Article</span>
        </button>
    </form>
</div>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <h3>📖 Published Articles</h3>
    <?php if ($articles && $articles->num_rows > 0): ?>
        <div style="display:grid; gap:14px; margin-top:14px;">
            <?php while($a = $articles->fetch_assoc()): ?>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                    <h4 style="margin:0 0 6px 0; color:var(--brand-primary); font-size:16px;"><?php echo htmlspecialchars($a['title']); ?></h4>
                    <p style="color:#475569; font-size:14px; margin:0 0 8px 0; line-height:1.5;"><?php echo nl2br(htmlspecialchars($a['description'] ?? $a['content'] ?? '')); ?></p>
                    <span style="font-size:12px; color:#94a3b8;"><?php echo $a['created_at']; ?></span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="color:#777; margin-top:10px;">No knowledge base articles published yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>