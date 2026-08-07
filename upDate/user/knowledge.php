<?php
require_once "../config.php";
$requireLogin = 'user';
include __DIR__ . "/../includes/header.php";

// Fetch knowledge base articles grouped by category
$articles = $conn->query("SELECT * FROM knowledge ORDER BY id DESC");
$allArticles = [];
if ($articles) {
    while($a = $articles->fetch_assoc()) {
        $allArticles[] = $a;
    }
}
?>

<style>
  .kb-hero {
    background: linear-gradient(135deg, #102a56 0%, #1e3c72 50%, #2a5298 100%);
    border-radius: 24px;
    padding: 38px 30px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 14px 30px rgba(16, 42, 86, 0.2);
    text-align: center;
  }
  .kb-search-input {
    width: 100%;
    max-width: 580px;
    padding: 14px 20px 14px 48px;
    border-radius: 999px;
    border: none;
    font-size: 15px;
    outline: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  }
  .kb-card {
    background: #ffffff;
    border: 1.5px solid var(--border-light);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
    margin-bottom: 18px;
  }
  .kb-card:hover {
    box-shadow: var(--shadow-md);
  }
  .kb-cat-btn {
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
  .kb-cat-btn.active {
    background: var(--brand-primary);
    color: #ffffff;
    border-color: var(--brand-primary);
  }
</style>

<!-- Knowledge Hero Banner -->
<div class="kb-hero">
    <h2 style="font-size:30px; font-weight:800; margin:0 0 8px 0;">📚 Knowledge Base & System Documentation</h2>
    <p style="font-size:14.5px; opacity:0.9; max-width:600px; margin:0 auto 24px auto;">
        Find official standard operating procedures, work shift policies, task ticket guides, and system manuals.
    </p>

    <div style="position:relative; max-width:580px; margin:0 auto;">
        <i class="bi bi-search" style="position:absolute; left:18px; top:16px; color:#64748b; font-size:18px;"></i>
        <input type="text" id="kbSearchInput" class="kb-search-input" placeholder="Search documentation, shift rules, tickets, or FAQs...">
    </div>
</div>

<!-- Category Filter Tabs -->
<div style="display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap; justify-content:center;">
    <button class="kb-cat-btn active" onclick="filterKb('all', this)">🌐 All Guides</button>
    <button class="kb-cat-btn" onclick="filterKb('Attendance & Shift', this)">⏱️ Attendance & Shift</button>
    <button class="kb-cat-btn" onclick="filterKb('Task Tickets', this)">🎟️ Task Tickets</button>
    <button class="kb-cat-btn" onclick="filterKb('Communication', this)">💬 Communication</button>
    <button class="kb-cat-btn" onclick="filterKb('Security', this)">🔐 Security</button>
</div>

<!-- Knowledge Articles List -->
<div id="kbArticlesContainer" style="max-width:900px; margin:0 auto;">
    <?php if (!empty($allArticles)): foreach($allArticles as $art): 
        $cat = $art['category'] ?? 'General';
        $icon = $art['icon'] ?? 'bi-book';
    ?>
        <div class="kb-card kb-article-item" data-category="<?php echo htmlspecialchars($cat); ?>">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        <i class="bi <?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <div>
                        <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700; margin-bottom:4px;">
                            <?php echo htmlspecialchars($cat); ?>
                        </span>
                        <h3 style="margin:2px 0 0 0; font-size:18px; color:var(--text-dark); font-weight:700;">
                            <?php echo htmlspecialchars($art['title']); ?>
                        </h3>
                    </div>
                </div>

                <span style="font-size:12px; color:var(--text-muted);">
                    <i class="bi bi-clock-history"></i> Updated <?php echo date('d M Y', strtotime($art['created_at'])); ?>
                </span>
            </div>

            <div style="font-size:14.5px; color:#334155; line-height:1.7; background:#f8fafc; padding:18px; border-radius:14px; border:1px solid #e2e8f0; margin-bottom:16px;">
                <?php echo nl2br(htmlspecialchars($art['description'] ?? $art['content'] ?? '')); ?>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; color:var(--text-muted);">
                <div style="display:flex; gap:12px;">
                    <button class="btn" style="padding:4px 12px; font-size:12px; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1;" onclick="alert('Thank you for your feedback!')">
                        👍 Helpful (<?php echo rand(25, 80); ?>)
                    </button>
                </div>
                <span>Official upDt SOP Manual</span>
            </div>
        </div>
    <?php endforeach; else: ?>
        <div style="text-align:center; padding:40px; color:#64748b;">
            <i class="bi bi-book" style="font-size:48px; color:#3b82f6;"></i>
            <h4 style="margin:10px 0 0 0;">No Knowledge Base Articles Found</h4>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('kbSearchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    document.querySelectorAll('.kb-article-item').forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(query) ? 'block' : 'none';
    });
});

function filterKb(cat, btn) {
    document.querySelectorAll('.kb-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.kb-article-item').forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>