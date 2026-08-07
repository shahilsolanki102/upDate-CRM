<?php
require_once "../config.php";
$requireLogin = 'admin';
include __DIR__."/../includes/header.php";

// Fetch today's work shifts and active online status
$today = date('Y-m-d');
$shifts = $conn->query("
    SELECT a.*, u.name, u.email, u.department, u.role
    FROM attendance_logs a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.clock_in DESC
    LIMIT 30
");

// Calculate online stats
$onlineRes = $conn->query("SELECT COUNT(DISTINCT user_id) c FROM attendance_logs WHERE status='active'");
$onlineCount = $onlineRes ? ($onlineRes->fetch_assoc()['c'] ?? 0) : 0;
?>

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
            <h3>Today's Shift Logins</h3>
            <div class="metric" style="color:#2563eb;"><?php echo $shifts ? $shifts->num_rows : 0; ?> Shifts</div>
            <div style="font-size:12px; color:#3b82f6; margin-top:4px; font-weight:600;">Remote & Office Shifts</div>
        </div>
        <div style="width:50px; height:50px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:24px;">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">⏱️ Remote & Office Employee Attendance Tracker</h3>
            <div style="font-size:13px; color:var(--text-muted); font-weight:400;">Track exact clock-in times, clock-out times, work modes, and total duration for remote and on-site staff.</div>
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
            $mins = $s['total_minutes'] ?? 0;
            $durationStr = $isActive ? 'Currently Working...' : (floor($mins / 60) . "h " . ($mins % 60) . "m");
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar-sm" style="width:34px; height:34px; font-size:14px; background:<?php echo $isActive?'#10b981':'#1e3c72'; ?>;">
                        <?php echo strtoupper(substr($s['name'], 0, 1)); ?>
                    </div>
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
                    <span class="badge" style="background:<?php echo $isActive?'#d1fae5':'#f1f5f9'; ?>; color:<?php echo $isActive?'#065f46':'#64748b'; ?>;">
                        <?php echo $isActive ? '🟢 Active Online' : '⚪ Shift Ended'; ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" style="text-align:center; color:#777; padding:20px;">No attendance work shifts recorded yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
