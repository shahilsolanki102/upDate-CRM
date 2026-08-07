<?php
require_once "../config.php";
include __DIR__ . "/../includes/header.php";

$articles = $conn->query("SELECT * FROM knowledge ORDER BY created_at DESC");
?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <h3>📚 Knowledge Base & System Documentation</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:18px;">Browse internal documentation, employee guidelines, and system user manuals.</p>

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
        <p style="color:#777; margin-top:10px;">No documentation articles available at the moment.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>