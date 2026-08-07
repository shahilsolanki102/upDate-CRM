<?php
require_once "../config.php";
$requireLogin = 'user'; 
include __DIR__."/../includes/header.php";

$uid = $_SESSION['uid'] ?? 0;
$name = $_SESSION['name'] ?? 'Employee';

// Fetch ONLY the logged-in user's activity logs
$sql = "SELECT a.*, u.name AS username, u.email
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h3 style="font-size:20px; margin:0;">📜 My Personal Activity Log</h3>
            <div style="font-size:13px; color:var(--text-muted); font-weight:400;">Your personal audit history of logins, task submissions, profile updates, and communications.</div>
        </div>
        <span class="badge" style="background:#e0e7ff; color:#3730a3;"><?php echo $result ? $result->num_rows : 0; ?> My Log Entries</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Action Performed</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $i=1;
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($row['action']); ?></td>
                    <td style="color:#64748b; font-size:13px; white-space:nowrap;"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                </tr>
        <?php } 
        } else { ?>
            <tr><td colspan="3" style="text-align:center; color:#777; padding:20px;">No activity logged for your account yet.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>