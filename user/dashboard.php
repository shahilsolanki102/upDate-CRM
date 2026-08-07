<?php
require_once "../config.php";

$requireLogin = 'user';
include __DIR__."/../includes/header.php";

if (!isset($_SESSION['uid'])) {
    header("Location: ../user_login.php");
    exit;
}

$uid = $_SESSION['uid'];

// Tasks & Notes Count
$tasksRes = $conn->query("SELECT COUNT(*) c FROM tasks WHERE user_id=$uid OR assigned_to=$uid");
$tasks    = $tasksRes ? ($tasksRes->fetch_assoc()['c'] ?? 0) : 0;

$notesRes = $conn->query("SELECT COUNT(*) c FROM notes WHERE user_id=$uid");
$notes    = $notesRes ? ($notesRes->fetch_assoc()['c'] ?? 0) : 0;

// Fetch Recent Assigned Tasks
$myTasks = $conn->query("SELECT * FROM tasks WHERE user_id=$uid OR assigned_to=$uid ORDER BY created_at DESC LIMIT 5");
?>

<div class="grid">
  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>Assigned Tasks</h3>
      <div class="metric"><?php echo $tasks; ?></div>
      <div style="font-size:12px; color:#10b981; margin-top:4px; font-weight:600;">📋 Tasks assigned to you</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#ecfdf5; border:1px solid #a7f3d0; color:#10b981; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-check2-square"></i>
    </div>
  </div>

  <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
      <h3>My Notes</h3>
      <div class="metric"><?php echo $notes; ?></div>
      <div style="font-size:12px; color:#f59e0b; margin-top:4px; font-weight:600;">📝 Personal Notes & Ideas</div>
    </div>
    <div style="width:52px; height:52px; border-radius:14px; background:#fffbeb; border:1px solid #fde68a; color:#f59e0b; display:flex; align-items:center; justify-content:center; font-size:24px;">
      <i class="bi bi-journal-bookmark-fill"></i>
    </div>
  </div>
</div>

<!-- My Assigned Tasks Section -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
    <h3>📋 Recent Assigned Tasks</h3>
    <a href="tasks.php" class="link" style="font-weight:600;">View All Tasks →</a>
  </div>

  <?php if ($myTasks && $myTasks->num_rows > 0): ?>
    <table>
      <thead>
        <tr><th>#</th><th>Task Title</th><th>Status</th><th>Due Date</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php $i=1; while($t = $myTasks->fetch_assoc()): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
          <td><span class="badge" style="background:#e0e7ff; color:#3730a3;"><?php echo htmlspecialchars($t['status'] ?? 'pending'); ?></span></td>
          <td style="color:#64748b; font-size:13px;"><?php echo $t['due_date'] ?: '—'; ?></td>
          <td><a href="tasks.php" class="btn" style="padding:6px 12px; font-size:12.5px;">Submit PDF</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="color:#64748b; padding:12px 0;">No tasks assigned yet.</p>
  <?php endif; ?>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
