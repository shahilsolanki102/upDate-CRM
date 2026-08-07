<?php
require_once "../config.php";
$requireLogin = 'admin';
include __DIR__."/../includes/header.php";

// Users dropdown
$users = $conn->query("SELECT id, name, email FROM users ORDER BY name");

// Tasks list (supports both user_id and assigned_to columns)
$sql = "SELECT t.*, COALESCE(u.name, 'Unassigned') AS user_name, u.email
        FROM tasks t
        LEFT JOIN users u ON u.id = COALESCE(NULLIF(t.user_id,0), NULLIF(t.assigned_to,0))
        ORDER BY t.created_at DESC";
$tasks = $conn->query($sql);
?>

<div class="grid">
  <div class="card">
    <h3>Add / Edit Task</h3>
    <form action="task_save.php" method="post" class="form" style="display:grid; gap:.5rem; max-width:600px">
      <input type="hidden" name="id" id="task_id" value="">
      <label>Assign to (User)
        <select name="user_id" id="user_id" required>
          <option value="">-- select user --</option>
          <?php if ($users) { while($u = $users->fetch_assoc()): ?>
            <option value="<?php echo $u['id']; ?>">
              <?php echo htmlspecialchars($u['name']." (".$u['email'].")"); ?>
            </option>
          <?php endwhile; } ?>
        </select>
      </label>

      <label>Title
        <input type="text" name="title" id="title" required>
      </label>

      <label>Description
        <textarea name="description" id="description" rows="3" placeholder="Optional details"></textarea>
      </label>

      <label>Due Date
        <input type="date" name="due_date" id="due_date">
      </label>

      <div style="display:flex; gap:.5rem">
        <button class="btn" type="submit">Save</button>
        <button class="btn" type="reset" onclick="clearForm()">Clear</button>
      </div>
    </form>
  </div>

  <div class="card" style="grid-column: 1 / -1;">
    <h3>All Tasks</h3>
    <div class="table">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Title</th>
            <th>Status</th>
            <th>Due</th>
            <th>Submission</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php $i=1; if ($tasks) { while($t = $tasks->fetch_assoc()): ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($t['user_name'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($t['title'] ?? '—'); ?></td>
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
            <td><?php echo htmlspecialchars($t['created_at'] ?? ''); ?></td>
            <td style="white-space:nowrap">
              <button class="btn" onclick='editTask(<?php echo json_encode([
                "id"=>$t["id"],
                "user_id"=>$t["user_id"] ?? $t["assigned_to"] ?? 0,
                "title"=>$t["title"],
                "description"=>$t["description"] ?? '',
                "due_date"=>$t["due_date"] ?? ''
              ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>)'>Edit</button>
              <a class="btn danger" href="task_delete.php?id=<?php echo $t['id']; ?>" onclick="return confirm('Delete this task?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function editTask(t){
  document.getElementById('task_id').value = t.id;
  document.getElementById('user_id').value = t.user_id;
  document.getElementById('title').value = t.title || '';
  document.getElementById('description').value = t.description || '';
  document.getElementById('due_date').value = t.due_date || '';
  window.scrollTo({top:0, behavior:'smooth'});
}
function clearForm(){
  document.getElementById('task_id').value='';
}
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
