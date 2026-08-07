<?php
require_once "../config.php";
$requireLogin = 'user';
include __DIR__."/../includes/header.php";

$uid = $_SESSION['uid'] ?? 0;

// Fetch tasks for logged in user (support user_id or assigned_to)
$sql = "SELECT * FROM tasks WHERE user_id=? OR assigned_to=? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $uid, $uid);
$stmt->execute();
$tasks = $stmt->get_result();
$stmt->close();

$allTasks = [];
$totalCnt = 0;
$pendingCnt = 0;
$submittedCnt = 0;

if ($tasks) {
    while ($t = $tasks->fetch_assoc()) {
        $allTasks[] = $t;
        $totalCnt++;
        if (!empty($t['submission_file'])) {
            $submittedCnt++;
        } else {
            $pendingCnt++;
        }
    }
}
$completionRate = $totalCnt > 0 ? round(($submittedCnt / $totalCnt) * 100) : 0;
?>

<!-- Task Summary Widgets -->
<div class="grid">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Total Assigned Tickets</h3>
            <div class="metric"><?php echo $totalCnt; ?></div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Work Tickets Issued</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-ticket-perforated-fill"></i>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Pending Action</h3>
            <div class="metric" style="color:#f59e0b;"><?php echo $pendingCnt; ?></div>
            <div style="font-size:12px; color:#f59e0b; margin-top:4px; font-weight:600;">⏳ Awaiting PDF Submission</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#fffbeb; border:1px solid #fde68a; color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-hourglass-split"></i>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Submitted Reports</h3>
            <div class="metric" style="color:#10b981;"><?php echo $submittedCnt; ?></div>
            <div style="font-size:12px; color:#10b981; margin-top:4px; font-weight:600;">✓ Completed & Uploaded</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#ecfdf5; border:1px solid #a7f3d0; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Completion Rate</h3>
            <div class="metric" style="color:#8b5cf6;"><?php echo $completionRate; ?>%</div>
            <div style="font-size:12px; color:#8b5cf6; margin-top:4px; font-weight:600;">Overall Progress</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#f3e8ff; border:1px solid #ddd6fe; color:#8b5cf6; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-trophy-fill"></i>
        </div>
    </div>
</div>

