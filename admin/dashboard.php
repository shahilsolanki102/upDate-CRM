<?php
require_once "../config.php";
$requireLogin = 'admin';
include __DIR__."/../includes/header.php";

// Counts
$cnt_users = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0;
$cnt_tasks = $conn->query("SELECT COUNT(*) c FROM tasks")->fetch_assoc()['c'] ?? 0;
$cnt_notes = $conn->query("SELECT COUNT(*) c FROM notes")->fetch_assoc()['c'] ?? 0;
$cnt_act   = $conn->query("SELECT COUNT(*) c FROM activity_log")->fetch_assoc()['c'] ?? 0;

// Latest 5 Activities
$activities = $conn->query("
    SELECT a.id, a.action, a.created_at, u.name 
    FROM activity_log a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
    LIMIT 5
");
?>
<div class="grid">
  <div class="card"><h3>Users</h3><div class="metric"><?php echo $cnt_users; ?></div></div>
  <div class="card"><h3>Tasks</h3><div class="metric"><?php echo $cnt_tasks; ?></div></div>
  <div class="card"><h3>Notes</h3><div class="metric"><?php echo $cnt_notes; ?></div></div>
  <div class="card"><h3>Activities</h3><div class="metric"><?php echo $cnt_act; ?></div></div>
</div>

<div class="card">
  <h3>Latest Activity</h3>
  <?php if ($activities && $activities->num_rows > 0) { ?>
      <table style="width:100%; border-collapse:collapse;" cellpadding="8">
        <thead>
          <tr style="background:#f1f5f9; text-align:left;"><th>User</th><th>Action</th><th>Date</th></tr>
        </thead>
        <tbody>
        <?php while($row = $activities->fetch_assoc()) { ?>
          <tr style="border-bottom:1px solid #e2e8f0;">
            <td><strong><?php echo htmlspecialchars($row['name'] ?? 'System User'); ?></strong></td>
            <td><?php echo htmlspecialchars($row['action']); ?></td>
            <td style="color:#666; font-size:0.85rem;"><?php echo $row['created_at']; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
  <?php } else { ?>
      <p style="color:#666;">No activity logged yet.</p>
  <?php } ?>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
