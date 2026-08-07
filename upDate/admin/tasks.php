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
    <h3>➕ Create / Assign Work Ticket</h3>
    <form action="task_save.php" method="post" class="form" style="display:grid; gap:.6rem; max-width:600px">
      <input type="hidden" name="id" id="task_id" value="">
      
      <label style="font-size:13px; font-weight:600;">Assign to Employee
        <select name="user_id" id="user_id" class="input" required>
          <option value="">-- select employee --</option>
          <?php if ($users) { while($u = $users->fetch_assoc()): ?>
            <option value="<?php echo $u['id']; ?>">
              <?php echo htmlspecialchars($u['name']." (".$u['email'].")"); ?>
            </option>
          <?php endwhile; } ?>
        </select>
      </label>

      <label style="font-size:13px; font-weight:600;">Task Title / Ticket Subject
        <input type="text" name="title" id="title" class="input" placeholder="e.g. Prepare Monthly Sales Report" required>
      </label>

      <label style="font-size:13px; font-weight:600;">Ticket Priority
        <select name="priority" id="priority" class="input">
          <option value="Normal">🌱 Normal Priority</option>
          <option value="High">⚡ High Priority</option>
          <option value="Urgent">🔥 Urgent Priority</option>
          <option value="Low">☕ Low Priority</option>
        </select>
      </label>

      <label style="font-size:13px; font-weight:600;">Work Instructions / Details
        <textarea name="description" id="description" class="input" rows="3" placeholder="Write instructions for employee..."></textarea>
      </label>

      <label style="font-size:13px; font-weight:600;">Due Date
        <input type="date" name="due_date" id="due_date" class="input">
      </label>

      <div style="display:flex; gap:.5rem; margin-top:6px;">
        <button class="btn" type="submit">🎟️ Issue Work Ticket</button>
        <button class="btn danger-btn" type="reset" onclick="clearForm()">Clear</button>
      </div>
    </form>
  </div>

  <div class="card" style="grid-column: 1 / -1;">
    <h3>🎫 Issued Work Tickets</h3>
    <div class="table">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr>
            <th>Ticket ID</th>
            <th>Employee</th>
            <th>Task Subject</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>PDF Report</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($tasks) { while($t = $tasks->fetch_assoc()): 
            $tid = $t['ticket_id'] ?: ("TK-" . date('Y') . "-" . str_pad($t['id'], 5, '0', STR_PAD_LEFT));
            $p = $t['priority'] ?: 'Normal';
        ?>
          <tr>
            <td>
              <span class="badge" style="background:#e0e7ff; color:#3730a3; font-weight:800;">
                🎟️ <?php echo htmlspecialchars($tid); ?>
              </span>
            </td>
            <td><strong><?php echo htmlspecialchars($t['user_name'] ?? '—'); ?></strong></td>
            <td><?php echo htmlspecialchars($t['title'] ?? '—'); ?></td>
            <td>
              <span class="badge" style="background:<?php echo $p==='Urgent'?'#fee2e2':($p==='High'?'#ffedd5':'#f1f5f9'); ?>; color:<?php echo $p==='Urgent'?'#991b1b':($p==='High'?'#c2410c':'#334155'); ?>;">
                <?php echo $p==='Urgent'?'🔥 Urgent':($p==='High'?'⚡ High':'🌱 Normal'); ?>
              </span>
            </td>
            <td><span class="badge"><?php echo htmlspecialchars($t['status'] ?? 'pending'); ?></span></td>
            <td><?php echo $t['due_date'] ?: '—'; ?></td>
            <td>
              <?php if(!empty($t['submission_file'])): ?>
                <a class="link" href="../uploads/tasks/<?php echo urlencode($t['submission_file']); ?>" target="_blank">📄 View PDF</a>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap; display:flex; gap:6px;">
              <a class="btn" style="padding:6px 12px; font-size:12px; background:#10b981; border:none;" href="../view_ticket.php?id=<?php echo $t['id']; ?>" target="_blank">🎫 View Ticket</a>
              <button class="btn" style="padding:6px 12px; font-size:12px;" onclick='editTask(<?php echo json_encode([
                "id"=>$t["id"],
                "user_id"=>$t["user_id"] ?? $t["assigned_to"] ?? 0,
                "title"=>$t["title"],
                "priority"=>$t["priority"] ?? "Normal",
                "description"=>$t["description"] ?? '',
                "due_date"=>$t["due_date"] ?? ''
              ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>)'>Edit</button>
              <a class="btn danger-btn" style="padding:6px 12px; font-size:12px;" href="task_delete.php?id=<?php echo $t['id']; ?>" onclick="return confirm('Delete this ticket?')">Delete</a>
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
  document.getElementById('priority').value = t.priority || 'Normal';
  document.getElementById('description').value = t.description || '';
  document.getElementById('due_date').value = t.due_date || '';
  window.scrollTo({top:0, behavior:'smooth'});
}
function clearForm(){
  document.getElementById('task_id').value='';
}
</script>

<?php include __DIR__."/../includes/footer.php"; ?>
