<?php
require_once "../config.php";
$requireLogin = 'admin'; 
include __DIR__."/../includes/header.php";

$uid = $_SESSION['uid'] ?? $_SESSION['aid'] ?? 0;

// Delete note
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];
    if ($del_id && $uid) {
        $stmt = $conn->prepare("DELETE FROM notes WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $del_id, $uid);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: notes.php"); exit;
}

// Add Note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = trim($_POST['note'] ?? '');
    if ($note && $uid) {
        // Detect note column
        $noteCol = ($conn->query("SHOW COLUMNS FROM notes LIKE 'note_content'")->num_rows > 0) ? 'note_content' : 'note';
        $stmt = $conn->prepare("INSERT INTO notes (user_id, $noteCol, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $uid, $note);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: notes.php"); exit;
}

// Fetch notes
$notes = $conn->query("SELECT * FROM notes WHERE user_id=$uid ORDER BY created_at DESC");
?>

<div class="card">
  <h3>📝 My Admin Notes</h3>

  <!-- Note Form -->
  <form method="post" action="notes.php" style="margin-bottom:15px;">
      <textarea name="note" 
                placeholder="✍️ Write a note..." 
                required 
                style="width:100%; height:80px; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; font-family:inherit;"></textarea><br><br>

      <button type="submit" 
              style="background:#4CAF50; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:bold;">
              ➕ Save Note
      </button>
  </form>

  <!-- Notes List -->
  <ul style="list-style:none; padding:0;">
    <?php if ($notes && $notes->num_rows > 0) { 
        while ($n = $notes->fetch_assoc()) { 
            $noteText = $n['note'] ?? $n['note_content'] ?? ''; ?>
          <li style="background:#f9f9f9; margin-bottom:10px; padding:12px; border-radius:10px; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
            <div style="font-size:15px; font-weight:500; color:#222;"><?php echo htmlspecialchars($noteText); ?></div>
            <div style="font-size:12px; color:#777; margin-top:5px;">
                <?php echo $n['created_at']; ?>
            </div>
            <a href="notes.php?del=<?php echo $n['id']; ?>" 
               onclick="return confirm('Delete this note?');"
               style="color:#fff; background:#e74c3c; padding:5px 12px; border-radius:6px; text-decoration:none; font-size:13px; margin-top:8px; display:inline-block;">
               🗑️ Delete
            </a>
          </li>
    <?php } } else { ?>
        <p style="color:#777;">No notes yet.</p>
    <?php } ?>
  </ul>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
