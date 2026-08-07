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
    LIMIT 6
");
?>

<!-- Metric Cards Grid -->
<div class="grid">
  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>Total Users</h3>
      <div class="metric"><?php echo $cnt_users; ?></div>
      <div style="font-size:12px; color:#10b981; margin-top:4px; font-weight:600;">✓ Active System Users</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#eff6ff; border:1px solid #bfdbfe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-people-fill"></i>
    </div>
  </div>

  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>Total Tasks</h3>
      <div class="metric"><?php echo $cnt_tasks; ?></div>
      <div style="font-size:12px; color:#3b82f6; margin-top:4px; font-weight:600;">📋 Assigned Tasks</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#ecfdf5; border:1px solid #a7f3d0; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-check2-square"></i>
    </div>
  </div>

  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>Saved Notes</h3>
      <div class="metric"><?php echo $cnt_notes; ?></div>
      <div style="font-size:12px; color:#f59e0b; margin-top:4px; font-weight:600;">📝 Admin & Personal Notes</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#fffbeb; border:1px solid #fde68a; color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-journal-bookmark-fill"></i>
    </div>
  </div>

  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>Activities</h3>
      <div class="metric"><?php echo $cnt_act; ?></div>
      <div style="font-size:12px; color:#8b5cf6; margin-top:4px; font-weight:600;">📜 Audit Trail Logs</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#f3e8ff; border:1px solid #ddd6fe; color:#8b5cf6; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-clock-history"></i>
    </div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="card" style="margin-bottom:24px; background:linear-gradient(135deg, #102a56 0%, #1e3c72 100%); color:#fff;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
    <div>
      <h3 style="color:#93c5fd; margin-bottom:4px;">⚡ Admin Quick Actions</h3>
      <div style="font-size:13.5px; opacity:0.85;">Directly manage system operations from your executive control panel.</div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="tasks.php" class="btn" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);"><i class="bi bi-plus-circle"></i> Create Task</a>
      <a href="users.php" class="btn" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);"><i class="bi bi-person-plus"></i> Add Employee</a>
      <a href="whatsapp.php" class="btn" style="background:#25d366; border:none;"><i class="bi bi-whatsapp"></i> WhatsApp Dispatch</a>
      <a href="gmail.php" class="btn" style="background:#ea4335; border:none;"><i class="bi bi-envelope-at-fill"></i> Send Email</a>
    </div>
  </div>
</div>

<!-- Latest Activity Log Table -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
    <h3>📜 Recent Audit & System Activity</h3>
    <a href="activity_log.php" class="link" style="font-weight:600;">View All Activity →</a>
  </div>
  <?php if ($activities && $activities->num_rows > 0) { ?>
      <table>
        <thead>
          <tr><th>User</th><th>Action Performed</th><th>Timestamp</th></tr>
        </thead>
        <tbody>
        <?php while($row = $activities->fetch_assoc()) { ?>
          <tr>
            <td style="display:flex; align-items:center; gap:10px;">
              <div class="avatar-sm" style="width:32px; height:32px; font-size:13px; background:#1e3c72;">
                <?php echo strtoupper(substr($row['name'] ?? 'S', 0, 1)); ?>
              </div>
              <strong><?php echo htmlspecialchars($row['name'] ?? 'System Admin'); ?></strong>
            </td>
            <td><?php echo htmlspecialchars($row['action']); ?></td>
            <td style="color:#64748b; font-size:13px;"><?php echo $row['created_at']; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
  <?php } else { ?>
      <p style="color:#64748b; padding:12px 0;">No activity logged yet.</p>
  <?php } ?>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