<!-- Main Task Manager Board -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
        <div>
            <h3 style="font-size:18px; margin:0;">🎫 My Assigned Work Tickets</h3>
            <div style="font-size:13px; color:var(--text-muted);">Manage official work tickets and submit PDF reports before due dates.</div>
        </div>

        <div style="display:flex; gap:8px;">
            <button class="btn" onclick="filterTask('all')" style="padding:6px 14px; font-size:13px; background:var(--brand-primary);">All (<?php echo $totalCnt; ?>)</button>
            <button class="btn" onclick="filterTask('pending')" style="padding:6px 14px; font-size:13px; background:#f59e0b;">Pending (<?php echo $pendingCnt; ?>)</button>
            <button class="btn" onclick="filterTask('completed')" style="padding:6px 14px; font-size:13px; background:#10b981;">Submitted (<?php echo $submittedCnt; ?>)</button>
        </div>
    </div>

    <?php if (!empty($allTasks)): ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:18px;">
            <?php foreach($allTasks as $t): 
                $isSubmitted = !empty($t['submission_file']);
                $cardStatus = $isSubmitted ? 'completed' : 'pending';
                $tid = $t['ticket_id'] ?: ("TK-" . date('Y') . "-" . str_pad($t['id'], 5, '0', STR_PAD_LEFT));
                $p = $t['priority'] ?: 'Normal';
            ?>
                <div class="task-card-item" data-status="<?php echo $cardStatus; ?>" style="background:#ffffff; border:1.5px solid <?php echo $isSubmitted?'#a7f3d0':'#e2e8f0'; ?>; border-radius:18px; padding:20px; box-shadow:0 4px 14px rgba(0,0,0,0.04); display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.2s;">
                    
                    <div>
                        <!-- Ticket Header & Priority Badges -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:6px;">
                            <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:800; padding:4px 10px;">
                                🎟️ <?php echo htmlspecialchars($tid); ?>
                            </span>

                            <span class="badge" style="background:<?php echo $p==='Urgent'?'#fee2e2':($p==='High'?'#ffedd5':'#f1f5f9'); ?>; color:<?php echo $p==='Urgent'?'#991b1b':($p==='High'?'#c2410c':'#334155'); ?>; font-weight:700;">
                                <?php echo $p==='Urgent'?'🔥 Urgent':($p==='High'?'⚡ High':'🌱 Normal'); ?>
                            </span>
                        </div>

                        <!-- Title & Description -->
                        <h4 style="margin:0 0 8px 0; font-size:16.5px; color:var(--text-dark); line-height:1.4;">
                            <?php echo htmlspecialchars($t['title']); ?>
                        </h4>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:12px; color:#64748b;">
                            <span><i class="bi bi-clock"></i> Due: <strong><?php echo $t['due_date'] ? date('d M Y', strtotime($t['due_date'])) : 'No due date'; ?></strong></span>
                            <a href="../view_ticket.php?id=<?php echo $t['id']; ?>" target="_blank" class="link" style="font-weight:700;">🎫 View Ticket Card →</a>
                        </div>

                        <?php if (!empty($t['description'])): ?>
                            <p style="font-size:13.5px; color:var(--text-muted); margin:0 0 16px 0; line-height:1.5; background:#f8fafc; padding:10px 12px; border-radius:10px; border-left:3px solid var(--accent-blue);">
                                <?php echo nl2br(htmlspecialchars($t['description'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Submission / Action Section -->
                    <div style="border-top:1px solid #f1f5f9; margin-top:14px; padding-top:14px;">
                        <?php if ($isSubmitted): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; background:#ecfdf5; padding:10px 14px; border-radius:12px; border:1px solid #a7f3d0;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <i class="bi bi-file-earmark-pdf-fill" style="color:#ef4444; font-size:22px;"></i>
                                    <div>
                                        <div style="font-size:12.5px; font-weight:700; color:#065f46;">Submitted PDF Report</div>
                                        <div style="font-size:11px; color:#047857;"><?php echo htmlspecialchars($t['submitted_at'] ?? 'Uploaded'); ?></div>
                                    </div>
                                </div>
                                <a href="../uploads/tasks/<?php echo urlencode($t['submission_file']); ?>" target="_blank" class="btn" style="padding:6px 12px; font-size:12px; background:#10b981; border:none;">
                                    <i class="bi bi-eye"></i> View PDF
                                </a>
                            </div>
                        <?php else: ?>
                            <form action="task_submit.php" method="post" enctype="multipart/form-data" style="display:grid; gap:10px; background:#f8fafc; padding:14px; border-radius:14px; border:1px solid #e2e8f0;">
                                <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                                
                                <div>
                                    <label style="font-size:12px; font-weight:700; color:var(--text-dark); display:block; margin-bottom:4px;">Attach Work PDF Report</label>
                                    <input type="file" name="submission" accept="application/pdf" class="input" style="padding:8px 10px; font-size:12.5px;" required>
                                </div>

                                <div>
                                    <input type="text" name="remarks" class="input" placeholder="Add remarks or notes (optional)" style="padding:8px 10px; font-size:12.5px;">
                                </div>

                                <button class="btn" type="submit" style="width:100%; justify-content:center; padding:10px; font-size:13.5px;">
                                    <i class="bi bi-upload"></i> Submit PDF Report
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:40px 20px; color:#64748b;">
            <i class="bi bi-ticket-perforated" style="font-size:48px; color:#3b82f6;"></i>
            <h4 style="margin:10px 0 4px 0; color:var(--text-dark);">No Work Tickets Issued</h4>
            <p style="font-size:13.5px; margin:0;">You have zero pending work tickets assigned by admin.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function filterTask(type) {
    const cards = document.querySelectorAll('.task-card-item');
    cards.forEach(card => {
        if (type === 'all') {
            card.style.display = 'flex';
        } else if (card.dataset.status === type) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
