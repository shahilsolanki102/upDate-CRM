<?php
require_once "../config.php";
$requireLogin = 'user'; 
include __DIR__."/../includes/header.php";

// Fetch announcements
$res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div class="card">
  <h3>📢 Announcements</h3>
  <ul style="list-style:none; padding:0;">
    <?php if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) { ?>
          <li style="background:#eef5ff; margin-bottom:10px; padding:12px; border-radius:8px;">
            <b><?= htmlspecialchars($row['title']) ?></b><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?>
            <div style="font-size:12px; color:#666;"><?= $row['created_at'] ?></div>
          </li>
    <?php } } else { ?>
        <p>No announcements yet.</p>
    <?php } ?>
  </ul>
</div>

<?php include __DIR__."/../includes/footer.php"; ?>
