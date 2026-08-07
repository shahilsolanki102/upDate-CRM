<?php
require_once "../config.php";
$requireLogin = 'admin'; 
include __DIR__."/../includes/header.php";

// Fetch all system activity logs for Admin
$logs = $conn->query("
    SELECT a.*, COALESCE(u.name, 'System / Admin') AS user_name, u.email, u.role
    FROM activity_log a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC
");
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">📜 System-Wide Activity Audit Log (Admin)</h3>
            <div style="font-size:13px; color:var(--text-muted); font-weight:400;">Real-time audit trail of all employee activities, logins, task submissions, and system actions.</div>
        </div>
        <span class="badge"><?php echo $logs ? $logs->num_rows : 0; ?> Total Log Entries</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User / Employee</th>
                <th>Action Performed</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; if ($logs && $logs->num_rows > 0) { while($row = $logs->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar-sm" style="width:34px; height:34px; font-size:14px; background:<?php echo ($row['role']??'')==='admin'?'var(--brand-gradient)':'#10b981'; ?>;">
                        <?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($row['user_name']); ?></div>
                        <div style="font-size:11.5px; color:var(--text-muted);"><?php echo htmlspecialchars($row['email'] ?? 'System Account'); ?></div>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($row['action']); ?></td>
                <td style="color:#64748b; font-size:13px; white-space:nowrap;"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
            </tr>
        <?php } } else { ?>
            <tr><td colspan="4" style="text-align:center; color:#777; padding:20px;">No system activity logged yet.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>