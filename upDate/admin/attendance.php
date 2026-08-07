<?php
require_once "../config.php";
$requireLogin = 'admin';
include __DIR__."/../includes/header.php";

// Fetch pending early exit requests for Admin approval
$pendingRequests = $conn->query("
    SELECT a.*, u.name, u.email, u.phone, u.profile_pic
    FROM attendance_logs a
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'pending_approval'
    ORDER BY a.id DESC
");

// Fetch today's work shifts and active online status
$shifts = $conn->query("
    SELECT a.*, u.name, u.email, u.department, u.role, u.profile_pic
    FROM attendance_logs a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.clock_in DESC
    LIMIT 30
");

// Calculate online stats
$onlineRes = $conn->query("SELECT COUNT(DISTINCT user_id) c FROM attendance_logs WHERE status='active'");
$onlineCount = $onlineRes ? ($onlineRes->fetch_assoc()['c'] ?? 0) : 0;
?>

<!-- Metric Widgets -->
<div class="grid">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Currently Online & Working</h3>
            <div class="metric" style="color:#10b981;"><?php echo $onlineCount; ?> Employees</div>
            <div style="font-size:12px; color:#10b981; margin-top:4px; font-weight:600;">🟢 Active Work Shifts</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#ecfdf5; border:1px solid #a7f3d0; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-clock-history"></i>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h3>Pending Early Exit Requests</h3>
            <div class="metric" style="color:#ef4444;"><?php echo $pendingRequests ? $pendingRequests->num_rows : 0; ?> Requests</div>
            <div style="font-size:12px; color:#ef4444; margin-top:4px; font-weight:600;">⚠️ Needs Admin Approval</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#fef2f2; border:1px solid #fecaca; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
    </div>
</div>

<!-- Pending Early Punch-Out Requests Box (Admin Approval) -->
<?php if ($pendingRequests && $pendingRequests->num_rows > 0): ?>
<div class="card" style="margin-bottom:24px; border:2px solid #f87171; background:#fef2f2;">
    <h3 style="color:#991b1b; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>⚠️ Emergency Early Punch-Out Approval Requests</span>
    </h3>
    <p style="color:#7f1d1d; font-size:13px; margin-bottom:16px;">Employees requested early exit between 9:00 AM and 5:00 PM. Review reasons and approve below.</p>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Clock In Time</th>
                <th>Emergency Reason</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($pr = $pendingRequests->fetch_assoc()): ?>
            <tr>
                <td style="display:flex; align-items:center; gap:10px;">
                    <?php if (!empty($pr['profile_pic']) && file_exists(__DIR__ . '/../uploads/profiles/' . $pr['profile_pic'])): ?>
                        <img src="../uploads/profiles/<?php echo urlencode($pr['profile_pic']); ?>" style="width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid #ef4444;" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-sm" style="width:34px; height:34px; font-size:14px; background:#ef4444;">
                            <?php echo strtoupper(substr($pr['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight:700; color:#111827;"><?php echo htmlspecialchars($pr['name']); ?></div>
                        <div style="font-size:12px; font-weight:400; color:#6b7280;"><?php echo htmlspecialchars($pr['email']); ?></div>
                    </div>
                </td>
                <td style="font-weight:600; color:#374151;"><?php echo date('h:i A', strtotime($pr['clock_in'])); ?></td>
                <td>
                    <span style="background:#fee2e2; color:#991b1b; padding:6px 12px; border-radius:8px; font-size:13px; display:inline-block; font-weight:600;">
                        <?php echo htmlspecialchars($pr['early_reason'] ?? 'Emergency Exit Request'); ?>
                    </span>
                </td>
                <td>
                    <button class="btn" onclick="approveEarlyExit(<?php echo $pr['id']; ?>)" style="background:#10b981; border:none; padding:8px 16px; font-size:13px;">
                        <i class="bi bi-check-circle-fill"></i> Approve Early Punch-Out
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Attendance Table -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">⏱️ Remote & Office Employee Attendance Tracker</h3>
            <div style="font-size:13px; color:var(--text-muted); font-weight:400;">Shift Hours: 9:00 AM to 5:00 PM | Auto 11:59 PM Midnight Punch-Out Enabled.</div>
        </div>
        <span class="badge" style="background:#e0e7ff; color:#3730a3;">Live Attendance Sync</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Work Mode</th>
                <th>Clock In Time</th>
                <th>Clock Out Time</th>
                <th>Total Work Hours</th>
                <th>Current Status</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; if ($shifts && $shifts->num_rows > 0): while($s = $shifts->fetch_assoc()): 
            $isActive = ($s['status'] === 'active');
            $isPending = ($s['status'] === 'pending_approval');
            $isAuto = ($s['status'] === 'completed_auto');
            $mins = $s['total_minutes'] ?? 0;
            $durationStr = $isActive ? 'Currently Working...' : ($isPending ? 'Approval Pending' : (floor($mins / 60) . "h " . ($mins % 60) . "m"));
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td style="display:flex; align-items:center; gap:10px;">
                    <?php if (!empty($s['profile_pic']) && file_exists(__DIR__ . '/../uploads/profiles/' . $s['profile_pic'])): ?>
                        <img src="../uploads/profiles/<?php echo urlencode($s['profile_pic']); ?>" style="width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid <?php echo $isActive?'#10b981':'#1e3c72'; ?>;" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-sm" style="width:34px; height:34px; font-size:14px; background:<?php echo $isActive?'#10b981':'#1e3c72'; ?>;">
                            <?php echo strtoupper(substr($s['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($s['name']); ?></div>
                        <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($s['email']); ?></div>
                    </div>
                </td>
                <td>
                    <span class="badge" style="background:<?php echo $s['work_mode']==='remote'?'#fef3c7':'#dbeafe'; ?>; color:<?php echo $s['work_mode']==='remote'?'#92400e':'#1e40af'; ?>; text-transform:capitalize;">
                        <?php echo $s['work_mode']==='remote'?'🏠 Remote Work':'🏢 Office Work'; ?>
                    </span>
                </td>
                <td style="font-weight:600; color:#1e293b;"><?php echo date('d M Y, h:i A', strtotime($s['clock_in'])); ?></td>
                <td style="color:#64748b;"><?php echo $s['clock_out'] ? date('h:i A', strtotime($s['clock_out'])) : '—'; ?></td>
                <td style="font-weight:700; color:<?php echo $isActive?'#10b981':'#334155'; ?>;"><?php echo $durationStr; ?></td>
                <td>
                    <?php if ($isActive): ?>
                        <span class="badge" style="background:#d1fae5; color:#065f46;">🟢 Active Online</span>
                    <?php elseif ($isPending): ?>
                        <span class="badge" style="background:#fee2e2; color:#991b1b;">⚠️ Approval Pending</span>
                    <?php elseif ($isAuto): ?>
                        <span class="badge" style="background:#f3e8ff; color:#6b21a8;">🌙 Auto 11:59 PM Punched Out</span>
                    <?php else: ?>
                        <span class="badge" style="background:#f1f5f9; color:#64748b;">⚪ Shift Ended</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" style="text-align:center; color:#777; padding:20px;">No attendance work shifts recorded yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function approveEarlyExit(logId) {
    if (!confirm("Approve early Punch-Out for this employee?")) return;
    const formData = new FormData();
    formData.append('action', 'admin_approve');
    formData.append('log_id', logId);

    fetch("../api_attendance.php", {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            window.location.reload();
        } else {
            alert(res.error || "Approval failed");
        }
    });
}
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
