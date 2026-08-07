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
?>

<div class="card">
  <h3>My Tasks</h3>
  <div class="table">
    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Status</th>
          <th>Due</th>
          <th>Submission</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php $i=1; if ($tasks) { while($t = $tasks->fetch_assoc()): ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td>
            <div style="font-weight:600"><?php echo htmlspecialchars($t['title']); ?></div>
            <?php if(!empty($t['description'])): ?>
              <div style="font-size:.9rem;color:#666"><?php echo nl2br(htmlspecialchars($t['description'])); ?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge"><?php echo htmlspecialchars($t['status'] ?? 'pending'); ?></span></td>
          <td><?php echo $t['due_date'] ?: '—'; ?></td>
          <td>
            <?php if(!empty($t['submission_file'])): ?>
              <a class="link" href="../uploads/tasks/<?php echo urlencode($t['submission_file']); ?>" target="_blank">📄 View PDF</a>
              <?php if(!empty($t['submitted_at'])) echo "<div style='font-size:.8rem;color:#666'>".htmlspecialchars($t['submitted_at'])."</div>"; ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <?php if(empty($t['submission_file'])): ?>
              <form action="task_submit.php" method="post" enctype="multipart/form-data" style="display:grid; gap:.25rem">
                <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                <input type="file" name="submission" accept="application/pdf" required>
                <input type="text" name="remarks" placeholder="Remarks (optional)">
                <button class="btn" type="submit">Submit PDF</button>
              </form>
            <?php else: ?>
              <span style="color:#0a0; font-weight:600;">✓ Submitted</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
