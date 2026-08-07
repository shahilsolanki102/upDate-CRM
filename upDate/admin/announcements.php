<?php
require_once "../config.php";
$requireLogin = 'admin'; 
include __DIR__."/../includes/header.php";

// Delete
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id) {
        $conn->query("DELETE FROM announcements WHERE id=$id");
    }
    header("Location: announcements.php"); exit;
}

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($title && $message) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $message);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: announcements.php"); exit;
}

// Fetch all
$res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div class="card">
  <h3>📢 Manage Announcements</h3>
  
  <form method="post" style="margin-bottom:15px;">
    <input type="text" name="title" placeholder="Title" required 
           style="width:100%; padding:10px; margin-bottom:8px; border:1px solid #ccc; border-radius:6px;">
    <textarea name="message" placeholder="Write announcement..." required 
              style="width:100%; height:80px; padding:10px; border:1px solid #ccc; border-radius:6px;"></textarea><br><br>
    <button type="submit" style="background:#4CAF50; color:#fff; padding:10px 20px; border:none; border-radius:6px;">
        ➕ Add Announcement
    </button>
  </form>

  <ul style="list-style:none; padding:0;">
    <?php while ($row = $res->fetch_assoc()) { ?>
      <li style="background:#f9f9f9; margin-bottom:10px; padding:12px; border-radius:8px;">
        <b><?= htmlspecialchars($row['title']) ?></b><br>
        <?= nl2br(htmlspecialchars($row['message'])) ?>
        <div style="font-size:12px; color:#666;"><?= $row['created_at'] ?></div>
        <a href="?del=<?= $row['id'] ?>" onclick="return confirm('Delete this?');"
           style="color:#fff; background:#e74c3c; padding:5px 12px; border-radius:6px; text-decoration:none; font-size:13px; margin-top:6px; display:inline-block;">
           🗑️ Delete
        </a>
      </li>
    <?php } ?>
  </ul>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
