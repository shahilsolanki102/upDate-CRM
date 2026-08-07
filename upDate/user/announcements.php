<?php
require_once "../config.php";
$requireLogin = 'user';
include __DIR__ . "/../includes/header.php";

// Fetch all announcements ordered by pinned status and date
$res = $conn->query("SELECT * FROM announcements ORDER BY is_pinned DESC, created_at DESC");
$allAnnouncements = [];
if ($res) {
    while($row = $res->fetch_assoc()) {
        $allAnnouncements[] = $row;
    }
}
?>

<style>
  .ann-hero {
    background: linear-gradient(135deg, #102a56 0%, #1e3c72 50%, #2a5298 100%);
    border-radius: 24px;
    padding: 38px 30px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 14px 30px rgba(16, 42, 86, 0.2);
    text-align: center;
  }
  .ann-search-input {
    width: 100%;
    max-width: 580px;
    padding: 14px 20px 14px 48px;
    border-radius: 999px;
    border: none;
    font-size: 15px;
    outline: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  }
  .ann-card {
    background: #ffffff;
    border: 1.5px solid var(--border-light);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
    margin-bottom: 18px;
  }
  .ann-card:hover {
    box-shadow: var(--shadow-md);
  }
  .ann-cat-btn {
    padding: 9px 18px;
    border-radius: 999px;
    border: 1px solid var(--border-light);
    background: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-dark);
  }
  .ann-cat-btn.active {
    background: var(--brand-primary);
    color: #ffffff;
    border-color: var(--brand-primary);
  }
</style>

<!-- Announcement Hero Banner -->
<div class="ann-hero">
    <h2 style="font-size:30px; font-weight:800; margin:0 0 8px 0;">📢 Official Company Announcements</h2>
    <p style="font-size:14.5px; opacity:0.9; max-width:600px; margin:0 auto 24px auto;">
        Stay up-to-date with company news, policy updates, events, and important management broadcasts.
    </p>

    <div style="position:relative; max-width:580px; margin:0 auto;">
        <i class="bi bi-search" style="position:absolute; left:18px; top:16px; color:#64748b; font-size:18px;"></i>
        <input type="text" id="annSearchInput" class="ann-search-input" placeholder="Search company broadcasts, HR policies, or news...">
    </div>
</div>

<!-- Category Filter Tabs -->
<div style="display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap; justify-content:center;">
    <button class="ann-cat-btn active" onclick="filterAnn('all', this)">🌐 All Broadcasts</button>
    <button class="ann-cat-btn" onclick="filterAnn('Urgent', this)">🔥 Urgent</button>
    <button class="ann-cat-btn" onclick="filterAnn('HR & Policy', this)">🏢 HR & Policy</button>
    <button class="ann-cat-btn" onclick="filterAnn('Tech & Product', this)">🚀 Tech & Product</button>
    <button class="ann-cat-btn" onclick="filterAnn('Events & News', this)">🎉 Events & News</button>
</div>

<!-- Announcements List Container -->
<div id="annContainer" style="max-width:900px; margin:0 auto;">
    <?php if (!empty($allAnnouncements)): foreach($allAnnouncements as $a): 
        $p = $a['priority'] ?? 'Normal';
        $cat = $a['category'] ?? 'General';
        $isPinned = !empty($a['is_pinned']);
    ?>
        <div class="ann-card ann-item" data-priority="<?php echo htmlspecialchars($p); ?>" data-category="<?php echo htmlspecialchars($cat); ?>" style="background:<?php echo $isPinned?'#fffbeb':'#ffffff'; ?>; border:1.5px solid <?php echo $isPinned?'#fde68a':'#e2e8f0'; ?>;">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <?php if ($isPinned): ?>
                        <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:800;">📌 Pinned Notice</span>
                    <?php endif; ?>

                    <span class="badge" style="background:<?php echo $p==='Urgent'?'#fee2e2':($p==='Important'?'#ffedd5':'#f1f5f9'); ?>; color:<?php echo $p==='Urgent'?'#991b1b':($p==='Important'?'#c2410c':'#334155'); ?>; font-weight:800;">
                        <?php echo $p==='Urgent'?'🔥 Urgent':($p==='Important'?'📌 Important':'🌱 Normal'); ?>
                    </span>

                    <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:700;">
                        <?php echo htmlspecialchars($cat); ?>
                    </span>
                </div>

                <span style="font-size:12px; color:var(--text-muted);">
                    <i class="bi bi-clock-history"></i> Posted <?php echo date('d M Y, h:i A', strtotime($a['created_at'])); ?>
                </span>
            </div>

            <h3 style="margin:0 0 10px 0; font-size:20px; color:var(--text-dark); font-weight:700;">
                <?php echo htmlspecialchars($a['title']); ?>
            </h3>

            <div style="font-size:14.5px; color:#334155; line-height:1.7; background:#f8fafc; padding:18px; border-radius:14px; border:1px solid #e2e8f0; margin-bottom:16px;">
                <?php echo nl2br(htmlspecialchars($a['message'])); ?>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; color:var(--text-muted);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div class="avatar-sm" style="width:28px; height:28px; font-size:12px; background:var(--brand-gradient);">A</div>
                    <span>Posted by <strong>Management / Admin</strong></span>
                </div>

                <button class="btn" style="padding:6px 14px; font-size:12.5px; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1;" onclick="this.innerHTML='✓ Acknowledged'; this.style.background='#d1fae5'; this.style.color='#065f46';">
                    👍 Acknowledge Notice
                </button>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div style="text-align:center; padding:40px; color:#64748b;">
            <i class="bi bi-megaphone" style="font-size:48px; color:#3b82f6;"></i>
            <h4 style="margin:10px 0 0 0;">No Company Announcements</h4>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('annSearchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    document.querySelectorAll('.ann-item').forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(query) ? 'block' : 'none';
    });
});

function filterAnn(filterVal, btn) {
    document.querySelectorAll('.ann-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.ann-item').forEach(card => {
        if (filterVal === 'all') {
            card.style.display = 'block';
        } else if (card.dataset.priority === filterVal || card.dataset.category === filterVal) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
